<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word() . ' Group',
            'teacher_id' => User::factory(),
            'monthly_fee' => $this->faker->randomFloat(2, 500, 3000000),
            'start_date' => $this->faker->date(),
            'time' => $this->faker->time('H:i') . '-' . $this->faker->time('H:i'),
        ];
    }
}
