<?php

namespace App\Services\Acquiring;

/**
 * Сервис платежной системы Anypay
 */
class AnypayService extends AcquiringBaseService
{
    /**
     * Название платежной системы
     *
     * @var string
     */
    protected string $client = 'anypay';
}
