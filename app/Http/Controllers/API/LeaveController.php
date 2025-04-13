<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\LeaveRequestMail;
use App\Mail\LeaveApprovedMail;
use App\Mail\LeaveRejectedMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Leave;
use App\Models\Employee;
use App\Models\User;
use App\Models\Department;

class LeaveController extends Controller
{
    public function store(Request $request) {

        try{
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'reason' => 'required|string|max:255',
            ]);

            $employee = Employee::where('user_id', auth()->id())->firstOrFail();

            $days  = now()->parse($validated['start_date'])->diffInDaysFiltered(function($date){
                return $date->isWeekday();
            } , now()->parse($validated['end_date'])) +1;

            if($employee->annual_leave_days < $days) {
                return response()->json([
                    'status' => false,
                    'message' => 'You do not have enough annual leave days to apply for this leave.'
                ], 422);
            }

            $leave = Leave::create([
                'employee_id' => $employee->id,
                'department' => $employee->department,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'reason' => $validated['reason'],
                'duration' => $days,
                'status' => 'pending',
            ]);

            $hrEmails = User::role('hr')->pluck('email')->toArray();

            Mail::to($hrEmails)->send(new LeaveRequestMail($leave));



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

    public function approve($id) {

        try{

            if (!auth()->user()->hasRole(['hr', 'admin'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Yetkisiz işlem'
                ], 403);
            }

            $leave = Leave::findOrFail($id);


            if($leave -> status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Request has already proceed'
                ], 400);
            }

            $leave->update(['status' => 'approved']);

            $employee = $leave->employee;
            $employee->annual_leave_days -= $leave->duration;
            $employee->save();

            $employeeEmail = $employee->user->email;

            Mail::to($employeeEmail)->send(new LeaveApprovedMail($leave));

            return response()->json([
                'status' => true,
                'message' => 'Request approved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ],422);
        }


    }

    public function reject($id) {
        try{

        if (!auth()->user()->hasRole(['hr', 'admin'])) {
            return response()->json([
                'status' => false,
                'message' => 'Yetkisiz işlem'
            ], 403);
            }

        $leave = Leave::findOrFail($id);

        if($leave->status !== 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Request has already proceed'
            ]);
        }

        $leave->update(['status' => 'rejected']);

        $employee = $leave->employee;
        $employeeEmail = $employee->user->email;

        Mail::to($employeeEmail)->send(new LeaveRejectedMail($leave));


        return response()->json([
            'status' => true,
            'message' => 'Request rejected successfully'
        ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ],422);
        }

    }

}
