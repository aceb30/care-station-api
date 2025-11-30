<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CareGroupController extends Controller
{
    public function getMyGroups(Request $request)
    {
        $user = $request->user();

        // Obtenemos los grupos del usuario y contamos sus miembros
        $groups = $user->careGroups()->withCount('members')->get();

        $formattedGroups = $groups->map(function ($group) use ($user) {
            return [
                'id' => (string) $group->care_group_id, // Usamos el ID correcto
                'patientName' => $group->name,
                'photoUrl' => $group->photo_url, 
                'membersCount' => $group->members_count,
                'role' => ($group->admin_id == $user->user_id) ? 'admin' : 'member',
            ];
        });

        return response()->json($formattedGroups);
    }
}
