<?php

namespace App\Interfaces;

interface ServiceInterface
{
    /**
     * Получить название сервиса
     * 
     * @return string
     */
    public function getServiceName(): string;
    
    /**
     * Установить префикс для логирования
     * 
     * @param string $prefix
     * @return self
     */
    public function setLogPrefix(string $prefix): self;
    
    /**
     * Установить префикс для кеширования
     * 
     * @param string $prefix
     * @return self
     */
    public function setCachePrefix(string $prefix): self;
} 