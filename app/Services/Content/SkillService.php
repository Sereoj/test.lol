<?php

namespace App\Services\Content;

use App\Models\Content\Skill;
use App\Models\Users\UserSkill;
use App\Services\Base\SimpleService;
use Exception;

class SkillService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'skill';

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
        $this->setLogPrefix('SkillService');
    }

    /**
     * Получить все навыки
     *
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws Exception
     */
    public function getAllSkills()
    {
        $cacheKey = $this->buildCacheKey('all_skills');
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () {
            $this->logInfo("Получение всех навыков");
            
            try {
                return Skill::all();
            } catch (Exception $e) {
                $this->logError("Ошибка при получении навыков", [], $e);
                throw new Exception('An error occurred while retrieving skills.');
            }
        });
    }

    /**
     * Создать навык
     *
     * @param array $data Данные навыка
     * @return Skill
     */
    public function storeSkill(array $data)
    {
        $this->logInfo("Создание нового навыка", ['name' => $data['name'] ?? 'not set']);
        
        return $this->transaction(function () use ($data) {
            $skill = Skill::create($data);
            
            // Сбрасываем кеш
            $this->forgetCache($this->buildCacheKey('all_skills'));
            
            $this->logInfo("Навык успешно создан", ['skill_id' => $skill->id]);
            
            return $skill;
        });
    }

    /**
     * Получить навык по ID
     *
     * @param int $id ID навыка
     * @return Skill
     * @throws Exception
     */
    public function getSkillById($id)
    {
        $cacheKey = $this->buildCacheKey('skill', [$id]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($id) {
            $this->logInfo("Получение навыка по ID", ['skill_id' => $id]);
            
            try {
                return Skill::findOrFail($id);
            } catch (Exception $e) {
                $this->logError("Ошибка при получении навыка", ['skill_id' => $id], $e);
                throw new Exception('An error occurred while retrieving the skill.');
            }
        });
    }

    /**
     * Обновить навык
     *
     * @param int $id ID навыка
     * @param array $data Данные для обновления
     * @return bool
     * @throws Exception
     */
    public function updateSkill(int $id, array $data)
    {
        $this->logInfo("Обновление навыка", ['skill_id' => $id]);
        
        return $this->transaction(function () use ($id, $data) {
            $skill = Skill::find($id);
            
            if (!$skill) {
                $this->logWarning("Навык не найден при обновлении", ['skill_id' => $id]);
                throw new Exception('Skill not found');
            }
            
            $result = $skill->update($data);
            
            // Сбрасываем кеш
            $this->forgetCache($this->buildCacheKey('skill', [$id]));
            $this->forgetCache($this->buildCacheKey('all_skills'));
            
            $this->logInfo("Навык успешно обновлен", ['skill_id' => $id]);
            
            return $result;
        });
    }

    /**
     * Удалить навык
     *
     * @param int $id ID навыка
     * @return bool
     * @throws Exception
     */
    public function deleteSkill(int $id)
    {
        $this->logInfo("Удаление навыка", ['skill_id' => $id]);
        
        return $this->transaction(function () use ($id) {
            $skill = Skill::find($id);
            
            if (!$skill) {
                $this->logWarning("Навык не найден при удалении", ['skill_id' => $id]);
                throw new Exception('Skill not found');
            }
            
            $result = $skill->delete();
            
            // Сбрасываем кеш
            $this->forgetCache($this->buildCacheKey('skill', [$id]));
            $this->forgetCache($this->buildCacheKey('all_skills'));
            $this->forgetCache($this->buildCacheKey('user_skills', ['*']));
            
            $this->logInfo("Навык успешно удален", ['skill_id' => $id]);
            
            return $result;
        });
    }

    /**
     * Добавить навык пользователю
     *
     * @param int $userId ID пользователя
     * @param array $skillIds Массив ID навыков
     * @return array
     * @throws Exception
     */
    public function addSkillToUser($userId, array $skillIds)
    {
        $this->logInfo("Добавление навыков пользователю", [
            'user_id' => $userId,
            'skill_ids' => $skillIds
        ]);
        
        return $this->transaction(function () use ($userId, $skillIds) {
            $addedSkills = [];

            foreach ($skillIds as $skillId) {
                try {
                    $skill = Skill::findOrFail($skillId);

                    // Проверяем, существует ли уже запись для данного пользователя и навыка
                    $existingRecord = UserSkill::query()->where('user_id', $userId)
                        ->where('skill_id', $skillId)
                        ->first();

                    if ($existingRecord) {
                        $this->logInfo("Навык уже добавлен пользователю", [
                            'user_id' => $userId,
                            'skill_id' => $skillId
                        ]);
                        continue;
                    }

                    $userSkill = UserSkill::create([
                        'user_id' => $userId,
                        'skill_id' => $skill->id,
                    ]);
                    
                    $addedSkills[] = $userSkill;
                    
                    $this->logInfo("Навык добавлен пользователю", [
                        'user_id' => $userId,
                        'skill_id' => $skillId
                    ]);
                } catch (Exception $e) {
                    $this->logError("Ошибка при добавлении навыка пользователю", [
                        'user_id' => $userId,
                        'skill_id' => $skillId
                    ], $e);
                    throw new Exception('An error occurred while adding the skills to the user.');
                }
            }
            
            // Сбрасываем кеш навыков пользователя
            $this->forgetCache($this->buildCacheKey('user_skills', [$userId]));
            
            return $addedSkills;
        });
    }

    /**
     * Удалить навык у пользователя
     *
     * @param int $userId ID пользователя
     * @param int $skillId ID навыка
     * @return bool
     * @throws Exception
     */
    public function removeSkillFromUser($userId, $skillId)
    {
        $this->logInfo("Удаление навыка у пользователя", [
            'user_id' => $userId,
            'skill_id' => $skillId
        ]);
        
        return $this->transaction(function () use ($userId, $skillId) {
            try {
                $skillUser = UserSkill::query()
                    ->where('user_id', $userId)
                    ->where('skill_id', $skillId)
                    ->firstOrFail();

                $result = $skillUser->delete();
                
                // Сбрасываем кеш навыков пользователя
                $this->forgetCache($this->buildCacheKey('user_skills', [$userId]));
                
                $this->logInfo("Навык успешно удален у пользователя", [
                    'user_id' => $userId,
                    'skill_id' => $skillId
                ]);
                
                return $result;
            } catch (Exception $e) {
                $this->logError("Ошибка при удалении навыка у пользователя", [
                    'user_id' => $userId,
                    'skill_id' => $skillId
                ], $e);
                throw new Exception('An error occurred while removing the skill from the user.');
            }
        });
    }

    /**
     * Получить навыки пользователя
     *
     * @param int $userId ID пользователя
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws Exception
     */
    public function getUserSkills($userId)
    {
        $cacheKey = $this->buildCacheKey('user_skills', [$userId]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
            $this->logInfo("Получение навыков пользователя", ['user_id' => $userId]);
            
            try {
                return UserSkill::query()->where('user_id', $userId)->get();
            } catch (Exception $e) {
                $this->logError("Ошибка при получении навыков пользователя", ['user_id' => $userId], $e);
                throw new Exception('An error occurred while retrieving the user skills.');
            }
        });
    }
    
    /**
     * Получить пользователей с определенным навыком
     *
     * @param int $skillId ID навыка
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersBySkill(int $skillId)
    {
        $cacheKey = $this->buildCacheKey('skill_users', [$skillId]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($skillId) {
            $this->logInfo("Получение пользователей с навыком", ['skill_id' => $skillId]);
            
            try {
                return UserSkill::query()
                    ->where('skill_id', $skillId)
                    ->with('user')
                    ->get()
                    ->pluck('user');
            } catch (Exception $e) {
                $this->logError("Ошибка при получении пользователей с навыком", ['skill_id' => $skillId], $e);
                throw new Exception('An error occurred while retrieving users with the skill.');
            }
        });
    }
}
