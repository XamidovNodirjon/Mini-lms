<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'teacher_id',
        'monthly_fee',
        'start_date',
        'time',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }


    public function students()
    {
        return $this->belongsToMany(Student::class);
    }
}
