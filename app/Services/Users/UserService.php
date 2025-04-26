<?php

namespace App\Services\Users;

use App\Events\UserExperienceChanged;
use App\Models\Content\Achievement;
use App\Models\Content\Task;
use App\Models\Roles\Role;
use App\Models\Users\User;
use App\Notifications\NewMessageNotification;
use App\Repositories\UserRepository;
use App\Services\BaseService;
use App\Services\Media\AvatarService;
use App\Services\UserMessageService;
use App\Services\UserSettingsService;
use App\Utils\TextUtil;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserService
{
    protected UserRepository $userRepository;
    protected UserSettingsService $userSettingsService;
    protected UserMessageService $userMessageService;
    protected UserBadgeService $userBadgeService;
    protected AvatarService  $avatarService;

    public function __construct(
        UserRepository $userRepository,
        UserSettingsService $userSettingsService,
        UserMessageService $userMessageService,
        UserBadgeService $userBadgeService,
        AvatarService $avatarService,
    )
    {
        $this->userRepository = $userRepository;
        $this->userSettingsService = $userSettingsService;
        $this->userMessageService = $userMessageService;
        $this->userBadgeService = $userBadgeService;
        $this->avatarService = $avatarService;
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

        $this->avatarService->setAvatar($user->id, 'default.png');
        $this->userBadgeService->createBadgeForUser($user, 1);
        $this->userBadgeService->createBadgeForUser($user, 2);
        $this->userBadgeService->setActiveBadgeForUser($user->id, 2);
        $this->userSettingsService->createNotificationSettings($user);
        $this->userSettingsService->createBalance($user);
        $this->userSettingsService->attachTask($user);
        $this->userSettingsService->attachAchievement($user);

        $message = $this->userMessageService->sendMessage(1, $user->id);
        if($message)
        {
            $user->notify(new NewMessageNotification($message));
        }
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
