<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Трейт для стандартизации логирования в сервисах
 */
trait LoggableTrait
{
    /**
     * Префикс для сообщений лога
     *
     * @var string|null
     */
    protected ?string $logPrefix = null;

    /**
     * Флаг включения/отключения логирования
     *
     * @var bool
     */
    protected bool $loggingEnabled = true;

    /**
     * Уровень детализации логирования
     * Возможные значения: debug, info, notice, warning, error, critical, alert, emergency
     * По умолчанию логируются все уровни
     *
     * @var string|null
     */
    protected ?string $logLevel = null;

    /**
     * Контекст для всех сообщений лога
     *
     * @var array
     */
    protected array $logContext = [];

    /**
     * Логирование информационного сообщения
     *
     * @param string $message Сообщение для логирования
     * @param array $context Контекст логирования
     * @return void
     */
    protected function logInfo(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * Логирование предупреждения
     *
     * @param string $message Сообщение для логирования
     * @param array $context Контекст логирования
     * @return void
     */
    protected function logWarning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Логирование ошибки
     *
     * @param string $message Сообщение для логирования
     * @param array $context Контекст логирования
     * @param Throwable|null $exception Исключение для дополнительного контекста
     * @return void
     */
    protected function logError(string $message, array $context = [], ?Throwable $exception = null): void
    {
        if ($exception) {
            $context['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        $this->log('error', $message, $context);
    }

    /**
     * Логирование отладочной информации
     *
     * @param string $message Сообщение для логирования
     * @param array $context Контекст логирования
     * @return void
     */
    protected function logDebug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Логирование критической ошибки
     *
     * @param string $message Сообщение для логирования
     * @param array $context Контекст логирования
     * @param Throwable|null $exception Исключение для дополнительного контекста
     * @return void
     */
    protected function logCritical(string $message, array $context = [], ?Throwable $exception = null): void
    {
        if ($exception) {
            $context['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        $this->log('critical', $message, $context);
    }

    /**
     * Логирование уведомления
     *
     * @param string $message Сообщение для логирования
     * @param array $context Контекст логирования
     * @return void
     */
    protected function logNotice(string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * Основной метод логирования с проверкой уровня и префикса
     *
     * @param string $level Уровень логирования
     * @param string $message Сообщение для логирования
     * @param array $context Контекст логирования
     * @return void
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        if (!$this->shouldLog($level)) {
            return;
        }

        // Добавляем префикс к сообщению, если он задан
        if ($this->logPrefix) {
            $message = "[{$this->logPrefix}] {$message}";
        }

        // Добавляем имя класса для отслеживания источника логов
        $context['class'] = get_class($this);

        // Объединяем с глобальным контекстом
        $context = array_merge($this->logContext, $context);

        // Логируем сообщение
        Log::$level($message, $context);
    }

    /**
     * Проверяет, должно ли сообщение быть залогировано
     *
     * @param string $level Уровень логирования
     * @return bool
     */
    protected function shouldLog(string $level): bool
    {
        if (!$this->loggingEnabled) {
            return false;
        }

        if ($this->logLevel === null) {
            return true;
        }

        $levels = [
            'debug' => 0,
            'info' => 1,
            'notice' => 2,
            'warning' => 3,
            'error' => 4,
            'critical' => 5,
            'alert' => 6,
            'emergency' => 7,
        ];

        return $levels[$level] >= $levels[$this->logLevel];
    }

    /**
     * Включить логирование
     *
     * @return self
     */
    public function enableLogging(): self
    {
        $this->loggingEnabled = true;
        return $this;
    }

    /**
     * Отключить логирование
     *
     * @return self
     */
    public function disableLogging(): self
    {
        $this->loggingEnabled = false;
        return $this;
    }

    /**
     * Проверить, включено ли логирование
     *
     * @return bool
     */
    public function isLoggingEnabled(): bool
    {
        return $this->loggingEnabled;
    }

    /**
     * Установить префикс для сообщений лога
     *
     * @param string $prefix Префикс
     * @return self
     */
    public function setLogPrefix(string $prefix): self
    {
        $this->logPrefix = $prefix;
        return $this;
    }

    /**
     * Получить текущий префикс для сообщений лога
     *
     * @return string|null
     */
    public function getLogPrefix(): ?string
    {
        return $this->logPrefix;
    }

    /**
     * Установить минимальный уровень логирования
     *
     * @param string $level Уровень логирования
     * @return self
     */
    public function setLogLevel(string $level): self
    {
        $this->logLevel = $level;
        return $this;
    }

    /**
     * Добавить контекст для всех сообщений лога
     *
     * @param string $key Ключ контекста
     * @param mixed $value Значение контекста
     * @return self
     */
    public function addLogContext(string $key, $value): self
    {
        $this->logContext[$key] = $value;
        return $this;
    }

    /**
     * Установить контекст для всех сообщений лога
     *
     * @param array $context Контекст
     * @return self
     */
    public function setLogContext(array $context): self
    {
        $this->logContext = $context;
        return $this;
    }

    /**
     * Выполнить операцию с отключенным логированием
     *
     * @param callable $callback Операция
     * @return mixed
     */
    public function withoutLogging(callable $callback)
    {
        $originalState = $this->loggingEnabled;
        $this->loggingEnabled = false;

        try {
            $result = $callback();
            return $result;
        } finally {
            $this->loggingEnabled = $originalState;
        }
    }

    /**
     * Выполнить операцию с временным уровнем логирования
     *
     * @param string $level Уровень логирования
     * @param callable $callback Операция
     * @return mixed
     */
    public function withLogLevel(string $level, callable $callback)
    {
        $originalLevel = $this->logLevel;
        $this->logLevel = $level;

        try {
            $result = $callback();
            return $result;
        } finally {
            $this->logLevel = $originalLevel;
        }
    }

    /**
     * Подготовить сообщение для лога с данными о модели
     *
     * @param string $action Действие (create, update, delete, и т.д.)
     * @param mixed $model Модель
     * @param array $additional Дополнительные данные
     * @return array Массив с данными для логирования [сообщение, контекст]
     */
    protected function prepareModelLog(string $action, $model, array $additional = []): array
    {
        $modelClass = get_class($model);
        $modelName = class_basename($modelClass);
        $modelId = method_exists($model, 'getKey') ? $model->getKey() : null;

        $message = ucfirst($action) . " {$modelName}" . ($modelId ? " #{$modelId}" : '');

        $context = [
            'model' => $modelName,
            'model_class' => $modelClass,
        ];

        if ($modelId) {
            $context['model_id'] = $modelId;
        }

        if (!empty($additional)) {
            $context = array_merge($context, $additional);
        }

        return [$message, $context];
    }
}
