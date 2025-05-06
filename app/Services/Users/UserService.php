<?php

namespace App\Services\Users;

use App\Models\Roles\Role;
use App\Models\Users\User;
use App\Repositories\UserRepository;
use App\Services\Media\AvatarService;
use App\Services\Media\GravatarService;
use App\Utils\TextUtil;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserService
{
    protected UserRepository $userRepository;
    protected UserSettingsService $userSettingsService;
    protected UserBadgeService $userBadgeService;
    protected AvatarService  $avatarService;
    protected GravatarService $gravatarService;

    public function __construct(
        UserRepository $userRepository,
        UserSettingsService $userSettingsService,
        UserBadgeService $userBadgeService,
        AvatarService $avatarService,
        GravatarService $gravatarService,
    )
    {
        $this->userRepository = $userRepository;
        $this->userSettingsService = $userSettingsService;
        $this->userBadgeService = $userBadgeService;
        $this->avatarService = $avatarService;
        $this->gravatarService = $gravatarService;
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

        $gravatar = $this->gravatarService->getPath($data['email']);

        $this->avatarService->setAvatar($user->id, $gravatar);
        $this->userBadgeService->createBadgeForUser($user, 1);
        $this->userBadgeService->createBadgeForUser($user, 2);
        $this->userBadgeService->setActiveBadgeForUser($user->id, 2);
        $this->userSettingsService->createNotificationSettings($user);
        $this->userSettingsService->createBalance($user);
        $this->userSettingsService->attachTask($user);
        $this->userSettingsService->attachAchievement($user);

        // Логирование успешного создания пользователя
        Log::info('User created successfully', ['user_id' => $user->id, 'username' => $user->username]);

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
        return $this->userRepository->findBySlug($slug);
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
