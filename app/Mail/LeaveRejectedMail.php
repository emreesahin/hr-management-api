<?php

namespace App\Mail;

use App\Models\Leave;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Leave $leave;

    public function __construct(Leave $leave)
    {
        $this->leave = $leave;
    }

    public function build()
    {
        return $this->subject('İzin Reddedildi')
                    ->view('emails.leave_rejected')
                    ->with([
                        'leave' => $this->leave,
                        'employee' => $this->leave->employee,
                        'department' => $this->leave->employee->department
                ]);
    }
}
