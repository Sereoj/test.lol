<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function getOriginal($filename)
    {
        $path = '/originals/' . $filename;

        if (Storage::exists($path)) {
            return response()->file(storage_path('app/' . $path));
        }
        return null;
    }
}
