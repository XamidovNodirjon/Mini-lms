<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Group;
use App\Models\Debt;
use App\Models\Payment; // Payment modelini import qilish
use App\Enums\DebtStatus;
use App\Enums\PaymentType; // PaymentType enumini import qilish
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Tranzaksiyalar uchun

class GenerateMonthlyDebts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debts:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly debts for all students in active groups and automatically deduct from balance.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Oylik qarzdorliklarni yaratish va avtomatik to\'lash boshlandi...');

        $currentMonth = Carbon::now()->format('Y-m');

        // Guruhlarni talabalari bilan birga yuklab olamiz
        // Talabalarning balansini ham oldindan yuklash muhim
        $groups = Group::with(['students' => function ($query) {
            $query->select('students.id', 'full_name', 'balance'); // Faqat kerakli maydonlarni yuklaymiz
        }])->get();

        $generatedCount = 0;
        $paidAutoCount = 0;
        $partialAutoCount = 0;

        foreach ($groups as $group) {
            $monthlyFee = (float) $group->monthly_fee; // Decimaldan floatga o'tkazamiz

            foreach ($group->students as $student) {
                // Ushbu talaba va guruh uchun shu oyda qarz allaqachon mavjudligini tekshiradi
                $existingDebt = Debt::where('student_id', $student->id)
                    ->where('group_id', $group->id)
                    ->where('month', $currentMonth)
                    ->first();

                if ($existingDebt) {
                    $this->warn("Talaba {$student->full_name} ({$student->id}) va guruh {$group->name} ({$group->id}) uchun {$currentMonth} oyiga qarz allaqachon mavjud. O'tkazib yuborildi.");
                    continue;
                }

                DB::beginTransaction(); // Tranzaksiya boshlandi

                try {
                    // Yangi qarzni yaratamiz (avvalgi koddan farqli o'laroq, endi bu joyda oldinroq yaratilmaydi, chunki balansni tekshirish kerak)
                    $debt = new Debt([
                        'student_id' => $student->id,
                        'group_id' => $group->id,
                        'amount' => $monthlyFee,
                        'month' => $currentMonth,
                        'paid_amount' => 0.00,
                        'is_paid' => false,
                        'status' => DebtStatus::Unpaid,
                    ]);

                    $remainingAmount = $monthlyFee;
                    $amountPaidFromBalance = 0.00;

                    // Talabaning balansini tekshiramiz va avtomatik to'lovni amalga oshiramiz
                    if ($student->balance > 0) {
                        $this->line("Talaba {$student->full_name} ({$student->id}) balansida {$student->balance} UZS bor. Qarz: {$monthlyFee} UZS.");

                        if ($student->hasSufficientBalance($remainingAmount)) {
                            // Balans yetarli, qarzni to'liq qoplaymiz
                            $student->withdraw($remainingAmount);
                            $amountPaidFromBalance = $remainingAmount;
                            $remainingAmount = 0;
                            $debt->status = DebtStatus::Paid;
                            $debt->is_paid = true;
                            $this->info("  -> Balansdan to'liq {$amountPaidFromBalance} UZS yechib olindi. Qarz to'liq to'landi.");
                        } else {
                            // Balans yetarli emas, qisman to'laymiz
                            $amountPaidFromBalance = (float) $student->balance;
                            $student->withdraw($amountPaidFromBalance); // Balansdagi barcha pulni yechib olamiz
                            $remainingAmount -= $amountPaidFromBalance;
                            $debt->status = DebtStatus::Partial;
                            $debt->is_paid = false;
                            $this->warn("  -> Balansdan {$amountPaidFromBalance} UZS yechib olindi. Qolgan qarz: {$remainingAmount} UZS.");
                        }

                        // To'lovni hisoblash
                        $debt->paid_amount += $amountPaidFromBalance;

                        // To'lov yozuvini yaratish
                        if ($amountPaidFromBalance > 0) {
                            Payment::create([
                                'student_id' => $student->id,
                                'amount' => $amountPaidFromBalance,
                                'date' => Carbon::now()->toDateString(),
                                'note' => 'Oylik qarz uchun avtomatik to\'lov (balansdan)',
                                'type' => PaymentType::Debt,
                                'debt_id' => null, // Hozircha null, qarz yaratilgandan keyin bog'laymiz
                            ]);
                        }
                    }

                    $debt->save(); // Qarz yozuvini saqlash

                    // Agar avtomatik to'lov bo'lgan bo'lsa, Payment modelidagi debt_id ni yangilaymiz
                    if ($amountPaidFromBalance > 0) {
                        $latestPayment = Payment::where('student_id', $student->id)
                                                ->where('type', PaymentType::Debt)
                                                ->whereNull('debt_id')
                                                ->latest()->first(); // Eng oxirgi debt_id null bo'lgan to'lov

                        if ($latestPayment) {
                            $latestPayment->debt_id = $debt->id;
                            $latestPayment->save();
                        }
                    }

                    $generatedCount++;
                    if ($debt->status === DebtStatus::Paid) {
                        $paidAutoCount++;
                    } elseif ($debt->status === DebtStatus::Partial) {
                        $partialAutoCount++;
                    }
                    $this->line("Talaba {$student->full_name} ({$student->id}) uchun {$currentMonth} oyiga {$monthlyFee} UZS miqdorida qarz yaratildi. Holati: " . $debt->status->value);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack(); // Xatolik bo'lsa tranzaksiyani bekor qilish
                    $this->error("Talaba {$student->full_name} ({$student->id}) uchun qarz yaratish va to'lashda xatolik yuz berdi: " . $e->getMessage());
                }
            }
        }

        $this->info("Jami {$generatedCount} ta yangi qarzdorlik yozuvi yaratildi.");
        $this->info("Shundan {$paidAutoCount} tasi balansdan to'liq avtomatik to'landi.");
        $this->info("Shundan {$partialAutoCount} tasi balansdan qisman avtomatik to'landi.");
        $this->info('Oylik qarzdorliklarni yaratish va avtomatik to\'lash yakunlandi.');

        return Command::SUCCESS;
    }
}