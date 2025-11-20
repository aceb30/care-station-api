<?php

namespace App\Http\Controllers\Auth;



// Todo código será comentado hasta que tenga la menor idea de lo que estoy haciendo
// Si quiere sacar el comentario, eliminar el /* del principio y * / al final del proyecto

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

// Controlador de autentificación. 
// Servirá para registrar a un usuario a la BD, loggearlo, y cerrar sesión
class AuthController extends Controller
{
    public function register(Request $request) {
        
        $request->validate([
            'names' => 'required|string|max:255',
            'surnames' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'cellphone' => 'nullable|string|max:20',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'unique' => 'Este correo ya está registrado.',
            'confirmed' => 'La confirmación de la contraseña no coincide',
        ]);

        $user = User::create([
            'names' => $request->names,
            'surnames' => $request->surnames,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'cellphone' => $request->cellphone ?? null,
        ]);

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Registro exitoso'
        ], 201);
    }

    public function login(Request $request) {
        // Validación de datos, una vez mas, consultar a BD
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ], [
            'required' => 'El campo :attribute es obligatorio.'
        ]);

        // Revisión de credenciales
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        // Obtener usuario
        $user = Auth::user();

        // Generación de un nuevo token para el login actual
        $user->tokens()->delete();
        $token = $user->createToken('API Token')->plainTextToken;

        // Status de respuesta
        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Login exitoso'
        ]);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada'
        ]);
    }
}
