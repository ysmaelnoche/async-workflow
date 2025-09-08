<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubDepartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'subdepartment_code',
        'name',
        'description',
        'supervisor_id'
    ];

    // Relationships
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'sub_department_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(User::class, 'sub_department_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id', 'accnt_id');
    }
}

