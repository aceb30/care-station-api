<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Devuelve la foto de perfil del usuario.
     * GET /user/{user_id}/photo
     */
    public function getPhoto($id)
{
    $user = \App\Models\User::find($id);
    
    Log::info('getPhoto reached');

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    // Ajusta este nombre de campo según tu base de datos
    if (!$user->photo_path || !\Storage::disk('public')->exists($user->photo_path)) {
        return response()->json(['error' => 'Photo not found'], 404);
    }

    $path = \Storage::disk('public')->path($user->photo_path);
    $mime = \File::mimeType($path);

    return response()->file($path, ['Content-Type' => $mime]);
}

    }
