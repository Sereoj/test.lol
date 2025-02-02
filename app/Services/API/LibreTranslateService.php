<?php

namespace App\Services\API;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LibreTranslateService
{
    protected static $apiUrl = 'https://libretranslate.com/translate';

    /**
     * Перевод текста с одного языка на другой.
     *
     * @param  string  $text  Текст для перевода
     * @param  string  $sourceLang  Исходный язык
     * @param  string  $targetLang  Язык для перевода
     * @return string|false Переведённый текст или false в случае ошибки
     */
    public static function translate(string $text, string $sourceLang = 'ru', string $targetLang = 'en')
    {
        try {
            $response = Http::asForm()->post(self::$apiUrl, [
                'q' => $text,
                'source' => $sourceLang,
                'target' => $targetLang,
                'format' => 'text',
                'alternatives' => 3,
                'api_key' => '',
            ]);

            if ($response->successful()) {
                return $response->json()['translatedText'];
            }

            if ($response->failed()) {
                Log::error('LibreTranslate API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            \Log::error('Translation API call failed: '.$response->body());
        } catch (\Exception $e) {
            \Log::error('Translation API exception: '.$e->getMessage());
        }

        return false;
    }
}
