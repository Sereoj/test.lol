<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Models\Roles\Role;
use App\Services\Roles\RoleService;
use Illuminate\Support\Facades\Log;
use Exception;

class UserRoleController extends Controller
{
    protected RoleService $roleService;
    
    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_ROLES_LIST = 'roles_list';
    private const CACHE_KEY_ROLE = 'role_';

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Получить все роли.
     */
    public function index()
    {
        try {
            $roles = $this->getFromCacheOrStore(self::CACHE_KEY_ROLES_LIST, self::CACHE_MINUTES, function () {
                return Role::all();
            });
            
            Log::info('Roles retrieved successfully');
            
            return $this->successResponse($roles);
        } catch (Exception $e) {
            Log::error('Error retrieving roles: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Создать новую роль.
     */
    public function store(StoreRoleRequest $request)
    {
        try {
            $data = $request->validated();
            $role = $this->roleService->createRole($data);
            
            Log::info('Role created successfully', ['role_id' => $role->id]);
            
            $this->forgetCache(self::CACHE_KEY_ROLES_LIST);
            
            return $this->successResponse($role, 201);
        } catch (Exception $e) {
            Log::error('Error creating role: ' . $e->getMessage(), ['data' => $request->all()]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Получить роль по ID.
     */
    public function show($id)
    {
        try {
            $cacheKey = self::CACHE_KEY_ROLE . $id;
            $role = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($id) {
                return $this->roleService->getRoleById($id);
            });
            
            Log::info('Role retrieved successfully', ['id' => $id]);
            
            return $this->successResponse($role);
        } catch (Exception $e) {
            Log::error('Error retrieving role: ' . $e->getMessage(), ['id' => $id]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
