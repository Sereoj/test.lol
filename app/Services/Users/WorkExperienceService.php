<?php

namespace App\Services\Users;

use App\Repositories\WorkExperienceRepository;
use App\Services\BaseService;
use App\Traits\LoggableTrait;
use Carbon\Carbon;

class WorkExperienceService extends BaseService
{
    use LoggableTrait;

    protected WorkExperienceRepository $workExperienceRepository;

    public function __construct(WorkExperienceRepository $workExperienceRepository)
    {
        $this->workExperienceRepository = $workExperienceRepository;
    }

    public function getAll()
    {
        return $this->workExperienceRepository->getByUserId(auth()->id());
    }

    public function create(array $data)
    {
        if (isset($data['is_current']) && $data['is_current']) {
            $this->updateCurrentWorkExperience($data['user_id']);
        }

        if (isset($data['end_date']) && isset($data['start_date'])) {
            if (Carbon::parse($data['end_date'])->lt(Carbon::parse($data['start_date']))) {
                $this->logError('End date cannot be before start date', [
                    'user_id' => $data['user_id'] ?? null,
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date']
                ]);
                throw new \InvalidArgumentException('Дата окончания не может быть раньше даты начала');
            }
        }

        $workExperience = $this->workExperienceRepository->create($data);

        $this->logInfo('Work experience created', [
            'user_id' => $data['user_id'],
            'work_experience_id' => $workExperience->id
        ]);

        return $workExperience;
    }

    public function getById(int $id)
    {
        return $this->workExperienceRepository->findById($id);
    }

    public function update(int $id, array $data)
    {
        $workExperience = $this->workExperienceRepository->findById($id);

        if (!$workExperience) {
            $this->logError('Work experience not found', ['id' => $id]);
            throw new \Exception('Опыт работы не найден');
        }

        if (isset($data['is_current']) && $data['is_current']) {
            $this->updateCurrentWorkExperience($workExperience->user_id, $id);
        }

        if (isset($data['end_date']) && isset($data['start_date'])) {
            if (Carbon::parse($data['end_date'])->lt(Carbon::parse($data['start_date']))) {
                $this->logError('End date cannot be before start date', [
                    'work_experience_id' => $id,
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date']
                ]);
                throw new \InvalidArgumentException('Дата окончания не может быть раньше даты начала');
            }
        }

        $this->workExperienceRepository->update($id, $data);

        $this->logInfo('Work experience updated', [
            'work_experience_id' => $id,
            'user_id' => $workExperience->user_id
        ]);

        return $this->workExperienceRepository->findById($id);
    }

    public function delete($id)
    {
        $workExperience = $this->workExperienceRepository->findById($id);

        if (!$workExperience) {
            $this->logError('Work experience not found', ['id' => $id]);
            throw new \Exception('Опыт работы не найден');
        }

        $this->workExperienceRepository->delete($id);

        $this->logInfo('Work experience deleted', [
            'work_experience_id' => $id,
            'user_id' => $workExperience->user_id
        ]);

        return true;
    }

    public function getByUserId(int $userId)
    {
        return $this->workExperienceRepository->getByUserId($userId);
    }

    protected function updateCurrentWorkExperience(int $userId, ?int $exceptId = null): void
    {
        $currentWork = $this->workExperienceRepository->getCurrentWorkExperience($userId);

        if ($currentWork && $currentWork->id !== $exceptId) {
            $this->workExperienceRepository->update($currentWork->id, ['is_current' => false]);
        }
    }
}
