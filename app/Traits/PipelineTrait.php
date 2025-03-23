<?php

namespace App\Traits;

use Closure;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\App;

/**
 * Трейт для работы с пайплайнами для обработки данных
 */
trait PipelineTrait
{
    /**
     * Пайплайн для обработки данных перед сохранением
     * 
     * @var array
     */
    protected array $beforeSavePipeline = [];
    
    /**
     * Пайплайн для обработки данных после получения
     * 
     * @var array
     */
    protected array $afterRetrievePipeline = [];
    
    /**
     * Обработать данные через пайплайн
     * 
     * @param array $data Данные для обработки
     * @param array $pipes Массив пайпов для обработки данных
     * @param array $context Контекст выполнения
     * @return array Обработанные данные
     */
    protected function processThroughPipeline(array $data, array $pipes = [], array $context = []): array
    {
        if (empty($pipes)) {
            $pipes = $this->beforeSavePipeline;
        }
        
        if (empty($pipes)) {
            return $data;
        }
        
        $processedPipes = [];
        
        // Подготавливаем пайпы
        foreach ($pipes as $pipe) {
            if (is_string($pipe) && method_exists($this, $pipe)) {
                // Метод в текущем классе
                $processedPipes[] = function ($data, $next) use ($pipe, $context) {
                    $result = $this->$pipe($data, $context);
                    return $next($result ?? $data);
                };
            } elseif (is_callable($pipe)) {
                // Обычный колбэк
                $processedPipes[] = function ($data, $next) use ($pipe, $context) {
                    $result = $pipe($data, $context);
                    return $next($result ?? $data);
                };
            } elseif (is_string($pipe) && class_exists($pipe)) {
                // Класс пайпа
                $processedPipes[] = $pipe;
            }
        }
        
        // Логируем начало обработки пайплайна, если есть метод logInfo
        if (method_exists($this, 'logInfo')) {
            $this->logInfo("Starting pipeline processing", [
                'pipes_count' => count($processedPipes),
                'context' => $context
            ]);
        }
        
        // Обрабатываем данные через пайплайн
        $result = App::make(Pipeline::class)
            ->send($data)
            ->through($processedPipes)
            ->then(function ($data) {
                return $data;
            });
        
        // Логируем результат обработки пайплайна, если есть метод logInfo
        if (method_exists($this, 'logInfo')) {
            $this->logInfo("Pipeline processing completed", [
                'pipes_count' => count($processedPipes),
                'context' => $context
            ]);
        }
        
        return $result;
    }
    
    /**
     * Обработать данные через пайплайн перед сохранением
     * 
     * @param array $data Данные для обработки
     * @param array $context Контекст выполнения
     * @return array Обработанные данные
     */
    protected function processBeforeSave(array $data, array $context = []): array
    {
        return $this->processThroughPipeline($data, $this->beforeSavePipeline, $context);
    }
    
    /**
     * Обработать данные через пайплайн после получения
     * 
     * @param array $data Данные для обработки
     * @param array $context Контекст выполнения
     * @return array Обработанные данные
     */
    protected function processAfterRetrieve(array $data, array $context = []): array
    {
        return $this->processThroughPipeline($data, $this->afterRetrievePipeline, $context);
    }
    
    /**
     * Добавить пайп в пайплайн перед сохранением
     * 
     * @param string|callable $pipe Пайп для добавления
     * @return self
     */
    public function addSavePipe($pipe): self
    {
        $this->beforeSavePipeline[] = $pipe;
        return $this;
    }
    
    /**
     * Добавить пайп в пайплайн после получения
     * 
     * @param string|callable $pipe Пайп для добавления
     * @return self
     */
    public function addRetrievePipe($pipe): self
    {
        $this->afterRetrievePipeline[] = $pipe;
        return $this;
    }
} 