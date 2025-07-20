<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $students = Student::query()
            ->when($search, function ($query, $search) {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->with('groups')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Students/Index', [
            'students' => $students,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }


    public function create()
    {
        $groups = Group::orderBy('name')->get(['id', 'name']);
        return Inertia::render('Students/Create', [
            'groups' => $groups,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:students,phone',
            'birth_date' => 'nullable|date',
            'balance' => 'nullable|numeric|min:0',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:groups,id',
        ]);

        $student = Student::create($validatedData);

        if (isset($validatedData['group_ids'])) {
            $student->groups()->sync($validatedData['group_ids']);
        }

        return redirect()->route('students.index')->with('success', 'O\'quvchi muvaffaqiyatli qo\'shildi.');
    }

    public function show(Student $student)
    {
        $student->load([
            'groups',
            'payments' => function ($query) {
                $query->orderBy('date', 'desc')->orderBy('created_at', 'desc');
            },
            'debts' => function ($query) {
                $query->with('group')->orderBy('month', 'desc');
            }
        ]);

        $groups = Group::select('id', 'name')->get();

        Log::info("Student data for show page: " . json_encode($student->toArray()));
        Log::info("Student debts with groups: " . json_encode($student->debts->toArray()));


        return Inertia::render('Students/Show', [
            'student' => $student,
            'groups' => $groups,
        ]);
    }


    public function edit(Student $student)
    {
        $groups = Group::orderBy('name')->get(['id', 'name']);
        $student->load('groups');

        return Inertia::render('Students/Edit', [
            'student' => $student,
            'groups' => $groups,
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $validatedData = $request->validate([
            'full_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20|unique:students,phone,' . $student->id,
            'birth_date' => 'nullable|date',
            'balance' => 'nullable|numeric|min:0',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:groups,id',
        ]);

        $student->update(array_filter($validatedData, fn($value) => !is_null($value)));

        $student->groups()->sync($validatedData['group_ids'] ?? []);

        return redirect()->route('students.index')->with('success', 'O\'quvchi ma\'lumotlari muvaffaqiyatli yangilandi.');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('students.index')->with('success', 'O\'quvchi muvaffaqiyatli o\'chirildi.');
    }
}
