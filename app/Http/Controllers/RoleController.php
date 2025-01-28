<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index()
    {
        $roles = Role::all(); // Получаем все роли

        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|array',
            'name.ru' => 'required|string',
            'name.en' => 'required|string',
            'type' => 'required|in:admin,user,moderator,guest',
        ]);

        $role = $this->roleService->createRole($data);

        return response()->json($role, 201);
    }

    public function show($id)
    {
        $role = $this->roleService->getRoleById($id);

        return response()->json($role);
    }
}
