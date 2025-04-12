<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\LeaveRequestMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Leave;
use App\Models\Employee;
use App\Models\User;

class LeaveController extends Controller
{
    public function store(Request $request) {

        try{
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'reason' => 'required|string|max:255',
            ]);

            $employee = Employee::where('user_id', auth()->id())->firstOrFail();

            $days  = now()->parse($validated['start_date'])->diffInDaysFiltered(function($date){
                return $date->isWeekday();
            } , now()->parse($validated['end_date']));

            if($employee->annual_leave_days < $days) {
                return response()->json([
                    'status' => false,
                    'message' => 'You do not have enough annual leave days to apply for this leave.'
                ], 422);
            }

            $leave = Leave::create([
                'employee_id' => $validated['employee_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'reason' => $validated['reason'],
                'duration' => $days,
                'status' => 'pending',
            ]);

            $hrEmails = User::role('hr')->pluck('email')->toArray();

            foreach ($hrEmails as $email) {
                Mail::to($email)->send(new LeaveRequestMail($leave));
            }


            return response()->json([
                'status' => true,
                'message' => 'Leave request created successfully',
                'leave' => $leave
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }

    }
}
