<?php

namespace App\Repositories;

use App\Events\UserExperienceChanged;
use App\Models\Achievement;
use App\Models\Task;
use App\Models\User;
use App\Models\UserSetting;
use App\Services\RoleService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserRepository
{
    private AvatarRepository $avatarRepository;

    private RoleService $roleService;

    public function __construct(AvatarRepository $avatarRepository, RoleService $roleService)
    {
        $this->avatarRepository = $avatarRepository;
        $this->roleService = $roleService;
    }

    public function create(array $data)
    {
        Log::info('Starting user creation', ['data' => $data]);

        $userSettings = UserSetting::query()->create([
            'is_online' => true,
            'is_preferences_feed' => false,
            'preferences_feed' => 'default',
        ]);

        $role = $this->roleService->getRoleByType('user');

        $user = User::query()->create([
            'username' => $data['username'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $role->id,
            'status_id' => 1,
            'userSettings_id' => $userSettings->id,
            'usingApps_id' => null,
            'location_id' => null,
            'employment_status_id' => null,
            'verification' => false,
            'experience' => 0,
            'gender' => null,
            'age' => null,
        ]);

        $user->userBalance->create([
            'balance' => 0.00,
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

        // Логирование завершения создания пользователя
        Log::info('User creation completed', ['user_id' => $user->id]);

        return $user;
    }

    public function getAll(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = User::query();

        if (! empty($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }

        if (! empty($filters['email'])) {
            $query->where('email', $filters['email']);
        }

        return $query->with(['level', 'achievements', 'role', 'badges', 'usingApps', 'userSettings', 'specializations', 'status', 'following', 'followers', 'employmentStatus', 'location', 'tasks', 'userBalance', 'transactions', 'sources', 'skills', 'avatars'])->get();
    }

    public function findById(int $id)
    {
        return User::query()
            ->with([
                'level',
                'achievements',
                'role',
                'badges',
                'usingApps',
                'userSettings',
                'specializations',
                'status',
                'following',
                'followers',
                'employmentStatus',
                'location',
                'tasks',
                'userBalance',
                'transactions',
                'sources',
                'skills',
                'avatars',
            ])
            ->find($id);
    }

    public function update(User $user, array $data)
    {
        $user->update($data);

        return $user;
    }

    public function delete(User $user)
    {
        $user->delete();
    }

    public function findBySlug(string $slug)
    {
        return User::query()
            ->with([
                'level',
                'achievements',
                'role',
                'badges',
                'usingApps',
                'userSettings',
                'specializations',
                'status',
                'following',
                'followers',
                'employmentStatus',
                'location',
                'tasks',
                'userBalance',
                'transactions',
                'sources',
                'skills',
                'avatars',
            ])
            ->where('slug', $slug)
            ->first();
    }

    public function findByEmail(string $email)
    {
        return User::query()
            ->with([
                'level',
                'achievements',
                'role',
                'badges',
                'usingApps',
                'userSettings',
                'specializations',
                'status',
                'following',
                'followers',
                'employmentStatus',
                'location',
                'tasks',
                'userBalance',
                'transactions',
                'sources',
                'skills',
                'avatars',
            ])
            ->where('email', $email)
            ->first();
    }

    public function findBySlugCount(string $slug)
    {
        return User::query()->where('slug', 'like', '%'.$slug.'%')->count();
    }
}
