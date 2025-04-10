<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    // Show Employees
    public function index()
    {
        return Employee::with(['user', 'departments' => function ($query) {
            $query->where(function ($q) {
                $q->whereNull('employee_department.end_date')
                  ->orWhere('employee_department.end_date', '>', now());
            });
        }])->get();
    }

    // Show Employee
    public function show(Employee $employee)
    {
        return response()->json([
            'employee' => $employee->load(['user', 'departments']),
            'assignment_history' => $employee->assignmentHistory()
        ]);
    }

    // Create Employee
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'department_id' => 'required|exists:departments,id',
                'position' => 'required|string|max:255',
                'salary' => 'required|numeric|min:0',
                'hire_date' => 'required|date',
                'birth_date' => 'required|date|before:today',
                'gender' => ['required', Rule::in(['male', 'female', 'other'])],
                'national_id' => ['required', 'string', 'max:20', Rule::unique('employees', 'national_id')],
                'address' => 'required|string|max:500',
                'phone' => 'required|string|max:15',
                'emergency_contact' => 'required|string|max:50'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $user = User::findOrFail($validated['user_id']);

            if ($user->employee) {
                return response()->json([
                    'message' => 'Bu kullanıcı zaten çalışan olarak tanımlanmış.'
                ], 422);
            }

            $employee = $user->employee()->create([
                'employee_number' => 'EMP-' . now()->format('Ymd') . '-' . str_pad(Employee::max('id') + 1, 4, '0', STR_PAD_LEFT),
                'position' => $validated['position'],
                'salary' => $validated['salary'],
                'hire_date' => $validated['hire_date'],
                'birth_date' => $validated['birth_date'],
                'gender' => $validated['gender'],
                'national_id' => $validated['national_id'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'emergency_contact' => $validated['emergency_contact'],
            ]);

            $employee->departments()->attach($validated['department_id'], [
                'position' => $validated['position'],
                'start_date' => now(),
                'is_primary' => true
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Çalışan başarıyla oluşturuldu',
                'data' => $employee->load(['user', 'departments'])
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            \Log::error('Çalışan kaydederken patladı:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'message' => 'Sunucu hatası oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // Update Employee
    public function update(Request $request, Employee $employee)
    {
        try {
            $validated = $this->validateEmployeeData($request, $employee->user_id);
            DB::beginTransaction();
            $this->updateEmployee($employee, $validated);
            DB::commit();

            return response()->json([
                'message' => 'Çalışan güncellendi',
                'data' => $employee->fresh()->load('user')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    // Delete Employee
    public function destroy(Employee $employee)
    {
        DB::beginTransaction();
        try {
            $employee->user()->update(['is_active' => false]);
            $employee->departments()->detach();
            $employee->delete();

            DB::commit();
            return response()->json(['message' => 'Çalışan silindi'], 204);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    // Assign Employee to Department
    public function assignToDepartment(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'position' => 'required|string|max:255',
            'is_primary' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $this->processDepartmentAssignment($employee, $validated);
            DB::commit();

            return response()->json([
                'message' => 'Çalışan departmana atandı',
                'data' => $employee->fresh()->departments
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    // Update Department Assignment
    public function updateDepartmentAssignment(Request $request, Employee $employee, Department $department)
    {
        try {
            $validated = $request->validate([
                'position' => 'required|string|max:255',
                'end_date' => 'nullable|date|after:start_date',
                'is_primary' => 'boolean'
            ]);

            DB::beginTransaction();
            $this->processAssignmentUpdate($employee, $department, $validated);
            DB::commit();

            return response()->json([
                'message' => 'Atama güncellendi',
                'data' => $employee->departments()->find($department->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    // Show Assignment History
    public function assignmentHistory(Employee $employee)
    {
        try {
            return response()->json([
                'assignments' => $employee->departments()
                    ->orderByPivot('start_date', 'desc')
                    ->get()
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    // Helpers
    private function validateEmployeeData(Request $request, $ignoreUserId = null)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'position' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'birth_date' => 'required|date|before:today',
            'gender' => ['required', Rule::in(['male','female','other'])],
            'national_id' => ['required','string','max:20',Rule::unique('employees','national_id')->ignore($ignoreUserId,'user_id')],
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:15',
            'emergency_contact' => 'required|string|max:50'
        ];

        return $request->validate($rules);
    }

    private function createEmployee(array $validated)
    {
        $user = User::findOrFail($validated['user_id']);

        return $user->employee()->create([
            'employee_number' => 'EMP'.strtoupper(uniqid()),
            'position' => $validated['position'],
            'salary' => $validated['salary'],
            'hire_date' => $validated['hire_date'],
            'birth_date' => $validated['birth_date'],
            'gender' => $validated['gender'],
            'national_id' => $validated['national_id'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'emergency_contact' => $validated['emergency_contact']
        ])->load('user');
    }

    private function updateEmployee(Employee $employee, array $validated)
    {
        $employee->update([
            'position' => $validated['position'],
            'salary' => $validated['salary'],
            'hire_date' => $validated['hire_date'],
            'birth_date' => $validated['birth_date'],
            'gender' => $validated['gender'],
            'national_id' => $validated['national_id'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'emergency_contact' => $validated['emergency_contact']
        ]);
    }

    private function processDepartmentAssignment(Employee $employee, array $validated)
    {
        if ($validated['is_primary'] ?? false) {
            DB::table('employee_department')
                ->where('employee_id', $employee->id)
                ->update(['is_primary' => false]);
        }

        $employee->departments()->attach($validated['department_id'], [
            'position' => $validated['position'],
            'start_date' => now(),
            'is_primary' => $validated['is_primary'] ?? false
        ]);
    }

    private function processAssignmentUpdate(Employee $employee, Department $department, array $validated)
    {
        if ($validated['is_primary'] ?? false) {
            DB::table('employee_department')
                ->where('employee_id', $employee->id)
                ->update(['is_primary' => false]);
        }

        $employee->departments()->updateExistingPivot($department->id, [
            'position' => $validated['position'],
            'end_date' => $validated['end_date'] ?? null,
            'is_primary' => $validated['is_primary'] ?? false
        ]);
    }

    private function errorResponse(\Exception $e)
    {
        return response()->json([
            'message' => 'İşlem sırasında hata oluştu',
            'error' => $e->getMessage()
        ], 500);
    }
}
