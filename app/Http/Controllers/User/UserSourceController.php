<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Source\AddSourceRequest;
use App\Http\Requests\Source\RemoveSourceRequest;
use App\Services\UserSourceService;
use Exception;
use Illuminate\Support\Facades\Auth;

class UserSourceController extends Controller
{
    protected $userSourceService;

    public function __construct(UserSourceService $userSourceService)
    {
        $this->userSourceService = $userSourceService;
    }

    public function addSource(AddSourceRequest $request)
    {
        try {
            $user = Auth::user();

            $this->userSourceService->addSourceToUser($user->id, $request->input('source_id'));

            return response()->json(['message' => 'Source added successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function removeSource(RemoveSourceRequest $request)
    {
        try {
            $user = Auth::user();
            $this->userSourceService->removeSourceFromUser($user->id, $request->input('source_id'));

            return response()->json(['message' => 'Source removed successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getUserSources()
    {
        try {
            $user = Auth::user();
            $sources = $this->userSourceService->getUserSources($user->id);

            return response()->json($sources, 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
