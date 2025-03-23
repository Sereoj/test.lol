<?php

namespace App\Services\Roles;

use App\Models\Roles\Role;
use App\Services\Base\SimpleService;
use Exception;

class RoleService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'role';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 120;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('RoleService');
    }

    /**
     * Получить роль по ID
     *
     * @param int $id ID роли
     * @return Role|null
     */
    public function getRoleById(int $id): ?Role
    {
        $cacheKey = $this->buildCacheKey('role_by_id', [$id]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($id) {
            $this->logInfo("Получение роли по ID", ['role_id' => $id]);

            try {
                $role = Role::query()->find($id);

                if (!$role) {
                    $this->logWarning("Роль не найдена", ['role_id' => $id]);
                    return null;
                }

                return $role;
            } catch (Exception $e) {
                $this->logError("Ошибка при получении роли по ID", ['role_id' => $id], $e);
                return null;
            }
        });
    }

    /**
     * Получить роль по типу
     *
     * @param string $type Тип роли
     * @return Role|null
     */
    public function getRoleByType(string $type): ?Role
    {
        $cacheKey = $this->buildCacheKey('role_by_type', [$type]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($type) {
            $this->logInfo("Получение роли по типу", ['type' => $type]);

            try {
                $role = Role::query()->where('type', $type)->first();

                if (!$role) {
                    $this->logWarning("Роль с типом '{$type}' не найдена");
                    return null;
                }

                $this->logInfo("Роль успешно найдена", [
                    'role_id' => $role->id,
                    'type' => $role->type
                ]);

                return $role;
            } catch (Exception $e) {
                $this->logError("Ошибка при получении роли по типу", ['type' => $type], $e);
                return null;
            }
        });
    }

    /**
     * Создать новую роль
     *
     * @param array $data Данные роли
     * @return Role
     * @throws Exception|\Throwable
     */
    public function createRole(array $data): Role
    {
        $this->logInfo("Создание новой роли", ['type' => $data['type'] ?? 'не указан']);

        return $this->transaction(function () use ($data) {
            try {
                $role = Role::query()->create($data);

                // Сбрасываем кеши
                $this->forgetCache($this->buildCacheKey('role_by_id', [$role->id]));
                $this->forgetCache($this->buildCacheKey('role_by_type', [$role->type]));
                $this->forgetCache($this->buildCacheKey('all_roles'));

                $this->logInfo("Роль успешно создана", [
                    'role_id' => $role->id,
                    'type' => $role->type
                ]);

                return $role;
            } catch (Exception $e) {
                $this->logError("Ошибка при создании роли", [
                    'type' => $data['type'] ?? 'не указан'
                ], $e);

                throw new Exception("Не удалось создать роль: " . $e->getMessage());
            }
        });
    }

    /**
     * Получить все роли
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllRoles()
    {
        $cacheKey = $this->buildCacheKey('all_roles');

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () {
            $this->logInfo("Получение всех ролей");

            try {
                return Role::all();
            } catch (Exception $e) {
                $this->logError("Ошибка при получении всех ролей", [], $e);
                throw new Exception("Не удалось получить список ролей: " . $e->getMessage());
            }
        });
    }

    /**
     * Обновить роль
     *
     * @param int $id ID роли
     * @param array $data Данные для обновления
     * @return Role|null
     * @throws Exception|\Throwable
     */
    public function updateRole(int $id, array $data): ?Role
    {
        $this->logInfo("Обновление роли", ['role_id' => $id]);

        return $this->transaction(function () use ($id, $data) {
            try {
                $role = Role::query()->find($id);

                if (!$role) {
                    $this->logWarning("Роль не найдена при обновлении", ['role_id' => $id]);
                    return null;
                }

                $role->update($data);

                // Сбрасываем кеши
                $this->forgetCache($this->buildCacheKey('role_by_id', [$id]));
                $this->forgetCache($this->buildCacheKey('role_by_type', [$role->type]));
                $this->forgetCache($this->buildCacheKey('all_roles'));

                $this->logInfo("Роль успешно обновлена", ['role_id' => $id]);

                return $role;
            } catch (Exception $e) {
                $this->logError("Ошибка при обновлении роли", ['role_id' => $id], $e);
                throw new Exception("Не удалось обновить роль: " . $e->getMessage());
            }
        });
    }

    /**
     * Удалить роль
     *
     * @param int $id ID роли
     * @return bool
     * @throws Exception
     */
    public function deleteRole(int $id): bool
    {
        $this->logInfo("Удаление роли", ['role_id' => $id]);

        return $this->transaction(function () use ($id) {
            try {
                $role = Role::query()->find($id);

                if (!$role) {
                    $this->logWarning("Роль не найдена при удалении", ['role_id' => $id]);
                    return false;
                }

                $type = $role->type;
                $result = $role->delete();

                // Сбрасываем кеши
                $this->forgetCache($this->buildCacheKey('role_by_id', [$id]));
                $this->forgetCache($this->buildCacheKey('role_by_type', [$type]));
                $this->forgetCache($this->buildCacheKey('all_roles'));

                $this->logInfo("Роль успешно удалена", ['role_id' => $id]);

                return $result;
            } catch (Exception $e) {
                $this->logError("Ошибка при удалении роли", ['role_id' => $id], $e);
                throw new Exception("Не удалось удалить роль: " . $e->getMessage());
            }
        });
    }
}
