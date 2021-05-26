<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendCSV extends Mailable
{
    use Queueable, SerializesModels;

    protected $attachment;
    public    $subject;
    public    $coverage;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($attachment, $subject, $coverage)
    {
        $this->attachment = $attachment;
        $this->subject    = $subject;
        $this->coverage   = $coverage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.bets-csv', ['coverage' => $this->coverage])
                    ->subject($this->subject)
                    ->attach($this->attachment);
    }
}
