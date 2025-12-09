<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\CareGroup;
use Illuminate\Support\Facades\Log;


class PatientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // GET /api/patients
    public function index(Request $request)
    {
        return response()->json(Patient::all(), 200);
    }

    // POST /api/patients
    public function store(Request $request)
    {
        $data = $request->validate([
            'care_group_id' => 'required|integer|exists:care_groups,care_group_id',
            'names'         => 'required|string|max:100',
            'surnames'      => 'nullable|string|max:100',
            'cellphone'     => 'nullable|string|max:20',
            'telephone'     => 'nullable|string|max:20',
            'address'       => 'nullable|string',
        ]);

        $patient = Patient::create($data);

        Log::info('Imprimiendo paciente guardado ', $patient->toArray());
        return response()->json($patient, 201);
    }

    // GET /api/patients/{patient}
    public function show(Patient $patient)
    {
        return response()->json($patient, 200);
    }

    // PATCH/PUT /api/patients/{patient}
    public function update(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'care_group_id' => 'sometimes|required|integer|exists:care_groups,care_group_id',
            'names'         => 'sometimes|required|string|max:100',
            'surnames'      => 'nullable|string|max:100',
            'cellphone'     => 'nullable|string|max:20',
            'telephone'     => 'nullable|string|max:20',
            'address'       => 'nullable|string',
        ]);

        $patient->update($data);

        return response()->json($patient, 200);
    }

    // DELETE /api/patients/{patient}
    public function destroy(Patient $patient)
    {
        $patient->delete();
        return response()->json(null, 204);
    }

    public function showByGroup(string $care_group_id){
        $care_group = CareGroup::find($care_group_id);

        if(!$care_group){
            return response()->json(['message' => 'No se encontró el grupo de cuidados indicado'], 404);
        }

        $patient = Patient::where('care_group_id', $care_group_id)->first();

        if(!$patient){
            return response()->json(['message' => 'Paciente no encontrado'], 404);
        }

        return response()->json($patient);
    }
}