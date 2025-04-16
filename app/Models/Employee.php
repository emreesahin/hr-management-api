<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'user_id', 'employee_number', 'hire_date', 'birth_date',
        'gender', 'national_id', 'address', 'phone',
        'emergency_contact', 'position', 'salary'
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'employee_department', 'employee_id', 'department_id')
                    ->withPivot('position', 'start_date', 'end_date', 'is_primary');
    }

    public function payroll()
    {
        return $this->hasMany(Payroll::class);
    }
}
