<?php

namespace App\Rules;

use Cache;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidMxRecord implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $emailDomain = substr(strrchr($value, '@'), 1);

        if (!$this->hasMxRecord($emailDomain)) {
            $fail(__('validation.invalid_mx_record'));
        }
    }

    /**
     * Проверяет наличие MX-записи для домена.
     *
     * @param string $domain
     * @return bool
     */
    protected function hasMxRecord(string $domain): bool
    {
        return Cache::remember("mx_record_{$domain}", now()->addMinutes(10), function () use ($domain) {
            return checkdnsrr($domain);
        });
    }
}
