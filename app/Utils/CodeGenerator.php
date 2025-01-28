<?php

namespace App\Utils;

use Illuminate\Support\Str;

class CodeGenerator
{
    /**
     * Generate a random numeric code.
     */
    public static function generateNumericCode(int $length = 6): string
    {
        return str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a random alphanumeric code.
     */
    public static function generateAlphanumericCode(int $length = 6): string
    {
        return Str::random($length);
    }

    /**
     * Generate a random alphabetic code.
     */
    public static function generate(int $length = 6): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
