<?php

namespace App\Services\Users;

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
            return $this->userRepository->create($data);
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
