<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $teachers = User::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);
        return Inertia::render('Teachers/Index', [
            'teachers' => $teachers,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Teachers/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255|unique:users,phone',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $request->full_name,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
        ]);

        return redirect()->route('teachers.index')->with('success', 'O\'qituvchi muvaffaqiyatli qo\'shildi.');
    }

    public function show(User $teacher)
    {
        return Inertia::render('Teachers/Show', [
            'teacher' => $teacher,
        ]);
    }

    public function edit(User $teacher)
    {
        return Inertia::render('Teachers/Edit', [
            'teacher' => $teacher,
        ]);
    }

    public function update(Request $request, User $teacher)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:255|unique:users,phone,' . $teacher->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $updateData = [];

        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }

        if ($request->has('phone')) {
            $updateData['phone'] = $request->phone;
        }

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password); // bcrypt o'rniga Hash::make()
        }

        if (empty($updateData)) {
            return redirect()->route('teachers.index')->with('info', 'Hech qanday ma\'lumot o\'zgartirilmadi.');
        }

        $teacher->update($updateData);

        return redirect()->route('teachers.index')->with('success', 'O\'qituvchi ma\'lumotlari muvaffaqiyatli yangilandi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $teacher)
    {
        $teacher->delete();
        return redirect()->route('teachers.index')->with('success', 'O\'qituvchi muvaffaqiyatli o\'chirildi.');
    }

}
