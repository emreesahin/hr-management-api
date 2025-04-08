<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'manager_id',
        'parent_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships

    /**
     * Get the manager of the department
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id')->withDefault([
            'name' => 'No Manager Assigned'
        ]);
    }

    /**
     * Get the parent department
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    /**
     * Get child departments
     */
    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    /**
     * Get all employees in this department
     */
    public function employees()
    {
        return $this->belongsToMany(User::class, 'employee_department', 'department_id', 'employee_id')
            ->withPivot('position', 'start_date', 'end_date');
    }

    // Scopes

    /**
     * Scope for active departments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for departments with employees
     */
    public function scopeWithEmployees($query)
    {
        return $query->with(['employees' => function($query) {
            $query->wherePivotNull('end_date')
                  ->orWherePivot('end_date', '>', now());
        }]);
    }

    // Custom Methods

    /**
     * Get active employees in the department
     */
    public function activeEmployees()
    {
        return $this->employees()
                    ->wherePivotNull('end_date')
                    ->orWherePivot('end_date', '>', now());
    }

    /**
     * Check if user is manager of another department
     */
    public static function isUserManagerElsewhere($userId, $ignoreDepartmentId = null)
    {
        $query = self::where('manager_id', $userId);

        if ($ignoreDepartmentId) {
            $query->where('id', '!=', $ignoreDepartmentId);
        }

        return $query->exists();
    }

    /**
     * Get department hierarchy
     */
    public function getHierarchyAttribute()
    {
        return [
            'department' => $this->only(['id', 'name', 'manager_id']),
            'children' => $this->children->map->hierarchy
        ];
    }

    /**
     * Get average tenure of employees in days
     */
    public function getAverageTenureAttribute()
    {
        return $this->activeEmployees()
                   ->avg(DB::raw('DATEDIFF(NOW(), employee_department.start_date)'));
    }

    /**
     * Get position statistics
     */
    public function getPositionStatsAttribute()
    {
        return DB::table('employee_department')
               ->where('department_id', $this->id)
               ->whereNull('end_date')
               ->select('position', DB::raw('count(*) as count'))
               ->groupBy('position')
               ->get();
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function($department) {
            // Prevent deletion if has active employees
            if ($department->activeEmployees()->exists()) {
                throw new \Exception('Cannot delete department with active employees');
            }

            // Convert children to root departments when deleting
            $department->children()->update(['parent_id' => null]);
        });
    }
}
