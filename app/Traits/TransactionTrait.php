<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Трейт для работы с транзакциями в базе данных
 */
trait TransactionTrait
{
    /**
     * Глубина вложенности транзакций
     *
     * @var int
     */
    protected $transactionLevel = 0;

    /**
     * Выполнить операцию в транзакции
     *
     * @param callable $callback Операция для выполнения
     * @param int|null $attempts Количество попыток при ошибках блокировки
     * @return mixed Результат выполнения операции
     * @throws Throwable
     */
    protected function transaction(callable $callback, ?int $attempts = null): mixed
    {
        $this->beginTransaction();

        try {
            $result = $attempts === null
                ? DB::transaction($callback)
                : DB::transaction($callback, $attempts);

            $this->commitTransaction();

            return $result;
        } catch (Throwable $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Начать транзакцию
     *
     * @return void
     */
    protected function beginTransaction(): void
    {
        $this->transactionLevel++;

        if ($this->transactionLevel === 1) {
            DB::beginTransaction();
            if (method_exists($this, 'logDebug')) {
                $this->logDebug('DB transaction started');
            }
        } else {
            if (method_exists($this, 'logDebug')) {
                $this->logDebug('DB transaction level increased', ['level' => $this->transactionLevel]);
            }
        }
    }

    /**
     * Зафиксировать транзакцию
     *
     * @return void
     */
    protected function commitTransaction(): void
    {
        if ($this->transactionLevel === 0) {
            if (method_exists($this, 'logWarning')) {
                $this->logWarning('Attempted to commit transaction without beginning one');
            }
            return;
        }

        $this->transactionLevel--;

        if ($this->transactionLevel === 0) {
            DB::commit();
            if (method_exists($this, 'logDebug')) {
                $this->logDebug('DB transaction committed');
            }
        } else {
            if (method_exists($this, 'logDebug')) {
                $this->logDebug('DB transaction level decreased', ['level' => $this->transactionLevel]);
            }
        }
    }

    /**
     * Отменить транзакцию
     *
     * @return void
     */
    protected function rollbackTransaction(): void
    {
        if ($this->transactionLevel === 0) {
            if (method_exists($this, 'logWarning')) {
                $this->logWarning('Attempted to rollback transaction without beginning one');
            }
            return;
        }

        $level = $this->transactionLevel;
        $this->transactionLevel = 0;

        DB::rollBack();

        if (method_exists($this, 'logDebug')) {
            $this->logDebug('DB transaction rolled back', ['from_level' => $level]);
        }
    }

    /**
     * Проверить, находимся ли мы внутри транзакции
     *
     * @return bool
     */
    protected function inTransaction(): bool
    {
        return $this->transactionLevel > 0;
    }

    /**
     * Получить текущий уровень транзакции
     *
     * @return int
     */
    protected function getTransactionLevel(): int
    {
        return $this->transactionLevel;
    }
}
