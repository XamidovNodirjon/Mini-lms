<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'phone' => $this->faker->unique()->phoneNumber(),
            'birth_date' => $this->faker->date(),
            'balance' => $this->faker->randomFloat(2, -1000, 5000000),
        ];
    }
}
