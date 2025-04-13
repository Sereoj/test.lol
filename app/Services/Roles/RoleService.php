<?php

namespace App\Services\Roles;

use App\Models\Roles\Role;
use Log;

class RoleService
{
    public function getRoleById($id)
    {
        return Role::query()->find($id);
    }

    public function getRoleByType($type)
    {
        $role = Role::query()->where('type', $type)->first();

        if (! $role) {
            Log::error("Role with type '{$type}' not found.");
        } else {
            Log::info('Role found:', ['role' => $role->toArray()]);
        }

        return $role;
    }

    public function create($data)
    {
        return Role::query()->create($data);
    }
}
