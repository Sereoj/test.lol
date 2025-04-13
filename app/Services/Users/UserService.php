<?php

namespace App\Services\Users;

use App\Events\UserExperienceChanged;
use App\Models\Content\Achievement;
use App\Models\Content\Task;
use App\Models\Roles\Role;
use App\Models\Users\User;
use App\Repositories\UserRepository;
use App\Services\BaseService;
use App\Utils\TextUtil;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAll(array $filters = [])
    {
        return $this->userRepository->getAll($filters);
    }

    public function create(array $data)
    {
        $data['description'] = 'Welcome to '.config('app.name').'!';
        $data['slug'] = TextUtil::generateUniqueSlug($data['username'], $this->userRepository->findBySlugCount(Str::slug($data['username'])));

        $user = $this->userRepository->create($data);

        $user->notificationSettings()->create([
            'notify_on_new_message' => true,
            'notify_on_new_follower' => true,
            'notify_on_post_like' => true,
            'notify_on_comment' => true,
            'notify_on_comment_like' => true,
            'notify_on_mention' => true
        ]);

        $user->userBalance()->create([
            'balance' => 0.00,
            'currency' => 'USD',
        ]);

        $user->userBalance()->create([
            'balance' => 0.00,
            'currency' => 'RUB',
        ]);

        // Логирование успешного создания пользователя
        Log::info('User created successfully', ['user_id' => $user->id, 'username' => $user->username]);

        $defaultTasks = Task::all();
        foreach ($defaultTasks as $task) {
            $user->tasks()->attach($task->id, ['progress' => 0, 'completed' => false]);
        }

        // Логирование присвоения заданий пользователю
        Log::info('Default tasks assigned to user', ['user_id' => $user->id]);

        $achievement = Achievement::first();
        if ($achievement) {
            $user->achievements()->syncWithoutDetaching([$achievement->id]);
            $points = $achievement->points;
            $user->update(['experience' => $user->experience + $points]);

            // Логирование присвоения достижения и обновления опыта
            Log::info('Achievement assigned and experience updated', ['user_id' => $user->id, 'achievement_id' => $achievement->id, 'points' => $points]);

            event(new UserExperienceChanged($user));
        }

        return $user;
    }

    public function getByEmail(string $email)
    {
        return $this->userRepository->findByEmail($email) ?: null;
    }

    public function getById(int $id)
    {
        return $this->userRepository->findById($id) ?: null;
    }

    public function getBySlug(string $slug)
    {
        return $this->userRepository->findBySlug($slug) ?: null;
    }

    public function updateUser(int $id, array $data)
    {
        $user = $this->getById($id);
        return $this->userRepository->update($user, $data);
    }

    public function deleteUser(User $user): void
    {
        $this->userRepository->delete($user);
    }

    public function changeUserRole(User $user, int $roleId)
    {
        Role::query()->findOrFail($roleId);
        return $user->update(['role_id' => $roleId]);
    }
}
