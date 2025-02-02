<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotTempEmail implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $tempEmailDomains = [
            'tempmail.com',
            'mailinator.com',
            '10minutemail.com',
            'guerrillamail.com',
            'maildrop.cc',
            'getnada.com',
            'inboxbear.com',
            'trashmail.com',
            'disposablemail.com',
            'throwawaymail.com',
            'tempmail.net',
            'instant-email.org',
            'fakeinbox.com',
            'yopmail.com',
            'mailexpire.com',
            'trashmail.net',
            'mintemail.com',
            'mytrashmail.com',
            'throwawaymail.net',
            'spamgourmet.com',
            'spoofmail.de',
            'boximail.com',
            'dodgit.com',
            'mailinator2.com',
            'fakemail.net',
            'mailcatch.com',
            'sharklasers.com',
            'mailnesia.com',
            'fake-mail.net',
            'jetable.org',
            'tempmailo.com',
            'mail-temp.com',
        ];

        // Извлекаем домен email
        $emailDomain = substr(strrchr($value, "@"), 1);

        if (in_array($emailDomain, $tempEmailDomains)) {
            $fail(__('validation.temp_email'));
        }
    }
}
