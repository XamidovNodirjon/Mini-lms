<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User; // O'qituvchilar User modelida bo'lishi kerak
use App\Models\Group;
use App\Models\Payment;
use App\Models\Debt;
use App\Enums\DebtStatus; // Agar DebtStatus enum ishlatilsa
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB; // DB facade ni import qiling
use Illuminate\Support\Facades\Log; // Log facade ni import qiling

class DashboardController extends Controller
{
    public function index()
    {
        $totalStudents = Student::count();
        Log::info('Dashboard: Total Students = ' . $totalStudents);

        $totalTeachers = User::count();
        Log::info('Dashboard: Total Teachers = ' . $totalTeachers);

        // Jami guruhlar soni
        $totalGroups = Group::count();
        Log::info('Dashboard: Total Groups = ' . $totalGroups);

        $today = Carbon::today();
        $todayRevenue = Payment::whereDate('date', $today)->sum('amount');
        Log::info('Dashboard: Today\'s Revenue (' . $today->toDateString() . ') = ' . $todayRevenue);


        $debtStatusPaidValue = 'paid';
        if (class_exists(DebtStatus::class) && method_exists(DebtStatus::Paid, 'value')) {
            $debtStatusPaidValue = DebtStatus::Paid->value;
        }

        $totalDebt = Debt::where('status', '!=', $debtStatusPaidValue)
            ->sum(DB::raw('amount - paid_amount'));
        Log::info('Dashboard: Total Debt = ' . $totalDebt); // Log qo'shamiz

        return Inertia::render('Dashboard', [
            'totalStudents' => (int) $totalStudents,
            'totalTeachers' => (int) $totalTeachers,
            'totalGroups' => (int) $totalGroups,
            'todayRevenue' => (float) $todayRevenue,
            'totalDebt' => (float) $totalDebt,
        ]);
    }
}
