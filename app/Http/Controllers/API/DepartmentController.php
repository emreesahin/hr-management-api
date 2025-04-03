<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{

    // Department List
    public function index (Request $request) {

        $request -> validate([
            'with_employees' => 'sometimes|boolean',
            'only_active' => 'sometimes|boolean'
        ]);

        $query = Deparment::query();

        if($request -> boolean('with_employees')) {
            $query -> with([
                'activeEmployees' => function ($query) {
                    $query -> select(['user.id', 'name', 'email'])
                        ->withPivot('position');

                }
            ]);

        if($request -> boolean('only_active')) {
            $query -> where('is_active', true);
        }

        $departments = $query -> whereNull('parent_id')-> get();

        return response()->json([
            $this->buildTree($departments)
        ]);

        }
    }

    // Create Department

    public function store(Request $request) {
        $validated = $request -> validate([
            'name' => 'required|string|max:255|unique:departments',
            'description' => 'nullable|string',
            'manager_id' => [
                'nullable',
                'integer',
                Rule::exist('user', 'id')->where(function($query) {
                    $query->where('is_active', true);
                })
            ],
            'parent_id' => 'nullable|exists:departments:id',
            'is_active' => 'boolean'
        ]);


        if($validated['manager_id'] && $this->isUserManagerElsewhere($validated['manager_id']))
    {
        return response()->json([
            'message' => 'Bu kullanıcı zaten başka bir departmanda yönetici',
            'errors' => ['manager_id' => ['User is already manager of another department']]
        ], 422);
    }

    $department = Department::create($validated);

    return response() -> json([
        'message' => 'Departman başarıyla oluşturuldu',
        'data' => $department
    ], 201);

    }

    // Department Details

    public function show(Department $department)
    {
        return response()->json([
            'department' => $department->load(['manager', 'parent']),
            'employees' => $department->activeEmployees()
                ->select(['users.id', 'name', 'email', 'phone'])
                ->withPivot('position', 'start_date')
                ->get(),
            'stats' => [
                'employee_count' => $department->activeEmployees->count(),
                'sub_departments' => $department->children()->count()
            ]
        ]);
    }

    // Update Department

    public function update(Request $request, Department $department) {

        $validated = $request -> validate ([
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('departments')->ignore($department->id)
            ],
            'description' => 'nullable|string',

            'manager_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id') -> where('is_active', true)
            ],
            'parent_id' => [
                'nullable',
                'exists:departments,id',
                function ($attribute, $value, $fail) use ($department) {
                    if ($value == $department->id) {
                        $fail('Departman kendisinin alt departmanı olamaz');
                    }
                }
            ],
            'is_active' => 'boolean'
        ]);

        if (isset($validated['manager_id']) &&
        $validated['manager_id'] != $department->manager_id &&
        $this->isUserManagerElsewhere($validated['manager_id'])) {
        return response()->json([
            'message' => 'Bu kullanıcı zaten başka bir departmanda yönetici',
            'errors' => ['manager_id' => ['User is already manager of another department']]
        ], 422);
    }

    $department->update($validated);

    return response()->json([
        'message' => 'Departman güncellendi',
        'data' => $department
    ]);
    }


    // Delete Department

    public function destroy(Department $department)
    {
        // Silinemez durumları kontrol et
        if ($department->activeEmployees()->exists()) {
            return response()->json([
                'message' => 'Bu departmanda aktif çalışanlar var',
                'errors' => ['department' => ['Department has active employees']]
            ], 422);
        }

        if ($department->children()->exists()) {
            return response()->json([
                'message' => 'Bu departmanın alt departmanları var',
                'errors' => ['department' => ['Department has sub departments']]
            ], 422);
        }

        $department->delete();

        return response()->json([
            'message' => 'Departman silindi',
            'data' => null
        ], 204);
    }

    // Assign Employee

    public function assignEmployee(Request $request, Department $department)
    {
        $validated = $request->validate([
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('is_active', true),
                function ($attribute, $value, $fail) use ($department) {
                    if ($department->employees()->where('users.id', $value)->wherePivotNull('end_date')->exists()) {
                        $fail('Bu çalışan zaten bu departmanda aktif olarak görev yapıyor');
                    }
                }
            ],
            'position' => 'required|string|max:255',
            'start_date' => 'nullable|date|before_or_equal:today',
            'end_date' => 'nullable|date|after:start_date'
        ]);

        // Eski departman atamasını kapat
        DB::table('employee_department')
            ->where('employee_id', $validated['employee_id'])
            ->whereNull('end_date')
            ->update(['end_date' => now()]);

        $department->employees()->attach($validated['employee_id'], [
            'position' => $validated['position'],
            'start_date' => $validated['start_date'] ?? now(),
            'end_date' => $validated['end_date'] ?? null
        ]);

        return response()->json([
            'message' => 'Çalışan departmana atandı',
            'data' => [
                'employee' => User::find($validated['employee_id'])->only(['id', 'name', 'email']),
                'position' => $validated['position'],
                'department' => $department->only(['id', 'name'])
            ]
        ], 201);
    }


    // Hieararchy Changes

    public function hierarchy(Department $department)
    {
        return response()->json([
            'department' => $department->only(['id', 'name', 'manager_id']),
            'children' => $this->getChildren($department)
        ]);
    }

    // Department Report

    public function report(Department $department)
    {
        $positions = DB::table('employee_department')
            ->where('department_id', $department->id)
            ->whereNull('end_date')
            ->select('position', DB::raw('count(*) as count'))
            ->groupBy('position')
            ->get();

        return response()->json([
            'employee_count' => $department->activeEmployees->count(),
            'positions' => $positions,
            'avg_tenure' => $department->activeEmployees()
                ->avg(DB::raw('DATEDIFF(NOW(), employee_department.start_date)'))
        ]);
    }

    // Private Functions

    private function buildTree($departments)
    {
        return $departments->map(function ($department) {
            return [
                'id' => $department->id,
                'name' => $department->name,
                'manager' => $department->manager?->only(['id', 'name']),
                'employee_count' => $department->activeEmployees->count(),
                'children' => $this->buildTree($department->children)
            ];
        });
    }

    private function getChildren(Department $department)
    {
        return $department->children->map(function ($child) {
            return [
                'department' => $child->only(['id', 'name', 'manager_id']),
                'children' => $this->getChildren($child)
            ];
        });
    }

    private function isUserManagerElsewhere($userId, $ignoreDepartmentId = null)
    {
        $query = Department::where('manager_id', $userId);

        if ($ignoreDepartmentId) {
            $query->where('id', '!=', $ignoreDepartmentId);
        }

        return $query->exists();
    }

}
