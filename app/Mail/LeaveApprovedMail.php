<?php

namespace App\Mail;

use App\Models\Leave;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Leave $leave;

    public function __construct(Leave $leave)
    {
        $this->leave = $leave;
    }

    public function build()
    {
        return $this->subject('İzin Onaylandı')
                    ->view('emails.leave_approved')
                    ->with([
                        'leave' => $this->leave,
                        'employee' => $this->leave->employee,
                        'department' => $this->leave->employee->department
                ]);
    }
}
