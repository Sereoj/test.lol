<?php

namespace App\Services\Users;

use App\Models\Roles\Role;
use App\Models\Users\User;
use App\Repositories\UserRepository;
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

    public function getAllUsers(array $filters = [])
    {
        return $this->userRepository->getAll($filters);
    }

    public function createUser(array $data)
    {
        try {
            $data['description'] = 'Welcome to '.config('app.name').'!';
            $data['slug'] = TextUtil::generateUniqueSlug($data['username'], $this->userRepository->findBySlugCount(Str::slug($data['username'])));

            $user = $this->userRepository->create($data);

            return $user;
        } catch (\Exception $e) {
            Log::error('User creation failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findUserByEmail(string $email)
    {
        return $this->userRepository->findByEmail($email);
    }

    public function findUserById(int $id)
    {
        return $this->userRepository->findById($id);
    }

    public function updateUser(User $user, array $data): User
    {
        return $this->userRepository->update($user, $data);
    }

    public function deleteUser(User $user): void
    {
        $this->userRepository->delete($user);
    }

    public function changeUserRole(User $user, int $roleId)
    {
        $role = Role::query()->findOrFail($roleId);
        $user->update(['role_id' => $roleId]);

        return $user;
    }
}
