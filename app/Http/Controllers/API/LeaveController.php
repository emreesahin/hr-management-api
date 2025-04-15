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

class LeaveController extends Controller
{
    // İzin Talebi Oluştur
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'reason' => 'required|string|max:255',
            ]);

            $employee = Employee::where('user_id', auth('employee')->id())->firstOrFail();

            $days = now()->parse($validated['start_date'])->diffInDaysFiltered(function ($date) {
                return $date->isWeekday();
            }, now()->parse($validated['end_date'])) + 1;

            if ($employee->annual_leave_days < $days) {
                return response()->json([
                    'status' => false,
                    'message' => 'Yeterli yıllık izniniz yok.',
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

            $hrEmails = User::role('hr', 'hr')->pluck('email')->toArray();

            Mail::to($hrEmails)->send(new LeaveRequestMail($leave));

            return response()->json([
                'status' => true,
                'message' => 'İzin talebi başarıyla oluşturuldu.',
                'leave' => $leave,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Hata: ' . $e->getMessage(),
            ], 422);
        }
    }

    // İzin Onayla
    public function approve($id)
    {
        try {
            if (!auth('hr')->user()->hasRole('hr', 'hr')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Yetkisiz işlem',
                ], 403);
            }

            $leave = Leave::findOrFail($id);

            if ($leave->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Bu istek zaten işlenmiş.',
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
                'message' => 'İzin talebi onaylandı.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Hata: ' . $e->getMessage(),
            ], 422);
        }
    }

    // İzin Reddet
    public function reject($id)
    {
        try {
            if (!auth('hr')->user()->hasRole('hr', 'hr')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Yetkisiz işlem',
                ], 403);
            }

            $leave = Leave::findOrFail($id);

            if ($leave->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Bu istek zaten işlenmiş.',
                ]);
            }

            $leave->update(['status' => 'rejected']);

            $employee = $leave->employee;
            $employeeEmail = $employee->user->email;
            Mail::to($employeeEmail)->send(new LeaveRejectedMail($leave));

            return response()->json([
                'status' => true,
                'message' => 'İzin talebi reddedildi.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Hata: ' . $e->getMessage(),
            ], 422);
        }
    }
}
