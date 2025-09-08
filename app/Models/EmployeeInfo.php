<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EmployeeInfo extends Model
{
    use HasFactory;

    protected $table = 'tb_employeeinfo';
    protected $primaryKey = 'Emp_No';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'Emp_No',
        'Titles',
        'LastName',
        'FirstName',
        'MiddleName',
        'Suffix',
        'Email',
    ];

<<<<<<< HEAD
    /**
     * Get the employee's full name.
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->Titles,
            $this->FirstName,
            $this->MiddleName,
            $this->LastName,
            $this->Suffix
        ]);
        return implode(' ', $parts);
    }

=======
>>>>>>> b9beceb5b2f09379b09569e8f3475ddab1b2fd80
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'Emp_No', 'Emp_No');
    }
}