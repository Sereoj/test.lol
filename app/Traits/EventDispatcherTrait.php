<?php

namespace App\Traits;

use Illuminate\Support\Facades\Event;

/**
 * Трейт для стандартизации работы с событиями
 */
trait EventDispatcherTrait
{
    /**
     * Флаг, определяющий, должны ли диспатчиться события
     * 
     * @var bool
     */
    protected bool $dispatchEvents = true;
    
    /**
     * Временно отключить диспатч событий 
     * 
     * @return self
     */
    public function withoutEvents(): self
    {
        $this->dispatchEvents = false;
        return $this;
    }
    
    /**
     * Включить диспатч событий
     * 
     * @return self
     */
    public function withEvents(): self
    {
        $this->dispatchEvents = true;
        return $this;
    }
    
    /**
     * Выполнить операцию без диспатча событий
     * 
     * @param callable $callback
     * @return mixed
     */
    public function withoutEventsRun(callable $callback)
    {
        $previous = $this->dispatchEvents;
        $this->dispatchEvents = false;
        
        try {
            $result = $callback();
            return $result;
        } finally {
            $this->dispatchEvents = $previous;
        }
    }
    
    /**
     * Диспатчить событие только если включен флаг dispatchEvents
     * 
     * @param string|object $event
     * @param mixed $payload
     * @return void
     */
    protected function dispatchIf($event, $payload = [])
    {
        if ($this->dispatchEvents) {
            Event::dispatch($event, $payload);
            
            // Логируем событие, если есть метод logInfo
            if (method_exists($this, 'logInfo')) {
                $eventName = is_string($event) ? $event : get_class($event);
                $this->logInfo("Event dispatched: {$eventName}", [
                    'event' => $eventName,
                    'payload_type' => is_object($payload) ? get_class($payload) : gettype($payload)
                ]);
            }
        }
    }
} 