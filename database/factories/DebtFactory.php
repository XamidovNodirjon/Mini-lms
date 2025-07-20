<?php

namespace Database\Factories;

use App\Enums\DebtStatus;
use App\Models\Debt;
use App\Models\Group;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Debt>
 */
class DebtFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Debt::class;

    public function definition(): array
    {
        $student = Student::inRandomOrder()->first();
        $group = Group::inRandomOrder()->first();

        if (!$student || !$group) {
            return [];
        }

        $month = Carbon::parse($this->faker->dateTimeBetween('-6 months', 'now'))
            ->startOfMonth()
            ->format('Y-m-d');

        if (Debt::where('student_id', $student->id)
            ->where('group_id', $group->id)
            ->where('month', $month)
            ->exists()) {
            return [];
        }

        $amount = $this->faker->randomFloat(2, 500, 3000000);
        $paidAmount = $this->faker->randomFloat(2, 0, $amount);
        $status = DebtStatus::Unpaid;

        if ($paidAmount > 0 && $paidAmount < $amount) {
            $status = DebtStatus::Partial;
        } elseif ($paidAmount >= $amount) {
            $status = DebtStatus::Paid;
            $paidAmount = $amount;
        }

        return [
            'student_id' => $student->id,
            'group_id' => $group->id,
            'amount' => $amount,
            'month' => $month,
            'paid_amount' => $paidAmount,
            'is_paid' => ($status === DebtStatus::Paid),
            'status' => $status,
        ];
    }

    public function uniqueForStudentAndGroupAndMonth(): self
    {
        return $this->afterMaking(function (Debt $debt) {
        });
    }
}
