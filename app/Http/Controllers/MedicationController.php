<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicationController extends Controller
{
    /**
     * Obtener medicamentos de un paciente específico.
     */
    public function index(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|integer|exists:patients,patient_id'
        ]);

        $medications = Medication::where('patient_id', $request->patient_id)->get();
        return response()->json($medications);
    }

    /**
     * Crear un nuevo medicamento.
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|integer|exists:patients,patient_id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $medication = Medication::create($request->all());

        return response()->json([
            'message' => 'Medicamento agregado exitosamente',
            'medication' => $medication
        ], 201);
    }

    /**
     * Obtener un medicamento específico.
     */
    public function show($id)
    {
        $medication = Medication::find($id);
        if (!$medication) {
            return response()->json(['message' => 'Medicamento no encontrado'], 404);
        }
        return response()->json($medication);
    }

    /**
     * Actualizar medicamento.
     */
    public function update(Request $request, $id)
    {
        $medication = Medication::find($id);
        if (!$medication) {
            return response()->json(['message' => 'Medicamento no encontrado'], 404);
        }

        $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
        ]);

        $medication->update($request->all());

        return response()->json([
            'message' => 'Medicamento actualizado',
            'medication' => $medication
        ]);
    }

    /**
     * Eliminar medicamento.
     */
    public function destroy($id)
    {
        $medication = Medication::find($id);
        if (!$medication) {
            return response()->json(['message' => 'Medicamento no encontrado'], 404);
        }

        $medication->delete();
        return response()->json(['message' => 'Medicamento eliminado']);
    }
}