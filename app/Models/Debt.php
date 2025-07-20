<?php

namespace App\Models;

use App\Enums\DebtStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'group_id',
        'amount',
        'month',
        'paid_amount',
        'is_paid',
        'status',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'status' => DebtStatus::class,
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
