<?php

namespace App\Traits;

/**
 * Трейт для работы с контекстом выполнения операций
 */
trait ContextAwareTrait
{
    /**
     * Контекст выполнения операций
     * 
     * @var array
     */
    protected array $context = [];
    
    /**
     * Добавить значение в контекст
     * 
     * @param string $key Ключ
     * @param mixed $value Значение
     * @return self
     */
    public function withContext(string $key, $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }
    
    /**
     * Добавить массив значений в контекст
     * 
     * @param array $values Массив значений
     * @return self
     */
    public function withContextValues(array $values): self
    {
        $this->context = array_merge($this->context, $values);
        return $this;
    }
    
    /**
     * Получить значение из контекста
     * 
     * @param string $key Ключ
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public function getContext(string $key, $default = null)
    {
        return $this->context[$key] ?? $default;
    }
    
    /**
     * Проверить наличие ключа в контексте
     * 
     * @param string $key Ключ
     * @return bool
     */
    public function hasContext(string $key): bool
    {
        return array_key_exists($key, $this->context);
    }
    
    /**
     * Получить весь контекст
     * 
     * @return array
     */
    public function getAllContext(): array
    {
        return $this->context;
    }
    
    /**
     * Очистить контекст
     * 
     * @return self
     */
    public function clearContext(): self
    {
        $this->context = [];
        return $this;
    }
    
    /**
     * Удалить ключ из контекста
     * 
     * @param string $key Ключ
     * @return self
     */
    public function removeContext(string $key): self
    {
        if (array_key_exists($key, $this->context)) {
            unset($this->context[$key]);
        }
        return $this;
    }
    
    /**
     * Выполнить операцию с временным контекстом
     * 
     * @param array $tempContext Временный контекст
     * @param callable $callback Функция для выполнения
     * @return mixed Результат выполнения функции
     */
    public function withTemporaryContext(array $tempContext, callable $callback)
    {
        $originalContext = $this->context;
        $this->context = array_merge($this->context, $tempContext);
        
        try {
            return $callback();
        } finally {
            $this->context = $originalContext;
        }
    }
} 