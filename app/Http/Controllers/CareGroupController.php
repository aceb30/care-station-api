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
use Illuminate\Support\Facades\Storage;


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

    public function update(Request $request, $id) {
        $group = CareGroup::find($id);

        if (!$group) {
            return response()->json(['message' => 'Grupo no Encontrado'], 404);
        }

        if ($group->admin_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado para editar este grupo'], 403);
        }

        // 2. Validación
        $data = $request->validate([
            // El frontend envía 'patient_names', que mapearemos al nombre del grupo
            'patient_names' => 'required|string|max:100', 
            'photo'         => 'nullable|file|mimes:jpeg,png,jpg,heic|max:20480', 
        ]);

        DB::beginTransaction();
        try {
            // 3. Actualizar Nombre
            $group->name = $data['patient_names'];

            // 4. Manejo de la Imagen
            if ($request->hasFile('photo')) {
                // a) Si ya tenía foto (y no es una url externa genérica), borrar la anterior para no llenar el disco
                if ($group->photo_url && str_contains($group->photo_url, '/storage/')) {
                    // Extraemos el path relativo de la URL
                    $oldPath = str_replace(asset('storage/'), '', $group->photo_url);
                    Storage::disk('public')->delete($oldPath);
                }

                // b) Guardar nueva foto en la carpeta 'care_groups' dentro del disco 'public'
                $path = $request->file('photo')->store('care_groups', 'public');
                
                // c) Generar URL completa
                $group->photo_url = asset('storage/' . $path);
            }

            $group->save();
            DB::commit();

            return response()->json([
                'message' => 'Grupo actualizado correctamente',
                'group' => $group
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando grupo: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno al actualizar'], 500);
        }
    }

    public function generateInvitation(Request $request, $id)
    {
        $careGroup = CareGroup::find($id);

        if (!$careGroup) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        // Validar que el usuario actual sea el admin del grupo
        // Nota: Asegúrate que tu tabla care_groups tenga la columna 'admin_id'
        if ($careGroup->admin_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado. Solo el administrador puede crear invitaciones.'], 403);
        }

        // Buscar si ya existe un código vigente para no llenar la BD
        $existingInvite = GroupInvitation::where('care_group_id', $id)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($existingInvite) {
            return response()->json([
                'message' => 'Código existente recuperado',
                'code' => $existingInvite->code,
                'expires_at' => $existingInvite->expires_at
            ]);
        }

        // Generar un código único aleatorio (6 caracteres mayúsculas/números)
        $code = strtoupper(Str::random(6));

        // Asegurarse de que no exista ya (loop simple de seguridad)
        while (GroupInvitation::where('code', $code)->exists()) {
            $code = strtoupper(Str::random(6));
        }

        // Crear la invitación (válida por 48 horas)
        $invitation = GroupInvitation::create([
            'care_group_id' => $id,
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
        // Validar el input
        $request->validate([
            'code' => 'required|string'
        ]);

        $code = strtoupper($request->input('code'));

        // Buscar la invitación
        $invitation = GroupInvitation::where('code', $code)->first();

        if (!$invitation) {
            return response()->json(['message' => 'Código inválido.'], 404);
        }

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

    public function getMembers(string $care_group_id){
        $care_group = CareGroup::find($care_group_id);

        if(!$care_group){
        return response()->json(['message' => 'No se encontró el grupo de cuidados indicado'], 404);
        }

        $members = GroupMember::where('care_group_id', $care_group_id)
                                ->with(['user:user_id,names,surnames'])
                                ->get();

        return response()->json(
            $members->map(fn ($m) => $m->user)
        );
    }
}
