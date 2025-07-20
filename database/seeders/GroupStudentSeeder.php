<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Group;
use App\Models\Student;

class GroupStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = Group::all();
        $students = Student::all();

        if ($groups->isEmpty() || $students->isEmpty()) {
            echo "Guruhlar yoki talabalar mavjud emas. Avval ularni yaratganingizga ishonch hosil qiling.\n";
            return;
        }

        foreach ($students as $student) {

            $numberOfGroupsToAttach = rand(1, min($groups->count(), 2));

            $randomGroupIds = $groups->random($numberOfGroupsToAttach)
                ->pluck('id')
                ->toArray();

            $student->groups()->syncWithoutDetaching($randomGroupIds);
        }

        echo "Talabalar guruhlarga muvaffaqiyatli biriktirildi (har bir talaba 1-2 guruhga).\n";
    }
}
