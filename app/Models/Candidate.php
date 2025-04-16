<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class Candidate extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles, SoftDeletes;

    protected $guard_name = 'candidate';

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'cv_path',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
}
