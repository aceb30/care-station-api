<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CareGroup;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\GroupInvitation;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\GroupMember;


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
            Log::info('Grupo creado correctamente', $group->toArray());

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear el grupo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function generateInvitationCode(Request $request, $careGroupId)
    {
        $careGroup = CareGroup::findOrFail($careGroupId);

        // Validar que el usuario actual sea el admin del grupo
        if ($careGroup->admin_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado. Solo el administrador puede crear invitaciones.'], 403);
        }

        // Generar un código único aleatorio 
        $code = strtoupper(Str::random(6));

        // Asegurarse de que no exista ya 
        while (GroupInvitation::where('code', $code)->exists()) {
            $code = strtoupper(Str::random(6));
        }

        // Crear la invitación (válida por 48 horas)
        $invitation = GroupInvitation::create([
            'care_group_id' => $careGroupId,
            'code' => $code,
            'expires_at' => Carbon::now()->addHours(48),
        ]);

        return response()->json([
            'message' => 'Código generado exitosamente',
            'code' => $invitation->code,
            'expires_at' => $invitation->expires_at
        ]);
    }

    public function joinByCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|exists:group_invitations,code'
        ]);

        $code = $request->input('code');

        // Buscar la invitación
        $invitation = GroupInvitation::where('code', $code)->first();

        // Verificar si ha expirado
        if (Carbon::now()->greaterThan($invitation->expires_at)) {
            return response()->json(['message' => 'El código de invitación ha expirado.'], 400);
        }

        $user = Auth::user();
        $groupId = $invitation->care_group_id;

        // Verificar si el usuario ya es miembro de ese grupo
        $isMember = GroupMember::where('user_id', $user->user_id)
                            ->where('care_group_id', $groupId)
                            ->exists();

        if ($isMember) {
            return response()->json(['message' => 'Ya eres miembro de este grupo.'], 409);
        }

        // Agregar al usuario al grupo
        GroupMember::create([
            'user_id' => $user->user_id,
            'care_group_id' => $groupId
        ]);

        return response()->json([
            'message' => 'Te has unido al grupo de cuidado exitosamente.',
            'care_group_id' => $groupId
        ]);
    }
}
