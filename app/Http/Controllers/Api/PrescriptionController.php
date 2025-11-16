<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrescriptionController extends Controller
{
    public function store(Request $request, Patient $patient)
    {
        // Aquí deberías añadir autorización para ver si el usuario puede añadir
        // prescripciones a este paciente.

        $request->validate([
            'name' => 'required|string|max:255',
            'emission_date' => 'nullable|date',
            'file' => 'required|file|mimes:pdf,jpg,png,jpeg|max:2048', // 2MB max
        ]);

        // 1. Subir el archivo a MinIO
        // El path será algo como 'prescriptions/archivo_aleatorio.pdf'
        $path = $request->file('file')->store('prescriptions', 's3');

        // 2. Crear el registro en la base de datos
        $prescription = $patient->prescriptions()->create([
            'name' => $request->name,
            'emission_date' => $request->emission_date,
            'description' => $request->description,
            'file_url' => $path, // Guardamos el path relativo
        ]);

        // Para devolver la URL completa, puedes hacer esto:
        $prescription->file_url = Storage::disk('s3')->url($path);

        return response()->json($prescription, 201);
    }
}