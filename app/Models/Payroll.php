<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'period',
        'gross_salary',
        'deductions',
        'bonuses',
        'net_salary',
        'issued_at'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
