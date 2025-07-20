<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Group::factory()->count(3)
            ->create()
            ->each(function ($group) {
                $students = Student::inRandomOrder()->limit(rand(3, 4))->get();
                $group->students()->attach($students);
            }
        );
    }
}
