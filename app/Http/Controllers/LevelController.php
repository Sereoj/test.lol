<?php

namespace App\Http\Controllers;

use App\Services\LevelService;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    protected LevelService $levelService;

    public function __construct(LevelService $levelService)
    {
        $this->levelService = $levelService;
    }

    /**
     * Получить все уровни.
     */
    public function index()
    {
        return $this->levelService->getAllLevels();
    }

    /**
     * Создать новый уровень.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|array',
            'name.ru' => 'required|string',
            'name.en' => 'required|string',
            'experience_required' => 'required|integer',
        ]);

        $level = $this->levelService->createLevel($request->name, $request->experience_required);

        return response()->json($level, 201);
    }
}
