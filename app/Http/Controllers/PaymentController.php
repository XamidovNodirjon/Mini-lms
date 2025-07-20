<?php

namespace App\Http\Controllers;

use App\Enums\DebtStatus;
use App\Enums\PaymentType;
use App\Models\Debt; // Corrected: Use App\Models\Debt
use App\Models\Group; // Corrected: Use App\Models\Group
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'type']);

        $query = Payment::with(['student:id,full_name', 'group:id,name', 'debt.group:id,name']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->where('full_name', 'like', "%{$search}%");
                })
                    ->orWhereHas('group', function ($gq) use ($search) {
                        $gq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('amount', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%");

                $dateFormattedSearch = null;
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $search)) {
                    $dateFormattedSearch = $search;
                } else if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $search)) {
                    try {
                        $dateFormattedSearch = Carbon::createFromFormat('d.m.Y', $search)->format('Y-m-d');
                    } catch (\Exception $e) {
                        // Invalid date format, skip
                    }
                }
                if ($dateFormattedSearch) {
                    $q->orWhere('date', $dateFormattedSearch);
                }
            });
        }

        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        $payments = $query->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $students = Student::select('id', 'full_name')->orderBy('full_name')->get();
        $groups = Group::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Payments/Index', [
            'payments' => $payments,
            'filters' => $filters,
            'paymentTypes' => array_column(PaymentType::cases(), 'value'),
            'students' => $students,
            'groups' => $groups,
        ]);
    }

    public function create()
    {
        $students = Student::select('id', 'full_name')->orderBy('full_name')->get();
        $groups = Group::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Payments/Create', [
            'students' => $students,
            'groups' => $groups,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
            'group_id' => ['required', 'exists:groups,id'],
        ]);


        DB::beginTransaction();

        try {
            Log::info('Transaction started.');
            $student = Student::find($validatedData['student_id']);
            Log::info('Student found: ' . json_encode($student->toArray())); // YENGI LOG

            $remainingAmount = $validatedData['amount'];
            $paymentType = PaymentType::Balance;

            $debts = Debt::where('student_id', $student->id)
                ->where('group_id', $validatedData['group_id'])
                ->where('status', '!=', DebtStatus::Paid)
                ->orderBy('month', 'asc')
                ->get();

            Log::info("Student ID: {$student->id}, Group ID: {$validatedData['group_id']}, Initial Amount: {$remainingAmount}");
            Log::info("Debts found for selected group: " . json_encode($debts->toArray()));

            foreach ($debts as $debt) {
                if ($remainingAmount <= 0) break;

                $dueAmount = $debt->amount - $debt->paid_amount;

                if ($dueAmount > 0) {
                    if ($remainingAmount >= $dueAmount) {
                        $debt->paid_amount += $dueAmount;
                        $remainingAmount -= $dueAmount;
                        $debt->status = DebtStatus::Paid;
                        Log::info("Debt ID {$debt->id} fully paid. Remaining amount: {$remainingAmount}"); // YENGI LOG
                    } else {
                        $debt->paid_amount += $remainingAmount;
                        $remainingAmount = 0;
                        $debt->status = DebtStatus::Partial;
                        Log::info("Debt ID {$debt->id} partially paid. Remaining amount: {$remainingAmount}"); // YENGI LOG
                    }
                    $debt->save();
                    $paymentType = PaymentType::Debt;
                }
            }
            Log::info("After debt processing. Remaining amount: {$remainingAmount}, Payment type: {$paymentType->value}"); // YENGI LOG

            if ($remainingAmount > 0) {
                $student->balance += $remainingAmount;
                $student->save();
                if ($paymentType !== PaymentType::Debt) {
                    $paymentType = PaymentType::Balance;
                }
                Log::info("Balance updated for student. New balance: {$student->balance}"); // YENGI LOG
            } else {
                if ($paymentType !== PaymentType::Debt) {
                    $paymentType = PaymentType::Balance;
                }
            }

            $payment = new Payment();
            $payment->student_id = $student->id;
            $payment->group_id = $validatedData['group_id'];
            $payment->amount = $validatedData['amount'];
            $payment->date = $validatedData['date'];
            $payment->note = $validatedData['note'];
            $payment->type = $paymentType;
            $payment->debt_id = ($paymentType === PaymentType::Debt && !empty($debts->first())) ? $debts->first()->id : null;
            $payment->save();
            Log::info('Payment record created: ' . json_encode($payment->toArray())); // YENGI LOG

            DB::commit();
            Log::info('Transaction committed successfully.'); // YENGI LOG

            return redirect()->route('payments.index')->with('success', 'To\'lov muvaffaqiyatli qabul qilindi.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Payment store error: " . $e->getMessage() . " on line " . $e->getLine());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'To\'lovni qabul qilishda xato yuz berdi: ' . $e->getMessage());
        }
    }

    public function getStudentGroupDebt(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'student_id' => ['required', 'exists:students,id'],
                'group_id' => ['required', 'exists:groups,id'],
            ]);

            $student = Student::find($validatedData['student_id']);
            $group = Group::find($validatedData['group_id']);

            if (!$student || !$group) {
                Log::warning("Invalid student_id or group_id provided to getStudentGroupDebt. Student: " . ($student ? $student->id : 'null') . ", Group: " . ($group ? $group->id : 'null'));
                return response()->json([
                    'has_debt' => false,
                    'total_debt_amount' => 0,
                    'message' => "Talaba yoki guruh topilmadi.",
                ], 404);
            }

            $totalDebtAmount = Debt::where('student_id', $student->id)
                ->where('group_id', $group->id)
                ->whereIn('status', [DebtStatus::Unpaid, DebtStatus::Partial])
                ->sum(DB::raw('amount - paid_amount'));

            Log::info("getStudentGroupDebt called for Student ID: {$student->id}, Group ID: {$group->id}. Total debt: {$totalDebtAmount}");

            if ($totalDebtAmount > 0) {
                return response()->json([
                    'has_debt' => true,
                    'total_debt_amount' => $totalDebtAmount,
                    'message' => "Bu guruhda o'quvchining jami qarzdorligi: " . number_format($totalDebtAmount, 2, '.', ' ') . " UZS",
                ]);
            } else {
                return response()->json([
                    'has_debt' => false,
                    'total_debt_amount' => 0,
                    'message' => "Bu o'quvchining ushbu guruh uchun qarzdorligi yo'q.",
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error("Validation error in getStudentGroupDebt: " . json_encode($e->errors()));
            return response()->json([
                'has_debt' => false,
                'total_debt_amount' => 0,
                'message' => 'Kiritilgan ma\'lumotlar noto\'g\'ri: ' . implode(', ', array_values($e->errors())[0]),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Error in getStudentGroupDebt: " . $e->getMessage() . " on line " . $e->getLine());
            Log::error($e->getTraceAsString());
            return response()->json([
                'has_debt' => false,
                'total_debt_amount' => 0,
                'message' => 'Serverda kutilmagan xato yuz berdi. Iltimos, keyinroq urinib ko\'ring.',
            ], 500);
        }
    }

    public function getStudentsByGroup(Group $group)
    {
        try {
            $students = $group->students()->select('students.id', 'full_name')->get();
            Log::info("Fetching students for group ID: {$group->id}");
            Log::info("Found students: " . json_encode($students->toArray()));
            return response()->json($students);
        } catch (\Exception $e) {
            Log::error("Error fetching students for group ID {$group->id}: " . $e->getMessage());
            return response()->json(['error' => 'Talabalarni yuklashda xato yuz berdi.'], 500);
        }
    }

    public function getStudentDebts(Student $student)
    {
        $debts = $student->debts()
            ->with('group:id,name')
            ->whereIn('status', [DebtStatus::Unpaid, DebtStatus::Partial])
            ->orderBy('month')
            ->get()
            ->map(function ($debt) {
                $debt->total_due = $debt->amount - $debt->paid_amount;
                return $debt;
            });
        return response()->json($debts);
    }

    public function show(Payment $payment)
    {
        return Inertia::render('Payments/Show', [
            'payment' => $payment->load('student', 'debt'),
        ]);
    }

    public function edit(Payment $payment)
    {
        $students = Student::select('id', 'full_name')->orderBy('full_name')->get();
        return Inertia::render('Payments/Edit', [
            'payment' => $payment->load('student', 'debt'),
            'students' => $students,
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        $validatedData = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'note' => 'nullable|string|max:255',
            'group_id' => ['required', 'exists:groups,id'],
        ]);

        $payment->update($validatedData);

        return redirect()->route('payments.index')->with('success', 'To\'lov muvaffaqiyatli yangilandi!');
    }

    public function destroy(Payment $payment)
    {
        try {
            DB::transaction(function () use ($payment) {
                if ($payment->type === PaymentType::Debt && $payment->debt_id) {
                    $debt = Debt::find($payment->debt_id);
                    if ($debt) {
                        $debt->paid_amount -= $payment->amount;
                        if ($debt->paid_amount <= 0) {
                            $debt->status = DebtStatus::Unpaid;
                            $debt->is_paid = false;
                            $debt->paid_amount = 0;
                        } else {
                            $debt->status = DebtStatus::Partial;
                            $debt->is_paid = false;
                        }
                        $debt->save();
                    }
                } elseif ($payment->type === PaymentType::Balance) {
                    $student = $payment->student;
                    if ($student) {
                        $student->balance -= $payment->amount;
                        $student->save();
                    }
                }
                $payment->delete();
            });
            return redirect()->route('payments.index')->with('success', 'To\'lov muvaffaqiyatli o\'chirildi.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'To\'lovni o\'chirishda xatolik yuz berdi: ' . $e->getMessage());
        }
    }
}
