<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CareGroup;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;

class CareGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function getMyGroups(Request $request)
    {
        $user = $request->user();

        $groups = $user->careGroups()->withCount('members')->get();

        $formattedGroups = $groups->map(function ($group) use ($user) {
            return [
                'id' => (string) $group->care_group_id,
                'patientName' => $group->name,
                'photoUrl' => $group->photo_url,
                'membersCount' => $group->members_count,
                'role' => ($group->admin_id == $user->user_id) ? 'admin' : 'member',
            ];
        });

        return response()->json($formattedGroups);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'group_name'          => 'required|string|max:100',
            'photo_url'           => 'nullable|string',

            'patient_names'      => 'required|string|max:100',
            'patient_surnames'   => 'nullable|string|max:100',
            'patient_cellphone' => 'nullable|string|max:20',
            'patient_telephone' => 'nullable|string|max:20',
            'patient_address'   => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $group = CareGroup::create([
                'name'     => $data['group_name'],
                'photo_url' => $data['photo_url'] ?? null,
                'admin_id' => $user->user_id,
            ]);

            $patient = Patient::create([
                'care_group_id' => $group->care_group_id,
                'names'         => $data['patient_names'],
                'surnames'      => $data['patient_surnames'] ?? null,
                'cellphone'     => $data['patient_cellphone'] ?? null,
                'telephone'     => $data['patient_telephone'] ?? null,
                'address'       => $data['patient_address'] ?? null,
            ]);

            $group->members()->attach($user->user_id);

            DB::commit();

            return response()->json([
                'message' => 'Grupo creado correctamente',
                'group'   => $group,
                'patient' => $patient,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear el grupo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
