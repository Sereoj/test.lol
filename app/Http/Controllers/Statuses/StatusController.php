<?php

namespace App\Http\Controllers\Statuses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Statuses\StoreStatusRequest;
use App\Http\Requests\Statuses\UpdateStatusRequest;
use App\Http\Resources\StatusResource;
use App\Services\StatusService;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    protected StatusService $statusService;

    private const CACHE_KEY_STATUSES_LIST = 'statuses_list';
    private const CACHE_KEY_STATUS = 'status_';
    private const CACHE_MINUTES = 60;

    public function __construct(StatusService $statusService)
    {
        $this->statusService = $statusService;
    }

    public function index()
    {
        $statuses = $this->getFromCacheOrStore(self::CACHE_KEY_STATUSES_LIST, self::CACHE_MINUTES, function () {
            return $this->statusService->getAll();
        });

        return $this->successResponse(StatusResource::collection($statuses));
    }

    public function store(StoreStatusRequest $request)
    {
        $status = $this->statusService->create($request->validated());
        $this->forgetCache(self::CACHE_KEY_STATUSES_LIST); // Очистка кэша после создания
        return $this->successResponse(new StatusResource($status), 201);
    }

    public function show($id)
    {
        $cacheKey = self::CACHE_KEY_STATUS . $id;

        $status = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($id) {
            return $this->statusService->getById($id);
        });

        if ($status) {
            return $this->successResponse(new StatusResource($status));
        }

        return $this->errorResponse('Status not found', 404);
    }

    public function update(UpdateStatusRequest $request, $id)
    {
        $status = $this->statusService->update($id, $request->validated());

        if ($status) {
            $this->forgetCache([
                self::CACHE_KEY_STATUS . $id,
                self::CACHE_KEY_STATUSES_LIST
            ]);

            return $this->successResponse(new StatusResource($status));
        }

        return $this->errorResponse('Status not found', 404);
    }

    public function destroy($id)
    {
        $this->statusService->delete($id);
        $this->forgetCache(self::CACHE_KEY_STATUS . $id);
        $this->forgetCache(self::CACHE_KEY_STATUSES_LIST);

        return $this->successResponse(['message' => 'Status deleted successfully']);
    }
}
