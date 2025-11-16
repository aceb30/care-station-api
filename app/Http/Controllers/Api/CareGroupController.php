<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CareGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreCareGroupRequest;

class CareGroupController extends Controller
{
    /**
     * Muestra los grupos de cuidado del usuario autenticado.
     */
    public function index()
    {
        $user = Auth::user();
        return response()->json($user->careGroups()->with('patient')->get());
    }

    /**
     * Crea un nuevo grupo de cuidado.
     */
    public function store(StoreCareGroupRequest $request)
    {
        $user = Auth::user();

        $careGroup = \DB::transaction(function () use ($request, $user) {
            $group = CareGroup::create([
                'name' => $request->name,
                'admin_id' => $user->user_id,
            ]);

            $group->members()->attach($user->user_id);

            $group->patient()->create([
                'names' => $request->patient_name,
            ]);

            return $group;
        });

        return response()->json($careGroup->load('patient', 'members'), 201);
    }


    public function show(CareGroup $careGroup)
    {
        if (Auth::user()->cannot('view', $careGroup)) {
            abort(403, 'Unauthorized action.');
        }

        $careGroup->load('patient', 'members');
        
        return new CareGroupResource($careGroup);
    }

    // métodos update() y destroy() po hacer
}