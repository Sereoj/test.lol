<?php

namespace App\Services\Apps;

use App\Models\Apps\App;
use App\Services\Base\SimpleService;
use Exception;
use Illuminate\Database\Eloquent\Collection;

/**
 * Сервис для работы с приложениями
 */
class AppService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'app';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 60;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('AppService');
    }

    /**
     * Создать новое приложение
     *
     * @param array $data
     * @return App
     * @throws \Throwable
     */
    public function createApp(array $data): App
    {
        $this->logInfo('Создание нового приложения', $data);

        return $this->transaction(function () use ($data) {
            try {
                $app = App::create([
                    'name' => json_encode($data['name']),
                    'path' => $data['path'],
                ]);

                $this->logInfo('Создано новое приложение', [
                    'id' => $app->id,
                    'path' => $app->path
                ]);

                // Очистка кеша
                $this->forgetCache($this->buildCacheKey('all'));

                return $app;
            } catch (Exception $e) {
                $this->logError('Ошибка при создании приложения', $data, $e);
                throw $e;
            }
        });
    }

    /**
     * Получить все приложения
     *
     * @return Collection
     */
    public function getAllApps(): Collection
    {
        $cacheKey = $this->buildCacheKey('all');

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () {
            $this->logInfo('Получение всех приложений');

            try {
                $apps = App::all();

                $this->logInfo('Получены приложения', [
                    'count' => $apps->count()
                ]);

                return $apps;
            } catch (Exception $e) {
                $this->logError('Ошибка при получении приложений', [], $e);
                return collect();
            }
        });
    }

    /**
     * Получить приложение по ID
     *
     * @param int $id
     * @return App|null
     */
    public function getAppById($id): ?App
    {
        $cacheKey = $this->buildCacheKey('app', [$id]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($id) {
            $this->logInfo('Получение приложения по ID', ['id' => $id]);

            try {
                $app = App::query()->findOrFail($id);

                $this->logInfo('Получено приложение', ['id' => $id]);

                return $app;
            } catch (Exception $e) {
                $this->logWarning('Приложение не найдено', ['id' => $id]);
                return null;
            }
        });
    }

    /**
     * Обновить приложение
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws \Throwable
     */
    public function updateApp(int $id, array $data): bool
    {
        $this->logInfo('Обновление приложения', ['id' => $id, 'data' => $data]);

        return $this->transaction(function () use ($id, $data) {
            try {
                $app = App::query()->findOrFail($id);
                $result = $app->update($data);

                if ($result) {
                    $this->logInfo('Приложение обновлено', ['id' => $id]);

                    // Очистка кеша
                    $this->forgetCache([
                        $this->buildCacheKey('all'),
                        $this->buildCacheKey('app', [$id])
                    ]);
                } else {
                    $this->logWarning('Не удалось обновить приложение', ['id' => $id]);
                }

                return $result;
            } catch (Exception $e) {
                $this->logError('Ошибка при обновлении приложения', ['id' => $id], $e);
                return false;
            }
        });
    }

    /**
     * Удалить приложение
     *
     * @param int $id
     * @return bool
     * @throws \Throwable
     */
    public function deleteApp(int $id): bool
    {
        $this->logInfo('Удаление приложения', ['id' => $id]);

        return $this->transaction(function () use ($id) {
            try {
                $app = App::query()->findOrFail($id);
                $result = $app->delete();

                if ($result) {
                    $this->logInfo('Приложение удалено', ['id' => $id]);

                    // Очистка кеша
                    $this->forgetCache([
                        $this->buildCacheKey('all'),
                        $this->buildCacheKey('app', [$id])
                    ]);
                } else {
                    $this->logWarning('Не удалось удалить приложение', ['id' => $id]);
                }

                return $result;
            } catch (Exception $e) {
                $this->logError('Ошибка при удалении приложения', ['id' => $id], $e);
                return false;
            }
        });
    }
}
