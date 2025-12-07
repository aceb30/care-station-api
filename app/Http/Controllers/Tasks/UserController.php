<?php

// docker exec -it care-station-api-app-1 bash
// & curl.exe http://localhost:8001/api/user/1/photo
/*
App\Models\User::create([
  'names'     => 'Juan',
  'surnames'  => 'Pérez',
  'email'     => 'juan@example.com',
  'password'  => bcrypt('secret123'),
  'cellphone' => '600123456',
  'photo_url' => null,
]);
*/
namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function __construct()
    {
        // aplicar auth solo a este endpoint
        $this->middleware('auth:sanctum')->only('getPhoto');
    }

    /**
     * GET /api/user/{user}/photo
     * Solo el usuario autenticado puede ver su propia foto (cambia la regla si necesitas otra)
     */
    public function getPhoto(Request $request, User $user)
    {
        $auth = $request->user();
        Log::info('getPhoto', ['requested' => $user->user_id, 'auth' => $auth?->user_id]);

        if (!$auth) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // autorización simple: solo el propio usuario
        if ($auth->user_id !== $user->user_id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $photo = $user->photo_url;
        if (!$photo) {
            return response()->json(['error' => 'Photo not set'], 404);
        }

        // Si es URL externa (añadimos esquema si falta)
        $candidate = $photo;
        if (!preg_match('#^https?://#i', $candidate) && str_contains($candidate, '.')) {
            $candidate = 'https://' . $candidate;
        }

        if (filter_var($candidate, FILTER_VALIDATE_URL)) {
            // redirigir al recurso externo (alternativa: proxyear el contenido y devolverlo)
            return redirect()->away($candidate);
        }

        // Caso archivo en storage/app/public
        if (!Storage::disk('public')->exists($photo)) {
            return response()->json([
                'error' => 'Photo not found on disk',
                'photo_value' => $photo,
                'storage_path' => Storage::disk('public')->path($photo) ?? null
            ], 404);
        }

        $path = Storage::disk('public')->path($photo);
        $mime = \File::mimeType($path) ?: 'application/octet-stream';

        return response()->file($path, ['Content-Type' => $mime]);
    }

    public function updatePhoto(Request $request, User $user)
    {
        $auth = $request->user();
        if (!$auth) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if ($auth->user_id !== $user->user_id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'photo_url' => ['required', 'string', 'max:255'],
        ]);

        $url = trim($data['photo_url']);

        // Normalizar: añadir https si falta (si parece dominio)
        if (!preg_match('#^https?://#i', $url) && strpos($url, '.') !== false) {
            $url = 'https://' . $url;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'Invalid URL'], 422);
        }

        $user->photo_url = $url;
        $user->save();

        return response()->json(['message' => 'Photo updated', 'photo_url' => $user->photo_url], 200);
    }
}
