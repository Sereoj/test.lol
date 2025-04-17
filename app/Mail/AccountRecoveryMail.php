<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountRecoveryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;

    /**
     * Create a new message instance.
     *
     * @param string $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $recoveryUrl = config('app.url') . '/account/recovery?token=' . $this->token;
        
        return $this->subject('Восстановление удаленного аккаунта')
            ->markdown('emails.account.recovery', [
                'recoveryUrl' => $recoveryUrl,
                'token' => $this->token
            ]);
    }
} 