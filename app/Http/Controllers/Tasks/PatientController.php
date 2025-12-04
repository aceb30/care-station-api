<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
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
}