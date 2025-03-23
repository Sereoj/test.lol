<?php

namespace App\Services\Acquiring;

/**
 * Сервис платежной системы Enot
 */
class EnotService extends AcquiringBaseService
{
    /**
     * Название платежной системы
     *
     * @var string
     */
    protected string $client = 'enot';
}
