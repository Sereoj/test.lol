<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RoleController extends Controller
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Получить все роли.
     */
    public function index()
    {
        // Кешируем список ролей
        $roles = Cache::remember('roles_list', now()->addMinutes(10), function () {
            return Role::all();
        });

        return response()->json($roles);
    }

    /**
     * Создать новую роль.
     */
    public function store(StoreRoleRequest $request)
    {
        $data = $request->validated();
        $role = $this->roleService->createRole($data);

        // После создания новой роли сбрасываем кеш с ролями
        Cache::forget('roles_list');

        return response()->json($role, 201);
    }

    /**
     * Получить роль по ID.
     */
    public function show($id)
    {
        $role = $this->roleService->getRoleById($id);

        return response()->json($role);
    }
}
