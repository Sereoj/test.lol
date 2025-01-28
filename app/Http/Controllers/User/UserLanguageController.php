<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Language\SetLanguageRequest;
use Exception;
use Illuminate\Support\Facades\Auth;

class UserLanguageController extends Controller
{
    public function setLanguage(SetLanguageRequest $request)
    {
        try {
            if (Auth::check()) {

                $user = Auth::user();
                $user->language = $request->input('language');
                $user->save();

            } else {
                session(['language' => $request->input('language')]);
            }

            return response()->json(['message' => 'Language updated successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'An error occurred while updating the language', 'error' => $e->getMessage()], 500);
        }
    }
}
