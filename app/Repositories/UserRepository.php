<?php

namespace App\Repositories;

use App\Models\Users\User;
use App\Services\Employment\EmploymentStatusService;
use App\Services\Roles\RoleService;
use App\Services\StatusService;
use App\Services\Users\UserSettingsService;
use App\Store\UserRelations;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserRepository
{
    protected AvatarRepository $avatarRepository;

    protected RoleService $roleService;
    protected EmploymentStatusService $employmentStatusService;
    protected UserSettingsService $userSettingsService;
    protected StatusService $statusService;

    public function __construct(AvatarRepository $avatarRepository,
                                RoleService $roleService,
                                EmploymentStatusService $employmentStatusService,
                                UserSettingsService $userSettingsService,
                                StatusService $statusService

    )
    {
        $this->avatarRepository = $avatarRepository;
        $this->roleService = $roleService;
        $this->employmentStatusService = $employmentStatusService;
        $this->userSettingsService = $userSettingsService;
        $this->statusService = $statusService;
    }

    public function create(array $data)
    {
        Log::info('Starting user creation:', ['data' => $data['email']]);

        $userSettings = $this->userSettingsService->createUserSettings();
        $role = $this->roleService->getRoleByType('user');
        $employmentStatus = $this->employmentStatusService->getEmploymentStatusById(1);
        $status = $this->statusService->getById(1);

        return User::query()->create([
            'username' => $data['username'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'website' => '',
            'cover' => '',
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $role->id,
            'status_id' => $status->id,
            'userSettings_id' => $userSettings->id,
            'usingApps_id' => null,
            'location_id' => null,
            'employment_status_id' => $employmentStatus->id,
            'verification' => false,
            'experience' => 0,
            'gender' => null,
            'age' => null,
        ]);
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

        return $query
            ->with(UserRelations::getUserRelations())->get();
    }

    public function findById(int $id)
    {
        return User::query()
            ->with(UserRelations::getUserRelations())
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
            ->where('slug', $slug)
            ->first();
    }

    public function findByEmail(string $email)
    {
        return User::query()
            ->with(UserRelations::getUserRelations())
            ->where('email', $email)
            ->first();
    }

    public function findBySlugCount(string $slug)
    {
        return User::query()->where('slug', 'like', '%'.$slug.'%')->count();
    }
}
