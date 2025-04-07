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

    public function store(Request $request) {

        try {

            $validated = $request ->validate([
                'employee_id' => 'required|exists:employee,id',
                'period' => 'required|string',
                'gross_salary' => 'required|numeric',
                'deductions' => 'nullable|numeric',
                'bonuses' => 'nullable|numeric',
                'net_salary' => 'required|numeric',
                'issued_at' => 'required|date',

            ]);

            $payroll = Payroll::create($validated);

            return response() -> json([
                'message' => 'Payroll created successfully',
                'data' => $payroll
            ]);

        }

        catch (\Exception $e) {
            return response()->json([
                'message' => 'Payroll not found',
                'error' => $e->getMessage()
            ], 404);
        }


    }

    public function download($id) {

        try{

            $payroll = Payroll::with('employee.user')->findOrFail($id);

            $user = auth()->user();

            if($user->cannot('view', $payroll)) {
                return response()->json([
                    'message' => 'You do not have permission to view this payroll'
                ], 403);
            }

            $pdf = Pdf::loadView('pdf.payroll', compact('payroll'));

            return $pdf->download('payroll_{$payroll->period}.pdf');

        }   catch (\Exception $e) {
            return response()->json([
                'message' => 'Payroll not found',
                'error' => $e->getMessage()
            ], 404);
        }


    }

}
