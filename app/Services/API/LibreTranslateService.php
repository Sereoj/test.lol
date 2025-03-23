<?php

namespace App\Services\API;

use App\Services\Base\SimpleService;
use Exception;
use Illuminate\Support\Facades\Http;

class LibreTranslateService extends SimpleService
{
    /**
     * URL API для перевода
     *
     * @var string
     */
    protected static string $apiUrl = 'https://libretranslate.com/translate';
    
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'translate';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 60;
    
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('LibreTranslateService');
    }

    /**
     * Перевод текста с одного языка на другой
     *
     * @param string $text Текст для перевода
     * @param string $sourceLang Исходный язык
     * @param string $targetLang Язык для перевода
     * @return string|null Переведённый текст или null в случае ошибки
     */
    public function translate(string $text, string $sourceLang = 'ru', string $targetLang = 'en'): ?string
    {
        $this->logInfo("Перевод текста", [
            'source_lang' => $sourceLang,
            'target_lang' => $targetLang,
            'text_length' => strlen($text)
        ]);
        
        $cacheKey = $this->buildCacheKey('translation', [
            $sourceLang, 
            $targetLang, 
            md5($text)
        ]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($text, $sourceLang, $targetLang) {
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
                    $translatedText = $response->json()['translatedText'];
                    
                    $this->logInfo("Перевод выполнен успешно", [
                        'source_lang' => $sourceLang,
                        'target_lang' => $targetLang,
                        'original_length' => strlen($text),
                        'translated_length' => strlen($translatedText)
                    ]);
                    
                    return $translatedText;
                }
                
                if ($response->failed()) {
                    $this->logError("Ошибка API перевода", [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    
                    return null;
                }
                
                $this->logError("Неизвестная ошибка при переводе", [
                    'response' => $response->body()
                ]);
                
                return null;
            } catch (Exception $e) {
                $this->logError("Исключение при вызове API перевода", [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                
                return null;
            }
        });
    }
    
    /**
     * Удобный статический метод для перевода текста
     *
     * @param string $text Текст для перевода
     * @param string $sourceLang Исходный язык
     * @param string $targetLang Язык для перевода
     * @return string|null Переведённый текст или null в случае ошибки
     */
    public static function translateText(string $text, string $sourceLang = 'ru', string $targetLang = 'en'): ?string
    {
        return (new self())->translate($text, $sourceLang, $targetLang);
    }
    
    /**
     * Обнаружение языка текста
     *
     * @param string $text Текст для определения языка
     * @return string|null Код языка или null в случае ошибки
     */
    public function detectLanguage(string $text): ?string
    {
        $this->logInfo("Определение языка текста", [
            'text_length' => strlen($text)
        ]);
        
        $cacheKey = $this->buildCacheKey('language_detection', [md5($text)]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($text) {
            try {
                $response = Http::asForm()->post('https://libretranslate.com/detect', [
                    'q' => $text,
                    'api_key' => '',
                ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data[0]) && isset($data[0]['language'])) {
                        $detectedLanguage = $data[0]['language'];
                        
                        $this->logInfo("Язык успешно определен", [
                            'detected_language' => $detectedLanguage,
                            'confidence' => $data[0]['confidence'] ?? 'unknown'
                        ]);
                        
                        return $detectedLanguage;
                    }
                }
                
                $this->logError("Ошибка при определении языка", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                
                return null;
            } catch (Exception $e) {
                $this->logError("Исключение при определении языка", [
                    'message' => $e->getMessage()
                ]);
                
                return null;
            }
        });
    }
}
