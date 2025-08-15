<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Event $event;
    public Employee $employee;

    public function __construct(Event $event, Employee $employee)
    {
        $this->event = $event;
        $this->employee = $employee;
    }

    public function build()
    {
        return $this->subject('คำเชิญเข้าร่วมกิจกรรม: '.$this->event->evn_title)
                    ->markdown('emails.event.invitation');
    }
}