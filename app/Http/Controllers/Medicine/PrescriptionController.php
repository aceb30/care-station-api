<?php

namespace App\Http\Controllers\Medicine;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;

class PrescriptionController extends Controller {
    // Lectura completa
    public function index(Request $request){
        $query = Prescription::query();

        if ($request->has('$patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        $prescriptions = $query->with(['patient', 'medications'])->get();

        return response()->json($prescriptions);
    }

    // Creación
    public function store(Request $request){
        $validatedData = $request->validate([
            'patient_id' => 'required|integer|exists:patients,patient_id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'emission_date' => 'required|date',
            'file_url' => 'nullable|url|max:2048',
            'medications' => 'nullable|array',
            'medications.*' => 'exists:medications,medication_id',
        ]);

        $prescription = Prescription::create($validatedData);

        if($request->has('medications')) {
            $prescription->medications()->sync($request->medications);
        }

        return response()->json([
            'message' => 'Prescripción creada',
            'prescription' => $prescription->load('medications')
        ], 201);

    }

    // Leer una sola instancia
    public function show(string $id){
       $prescription = Prescription::with(['patient', 'medications'])
            ->where('prescription_id', $id)
            ->first();
        
        if(!$prescription) {
            return response()->json(['message' => 'Prescripción no encontrada'], 404);
        }

        return response()->json($prescription);
    }

    // Actualizar
    public function update(Request $request, string $id){
        $prescription = Prescription::where('prescription_id', $id)->first();

        if(!$prescription){
            return response()->json(['message' => 'Prescripción no encontrada'], 404);
        }

        $validatedData = $request->validate([
            'patient_id' => 'required|integer|exists:patients,patient_id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'emission_date' => 'required|date',
            'file_url' => 'nullable|url|max:2048',
            'medications' => 'nullable|array',
            'medications.*' => 'exists:medications,medication_id',
        ]);

        $prescription->update($validatedData);

        if($request->has('medications')){
            $prescription->medications()->sync($request->medications);
        }

        return response()->json([
            'message' => 'Prescripción actualizada',
            'prescription' => $prescription->fresh()->load(['patient', 'medications'])
        ]);
    }

    public function destroy(string $id){
        $prescription = Prescription::where('prescription_id', $id)->first();

        if(!$prescription) {
            return response()->json(['message' => 'Prescripción no encontrada'], 404);
        }

        $prescription->medications()->detach();
        $prescription->delete();

        return response()->json(['message' => 'Prescripción eliminada'], 204);
    }
}