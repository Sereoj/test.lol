<?php

namespace App\Http\Controllers;

use App\Services\InitService;

class InitController extends Controller
{
    protected InitService $initService;
    public function __construct(InitService $initService)
    {
        $this->initService = $initService;
    }
    public function init()
    {
        return response()->json($this->initService->getInfo(), 200);
    }
}
