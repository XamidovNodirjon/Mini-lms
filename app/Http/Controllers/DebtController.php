<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\Student;
use App\Models\Group;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use App\Enums\DebtStatus;

// DebtStatus enum'ini import qilish

class DebtController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Debt::with(['student:id,full_name', 'group:id,name']);

        if ($request->has('search') && $request->input('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('amount', 'like', "%{$search}%")
                    ->orWhere('month', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('full_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('group', function ($gq) use ($search) {
                        $gq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('status') && $request->input('status') && $request->input('status') !== 'all') {
            $status = $request->input('status');
            $query->where('status', $status);
        }

        $debts = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Debts/Index', [
            'debts' => $debts,
            'filters' => $request->only(['search', 'status']),
            'statuses' => array_column(DebtStatus::cases(), 'value'),
        ]);
    }

    public function create()
    {
        $students = Student::orderBy('full_name')->get(['id', 'full_name']);
        $groups = Group::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Debts/Create', [
            'students' => $students,
            'groups' => $groups,
            'statuses' => array_column(DebtStatus::cases(), 'value'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'group_id' => ['required', 'exists:groups,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'month' => ['required', 'string', 'max:255'],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'is_paid' => ['boolean'], // Avtomatik to'g'irlanadi
            'status' => ['required', Rule::in(array_column(DebtStatus::cases(), 'value'))],
        ]);

        if (isset($validated['paid_amount']) && $validated['paid_amount'] > $validated['amount']) {
            return redirect()->back()->withErrors(['paid_amount' => 'To\'langan miqdor qarz miqdoridan oshmasligi kerak.']);
        }
        $validated['paid_amount'] = $validated['paid_amount'] ?? 0;
        if ($validated['paid_amount'] >= $validated['amount']) {
            $validated['status'] = DebtStatus::Paid->value;
            $validated['is_paid'] = true;
        } elseif ($validated['paid_amount'] > 0) {
            $validated['status'] = DebtStatus::Partial->value;
            $validated['is_paid'] = false;
        } else {
            $validated['status'] = DebtStatus::Unpaid->value;
            $validated['is_paid'] = false;
        }

        Debt::create($validated);

        return redirect()->route('debts.index')->with('success', 'Qarz muvaffaqiyatli qo\'shildi.');
    }

    public function show(Debt $debt)
    {
        $debt->load(['student:id,full_name', 'group:id,name', 'payments']); // studentdan 'name' o'rniga 'full_name'
        return Inertia::render('Debts/Show', [
            'debt' => $debt,
        ]);
    }

    public function edit(Debt $debt)
    {
        $students = Student::orderBy('full_name')->get(['id', 'full_name']); // studentdan 'name' o'rniga 'full_name'
        $groups = Group::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Debts/Edit', [
            'debt' => $debt,
            'students' => $students,
            'groups' => $groups,
            'statuses' => array_column(DebtStatus::cases(), 'value'),
        ]);
    }

    public function update(Request $request, Debt $debt)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'group_id' => ['required', 'exists:groups,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'month' => ['required', 'string', 'max:255'],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'is_paid' => ['boolean'],
            'status' => ['required', Rule::in(array_column(DebtStatus::cases(), 'value'))],
        ]);

        if (isset($validated['paid_amount']) && $validated['paid_amount'] > $validated['amount']) {
            return redirect()->back()->withErrors(['paid_amount' => 'To\'langan miqdor qarz miqdoridan oshmasligi kerak.']);
        }

        $validated['paid_amount'] = $validated['paid_amount'] ?? 0;
        if ($validated['paid_amount'] >= $validated['amount']) {
            $validated['status'] = DebtStatus::Paid->value;
            $validated['is_paid'] = true;
        } elseif ($validated['paid_amount'] > 0) {
            $validated['status'] = DebtStatus::Partial->value;
            $validated['is_paid'] = false;
        } else {
            $validated['status'] = DebtStatus::Unpaid->value;
            $validated['is_paid'] = false;
        }


        $debt->update($validated);

        return redirect()->route('debts.index')->with('success', 'Qarz muvaffaqiyatli yangilandi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Debt $debt)
    {
        $debt->delete();
        return redirect()->route('debts.index')->with('success', 'Qarz muvaffaqiyatli o\'chirildi.');
    }
}
