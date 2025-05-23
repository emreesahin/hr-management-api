<?php

namespace App\Mail;

use App\Models\Leave;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public Leave $leave;

    public function __construct(Leave $leave)
    {
        $this->leave = $leave;
    }

    public function build()
    {
        return $this->subject('Yeni İzin Talebi')
                    ->view('emails.leave_request')
                    ->with([
                        'leave' => $this->leave,
                        'employee' => $this->leave->employee,
                        'department' => $this->leave->employee->department
                ]);
    }
}
