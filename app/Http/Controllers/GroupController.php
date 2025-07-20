<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GroupController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->input('search');

        $teachers = User::select('id', 'name')->get();

        $groups = Group::query()
            ->with('teacher')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('teacher', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Groups/Index', [
            'groups' => $groups,
            'teachers' => $teachers, // Prop nomini "teachers" ga o'zgartiring
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create()
    {
        $teachers = User::select('id', 'name')->get();
        return Inertia::render('Groups/Create', [
            'teachers' => $teachers,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'teacher_id' => 'required|exists:users,id',
            'monthly_fee' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'time' => 'required|string|max:50',
        ]);

        Group::create($validatedData);

        return redirect()->route('groups.index')->with('success', 'Guruh muvaffaqiyatli qo\'shildi.');
    }


    public function show(Group $group)
    {
        $group->load('teacher');
        $teachers = User::select('id', 'name')->get();

        return Inertia::render('Groups/Show', [
            'group' => $group,
            'teachers' => $teachers,
        ]);
    }

    public function edit(Group $group)
    {
        $teachers = User::select('id', 'name')->get();
        $group->load('teacher');
        return Inertia::render('Groups/Edit', [
            'group' => $group,
            'teachers' => $teachers,
        ]);
    }

    public function update(Request $request, Group $group)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:groups,name,' . $group->id,
            'teacher_id' => 'sometimes|required|exists:users,id',
            'monthly_fee' => 'sometimes|required|numeric|min:0',
            'start_date' => 'sometimes|required|date',
            'time' => 'sometimes|required|string|max:255',
        ]);

        $group->update($validatedData);

        return back()->with('success', 'Guruh ma\'lumotlari muvaffaqiyatli yangilandi.');
    }

    public function destroy(Group $group)
    {
        $group->delete();
        return back()->with('success', 'Guruh muvaffaqiyatli o\'chirildi.');
    }
}
