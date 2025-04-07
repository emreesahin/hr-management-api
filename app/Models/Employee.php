<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'name', 'user_id', 'employee_number', 'hire_date', 'birth_date',
        'gender', 'national_id', 'address', 'phone',
        'emergency_contact', 'position', 'salary'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'employee_department')
                   ->withPivot('position', 'start_date', 'end_date');
    }

    public function payroll()
    {
        return $this->hasMany(Payroll::class);
    }
}
