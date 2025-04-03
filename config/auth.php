<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'api'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
            'hash' => false,
        ],

        'api' => [
            'driver' => 'sanctum',
            'provider' => 'users',
            'hash' => false,
        ],

        'hr-api' => [
            'driver' => 'sanctum',
            'provider' => 'hr_users',
        ],

        'admin-api' => [
            'driver' => 'sanctum',
            'provider' => 'admins',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        'hr_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\HrUser::class,
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'employees',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'hr_users' => [
            'provider' => 'hr_users',
            'table' => 'hr_password_reset_tokens',
            'expire' => 120,
            'throttle' => 120,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */
    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

    /*
    |--------------------------------------------------------------------------
    | Permission Configuration
    |--------------------------------------------------------------------------
    */
    'permission' => [
        'models' => [
            'permission' => Spatie\Permission\Models\Permission::class,
            'role' => Spatie\Permission\Models\Role::class,
        ],

        'table_names' => [
            'roles' => 'roles',
            'permissions' => 'permissions',
            'model_has_permissions' => 'model_has_permissions',
            'model_has_roles' => 'model_has_roles',
            'role_has_permissions' => 'role_has_permissions',
        ],

        'column_names' => [
            'model_morph_key' => 'model_id',
        ],

        'display_permission_in_exception' => env('PERMISSION_DISPLAY_EXCEPTION', false),
    ],
];
