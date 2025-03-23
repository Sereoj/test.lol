<?php

namespace App\Services\Base;

use Exception;
use Illuminate\Support\Facades\File;

class AppSettingsService extends SimpleService
{
    /**
     * Путь к файлу настроек
     *
     * @var string
     */
    protected string $settingsPath;

    /**
     * Настройки приложения
     *
     * @var array
     */
    protected array $settings = [];

    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'app_settings';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 60;

    /**
     * Конструктор
     *
     * @param string|null $settingsPath Путь к файлу настроек
     */
    public function __construct(?string $settingsPath = null)
    {
        parent::__construct();
        $this->setLogPrefix('AppSettingsService');
        
        $this->settingsPath = $settingsPath ?? storage_path('app/settings.json');
        
        $this->loadSettings();
    }

    /**
     * Загрузить настройки из файла
     *
     * @return array
     * @throws Exception
     */
    public function loadSettings(): array
    {
        $cacheKey = $this->buildCacheKey('all_settings');
        
        $this->settings = $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () {
            $this->logInfo("Загрузка настроек из файла", ['path' => $this->settingsPath]);
            
            try {
                if (!File::exists($this->settingsPath)) {
                    $this->logWarning("Файл настроек не найден, создаем пустой файл", ['path' => $this->settingsPath]);
                    
                    // Создаем директорию, если она не существует
                    $directory = dirname($this->settingsPath);
                    if (!File::exists($directory)) {
                        File::makeDirectory($directory, 0755, true);
                    }
                    
                    // Создаем пустой файл настроек
                    File::put($this->settingsPath, json_encode([]));
                    
                    return [];
                }
                
                $content = File::get($this->settingsPath);
                $settings = json_decode($content, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Ошибка при декодировании JSON: " . json_last_error_msg());
                }
                
                $this->logInfo("Настройки успешно загружены", ['count' => count($settings)]);
                
                return $settings ?: [];
            } catch (Exception $e) {
                $this->logError("Ошибка при загрузке настроек", [], $e);
                throw new Exception("Не удалось загрузить настройки: " . $e->getMessage());
            }
        });
        
        return $this->settings;
    }

    /**
     * Получить значение настройки
     *
     * @param string $key Ключ настройки
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $settings = $this->settings;
        
        foreach ($keys as $segment) {
            if (!is_array($settings) || !array_key_exists($segment, $settings)) {
                $this->logInfo("Настройка не найдена, возвращаем значение по умолчанию", [
                    'key' => $key,
                    'default' => is_scalar($default) ? $default : gettype($default)
                ]);
                return $default;
            }
            
            $settings = $settings[$segment];
        }
        
        return $settings;
    }

    /**
     * Установить значение настройки
     *
     * @param string $key Ключ настройки
     * @param mixed $value Значение настройки
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->logInfo("Установка настройки", [
            'key' => $key,
            'value' => is_scalar($value) ? $value : gettype($value)
        ]);
        
        $keys = explode('.', $key);
        $lastKey = array_pop($keys);
        
        $settings = &$this->settings;
        
        foreach ($keys as $segment) {
            if (!isset($settings[$segment]) || !is_array($settings[$segment])) {
                $settings[$segment] = [];
            }
            
            $settings = &$settings[$segment];
        }
        
        $settings[$lastKey] = $value;
        
        // Сбрасываем кеш
        $this->forgetCache($this->buildCacheKey('all_settings'));
    }

    /**
     * Сохранить настройки в файл
     *
     * @return bool
     * @throws Exception
     */
    public function save(): bool
    {
        $this->logInfo("Сохранение настроек в файл", ['path' => $this->settingsPath]);
        
        try {
            // Создаем директорию, если она не существует
            $directory = dirname($this->settingsPath);
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            
            $json = json_encode($this->settings, JSON_PRETTY_PRINT);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Ошибка при кодировании JSON: " . json_last_error_msg());
            }
            
            File::put($this->settingsPath, $json);
            
            // Сбрасываем кеш
            $this->forgetCache($this->buildCacheKey('all_settings'));
            
            $this->logInfo("Настройки успешно сохранены");
            
            return true;
        } catch (Exception $e) {
            $this->logError("Ошибка при сохранении настроек", [], $e);
            throw new Exception("Не удалось сохранить настройки: " . $e->getMessage());
        }
    }
    
    /**
     * Удалить настройку
     *
     * @param string $key Ключ настройки
     * @return bool
     */
    public function delete(string $key): bool
    {
        $this->logInfo("Удаление настройки", ['key' => $key]);
        
        $keys = explode('.', $key);
        $lastKey = array_pop($keys);
        
        $settings = &$this->settings;
        
        foreach ($keys as $segment) {
            if (!isset($settings[$segment]) || !is_array($settings[$segment])) {
                $this->logWarning("Настройка не найдена", ['key' => $key]);
                return false;
            }
            
            $settings = &$settings[$segment];
        }
        
        if (!isset($settings[$lastKey])) {
            $this->logWarning("Настройка не найдена", ['key' => $key]);
            return false;
        }
        
        unset($settings[$lastKey]);
        
        // Сбрасываем кеш
        $this->forgetCache($this->buildCacheKey('all_settings'));
        
        $this->logInfo("Настройка успешно удалена", ['key' => $key]);
        
        return true;
    }
    
    /**
     * Проверить существование настройки
     *
     * @param string $key Ключ настройки
     * @return bool
     */
    public function has(string $key): bool
    {
        $keys = explode('.', $key);
        $settings = $this->settings;
        
        foreach ($keys as $segment) {
            if (!is_array($settings) || !array_key_exists($segment, $settings)) {
                return false;
            }
            
            $settings = $settings[$segment];
        }
        
        return true;
    }
    
    /**
     * Получить все настройки
     *
     * @return array
     */
    public function all(): array
    {
        return $this->settings;
    }
    
    /**
     * Сбросить все настройки
     *
     * @return bool
     * @throws Exception
     */
    public function reset(): bool
    {
        $this->logInfo("Сброс всех настроек");
        
        $this->settings = [];
        
        // Сбрасываем кеш
        $this->forgetCache($this->buildCacheKey('all_settings'));
        
        return $this->save();
    }
}
