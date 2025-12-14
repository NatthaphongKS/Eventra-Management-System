<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventCancellationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $employee;
    public $event;

    public function __construct($employee, $event)
    {
        $this->employee = $employee;
        $this->event = $event;
    }

    public function build()
    {
        return $this->subject('แจ้งยกเลิกการเข้าร่วมกิจกรรม: ' . $this->event->evn_title)
                    ->view('emails.event.cancellation');
    }
}