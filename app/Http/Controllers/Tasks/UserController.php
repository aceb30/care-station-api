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
}

/*
class UserController extends Controller
{

    public function getPhoto($id)
{
    $user = \App\Models\User::find($id);
    
    Log::info('getPhoto reached');

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    // Ajusta este nombre de campo según tu base de datos
    if (!$user->photo_url || !\Storage::disk('public')->exists($user->photo_url)) {
        return response()->json(['error' => 'Photo not found'], 404);
    }

    $path = \Storage::disk('public')->path($user->photo_url);
    $mime = \File::mimeType($path);

    return response()->file($path, ['Content-Type' => $mime]);
}

 public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 0);

        $query = User::select('id', 'name', 'email', 'photo_path', 'created_at')->orderBy('id');

        if ($perPage > 0) {
            return response()->json($query->paginate($perPage), 200);
        }

        return response()->json(['data' => $query->get()], 200);
    }

    }
*/