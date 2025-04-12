<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\PDF;


class PayrollController extends Controller
{
    public function index () {
        try {

        $user = Auth::user();

        $employee = Employee::where('user_id', $user->id)->firstOrFail();

        $payrolls = Payroll::where('employee_id', $employee->id)->get();

        return response()->json([
            'payrolls' => $payrolls
        ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Payroll not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
            ]);


            $employee = Employee::findOrFail($validated['employee_id']);

            $netSalary = $employee->salary;

            //  Otomatik hesaplamalar (örnek oranlarla)
            $deductions = $netSalary * 0.14; // SGK/stopaj kesintisi
            $bonuses = $netSalary * 0.10;    // prim vb.
            $grossSalary = $netSalary + $deductions - $bonuses;

            $issuedAt = now();
            $period = $issuedAt->format('Y-m');


            $payroll = Payroll::create([
                'employee_id'   => $employee->id,
                'period'        => $period,
                'gross_salary'  => round($grossSalary, 2),
                'deductions'    => round($deductions, 2),
                'bonuses'       => round($bonuses, 2),
                'net_salary'    => round($netSalary, 2),
                'issued_at'     => $issuedAt,
            ]);

            return response()->json([
                'message' => 'Payroll created successfully',
                'data' => $payroll
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Payroll could not be created',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function download($id)
    {
        try {
            $payroll = Payroll::with('employee.user')->findOrFail($id);
            $user = auth()->user();

            if ($user->cannot('view', $payroll)) {
                return response()->json([
                    'message' => 'You do not have permission to view this payroll'
                ], 403);
            }

            $pdf = PDF::loadView('pdf.payroll', compact('payroll'));

            return $pdf->download("payroll_{$payroll->period}.pdf");

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Payroll not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }


}
