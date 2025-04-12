<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{

    protected $fillable = [
        'employee_id',
        'start_date',
        'end_date',
        'duration',
        'reason',
        'status'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
