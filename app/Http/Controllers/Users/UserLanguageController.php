<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Language\SetLanguageRequest;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserLanguageController extends Controller
{
    public function setLanguage(SetLanguageRequest $request)
    {
        try {
            if (Auth::check()) {
                $user = Auth::user();
                $user->language = $request->input('language');
                $user->save();
                
                Log::info('User language updated successfully', [
                    'user_id' => $user->id,
                    'language' => $request->input('language')
                ]);
            } else {
                session(['language' => $request->input('language')]);
                
                Log::info('Session language updated successfully', [
                    'session_id' => session()->getId(),
                    'language' => $request->input('language')
                ]);
            }

            return $this->successResponse(['message' => 'Language updated successfully']);
        } catch (Exception $e) {
            Log::error('Error updating language: ' . $e->getMessage(), [
                'user_id' => Auth::id(), 
                'language' => $request->input('language')
            ]);
            return $this->errorResponse('An error occurred while updating the language: ' . $e->getMessage(), 500);
        }
    }
}
