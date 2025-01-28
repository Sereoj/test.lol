<?php

namespace App\Http\Controllers;

use App\Http\Requests\App\AppRequest;
use App\Services\AppService;

class AppController extends Controller
{
    protected AppService $appService;

    public function __construct(AppService $appService)
    {
        $this->appService = $appService;
    }

    public function index()
    {
        try {
            $apps = $this->appService->getAllApps();

            return response()->json($apps);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch apps'], 500);
        }
    }

    public function store(AppRequest $request)
    {
        try {
            $app = $this->appService->createApp($request->validated());

            return response()->json($app, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create app'], 500);
        }
    }

    public function show($id)
    {
        try {
            $app = $this->appService->getAppById($id);

            return response()->json($app);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch app'], 500);
        }
    }

    public function update(AppRequest $request, $id)
    {
        try {
            $this->appService->updateApp($id, $request->validated());

            return response()->json(['message' => 'App updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update app'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->appService->deleteApp($id);

            return response()->json(['message' => 'App deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete app'], 500);
        }
    }
}
