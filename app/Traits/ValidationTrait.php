<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Трейт для валидации данных
 */
trait ValidationTrait
{
    /**
     * Правила валидации для создания
     *
     * @var array
     */
    protected $validationRules = [];

    /**
     * Правила валидации для обновления
     *
     * @var array
     */
    protected $updateValidationRules = [];

    /**
     * Сообщения об ошибках валидации
     *
     * @var array
     */
    protected $validationMessages = [];

    /**
     * Атрибуты полей для валидации
     *
     * @var array
     */
    protected $validationAttributes = [];

    /**
     * Флаг необходимости валидации
     *
     * @var bool
     */
    protected $shouldValidate = true;

    /**
     * Валидация данных
     *
     * @param array $data Данные для валидации
     * @param array|null $rules Правила валидации (если null, используются правила из свойства $validationRules)
     * @param bool $throwOnFailure Выбрасывать исключение при ошибке валидации
     * @return array Валидированные данные
     * @throws ValidationException Если throwOnFailure = true и валидация не пройдена
     */
    protected function validate(array $data, ?array $rules = null, bool $throwOnFailure = true): array
    {
        if (!$this->shouldValidate) {
            return $data;
        }

        $rules = $rules ?? $this->validationRules;

        if (empty($rules)) {
            return $data;
        }

        $validator = Validator::make(
            $data,
            $rules,
            $this->validationMessages,
            $this->validationAttributes
        );

        if ($throwOnFailure) {
            $validated = $validator->validate();
        } else {
            if ($validator->fails()) {
                if (method_exists($this, 'logWarning')) {
                    $this->logWarning('Validation failed', [
                        'errors' => $validator->errors()->toArray(),
                        'data' => $this->maskSensitiveData($data)
                    ]);
                }
                return [];
            }
            $validated = $validator->validated();
        }

        return $validated;
    }

    /**
     * Установить правила валидации
     *
     * @param array $rules Правила валидации
     * @return self
     */
    public function setValidationRules(array $rules): self
    {
        $this->validationRules = $rules;
        return $this;
    }

    /**
     * Получить текущие правила валидации
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    /**
     * Установить правила валидации для обновления
     *
     * @param array $rules Правила валидации
     * @return self
     */
    public function setUpdateValidationRules(array $rules): self
    {
        $this->updateValidationRules = $rules;
        return $this;
    }

    /**
     * Получить текущие правила валидации для обновления
     *
     * @return array
     */
    public function getUpdateValidationRules(): array
    {
        return $this->updateValidationRules;
    }

    /**
     * Установить сообщения об ошибках валидации
     *
     * @param array $messages Сообщения об ошибках
     * @return self
     */
    public function setValidationMessages(array $messages): self
    {
        $this->validationMessages = $messages;
        return $this;
    }

    /**
     * Установить названия атрибутов для валидации
     *
     * @param array $attributes Названия атрибутов
     * @return self
     */
    public function setValidationAttributes(array $attributes): self
    {
        $this->validationAttributes = $attributes;
        return $this;
    }

    /**
     * Включить валидацию
     *
     * @return self
     */
    public function enableValidation(): self
    {
        $this->shouldValidate = true;
        return $this;
    }

    /**
     * Отключить валидацию
     *
     * @return self
     */
    public function disableValidation(): self
    {
        $this->shouldValidate = false;
        return $this;
    }

    /**
     * Выполнить операцию с отключенной валидацией
     *
     * @param callable $callback Операция
     * @return mixed Результат операции
     */
    public function withoutValidation(callable $callback)
    {
        $originalValidate = $this->shouldValidate;
        $this->shouldValidate = false;

        try {
            $result = $callback();
            return $result;
        } finally {
            $this->shouldValidate = $originalValidate;
        }
    }

    /**
     * Выполнить операцию с временными правилами валидации
     *
     * @param array $rules Временные правила валидации
     * @param callable $callback Операция
     * @return mixed Результат операции
     */
    public function withValidationRules(array $rules, callable $callback)
    {
        $originalRules = $this->validationRules;
        $this->validationRules = $rules;

        try {
            $result = $callback();
            return $result;
        } finally {
            $this->validationRules = $originalRules;
        }
    }

    /**
     * Выполнить операцию с временными правилами валидации обновления
     *
     * @param array $rules Временные правила валидации
     * @param callable $callback Операция
     * @return mixed Результат операции
     */
    public function withUpdateValidationRules(array $rules, callable $callback)
    {
        $originalRules = $this->updateValidationRules;
        $this->updateValidationRules = $rules;

        try {
            $result = $callback();
            return $result;
        } finally {
            $this->updateValidationRules = $originalRules;
        }
    }

    /**
     * Маскирует чувствительные данные перед логированием
     * Переопределите этот метод для маскирования конкретных полей
     *
     * @param array $data Исходные данные
     * @return array Маскированные данные
     */
    protected function maskSensitiveData(array $data): array
    {
        $sensitiveFields = ['password', 'password_confirmation', 'secret', 'token', 'api_key', 'credit_card'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '********';
            }
        }
        
        return $data;
    }
} 