<?php

namespace App\Utils;

use Illuminate\Support\Facades\Hash;

class PasswordUtil
{
    public static function hash($value)
    {
        return Hash::make($value);
    }
}
