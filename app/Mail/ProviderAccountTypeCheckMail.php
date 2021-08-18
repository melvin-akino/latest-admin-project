<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProviderAccountTypeCheckMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($env, $subject, $accounts)
    {
        $this->env = $env;
        $this->subject = $subject;
        $this->accounts = $accounts;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.provider-account-type-check', ['env' => $this->env, 'accounts' => $this->accounts])
                    ->subject($this->subject);
    }
}
