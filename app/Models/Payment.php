<?php

namespace App\Models;

use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'amount',
        'date',
        'note',
        'type',
        'debt_id',
    ];

    protected $casts = [
        'date' => 'date',
        'type' => PaymentType::class,
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
