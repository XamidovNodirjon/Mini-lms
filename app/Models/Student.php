<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'phone',
        'birth_date',
        'balance',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'balance' => 'decimal:2',
    ];

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }
    public function deposit(float $amount): void
    {
        $this->balance += $amount;
        $this->save();
    }

    public function withdraw(float $amount): bool
    {
        if ($this->balance >= $amount) {
            $this->balance -= $amount;
            $this->save();
            return true;
        }
        return false;
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }
}
