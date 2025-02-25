<?php

namespace App\Http\Controllers;

use App\Http\Resources\Systems\InitResource;
use App\Services\Content\TagService;
use Illuminate\Http\Request;

class InitController extends Controller
{
    public function init()
    {
        return InitResource::make(\request());
    }
}
