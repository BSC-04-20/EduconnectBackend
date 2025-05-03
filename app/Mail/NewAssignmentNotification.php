<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Assignment;

class NewAssignmentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $assignment;

    /**
     * Create a new message instance.
     */
    public function __construct(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('New Assignment Posted')
                    ->view('emails.new_assignment')
                    ->with([
                        'assignment' => $this->assignment
                    ]);
    }
}
