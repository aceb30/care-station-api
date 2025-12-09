<?php

namespace App\Http\Controllers\Medicine;

use App\Models\Medication;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// ¿Quizás se usen para validación de usuario?
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Hash;

class MedicationController extends Controller {

    // Lectura completa
    public function index(Request $request){
        $query = Medication::query();

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        $medications = $query->with('patient')->get();

        return response()->json($medications);
    }

    // Creación
    public function store(Request $request){
        $validatedData = $request->validate([
            'patient_id' => 'required|integer|exists:patients,patient_id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $medication = Medication::create($validatedData);

        return response()->json([
            'message' => 'Medicamento registrado',
            'medication' => $medication
        ], 201);
    }

    // Mostrar uno solo
    public function show(string $id){
        $medication = Medication::with('patient')->where('medication_id', $id)->first();

        if(!$medication){
            return response()->json(['message' => 'Medicamento no encontrado'], 404);
        }

        return response()->json($medication);
    }

    public function update(Request $request, string $id){
        $medication = Medication::where('medication_id', $id)->first();

        if(!$medication){
            return response()->json(['message' => 'Medicamento no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $medication->update($validatedData);

        return response()->json([
            'message' => 'Medicamento actualizado',
            'medication' => $medication->fresh()->with('patient')->first()
        ]);
    }
    

    // Eliminar
    public function destroy(string $id){
        $medication = Medication::where('medication_id', $id)->first();

        if (!$medication){
            return response()->json(['message' => 'Medicamento no encontrado'], 404);
        }

        $medication->delete();

        return response()->json(['message' => 'Medicamento eliminado'], 204);
    }
}