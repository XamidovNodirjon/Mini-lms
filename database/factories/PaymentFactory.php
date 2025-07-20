<?php

namespace Database\Factories;

use App\Enums\PaymentType;
use App\Models\Debt;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Payment::class;

    public function definition(): array
    {
        $student = Student::inRandomOrder()->first();
        if (!$student) {
            return [];
        }

        $type = $this->faker->randomElement([PaymentType::Debt, PaymentType::Balance]);
        $debtId = null;
        $groupId = null;

        if ($type === PaymentType::Debt) {
            $debt = Debt::where('student_id', $student->id)
                ->where('status', '!=', 'paid')
                ->inRandomOrder()
                ->first();
            if ($debt) {
                $debtId = $debt->id;
                $groupId = $debt->group_id;
            } else {
                $type = PaymentType::Balance;
            }
        }

        if (is_null($groupId)) {
            $studentGroups = $student->groups()->pluck('groups.id')->toArray();
            if (!empty($studentGroups)) {
                $groupId = $this->faker->randomElement($studentGroups);
            } else {
                $randomGroup = Group::inRandomOrder()->first();
                if ($randomGroup) {
                    $groupId = $randomGroup->id;
                } else {
                    return [];
                }
            }
        }

        return [
            'student_id' => $student->id,
            'group_id' => $groupId,
            'amount' => $this->faker->randomFloat(2, 50000, 5000000),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'note' => $this->faker->optional()->sentence(),
            'type' => $type,
            'debt_id' => $debtId,
        ];
    }
}
