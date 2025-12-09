<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Task;
use App\Models\CareGroup;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller{
  // RESTFUL STYLE ENDPOINTS

  // EDIT TO INCLUDE MORE INFORMATION ABOUT A SPECIFIC TASK (!!!!!)
  public function show(string $id){
    $task = Task::with(['assignedUsers:user_id,names,surnames,email'])->find($id);

    if(!$task){
      return response()->json(['message' => 'Tarea no encontrada'], 404);
    }

    return response()->json($task);
  }

  // Create a single task
 // Create a single task
  public function store(Request $request){
    $validated = $request->validate([
      'care_group_id' => 'required|exists:care_groups,care_group_id',
      'title' => 'required|string|max:255',
      'description' => 'nullable|string',
      'frequency' => 'required|string',
      'category' => 'nullable|string',
      'begin_time' => 'required|date',
      'end_time' => 'nullable|date|after_or_equal:begin_time',
      // 1. Validate the user exists
      'assigned_to' => 'nullable|exists:users,user_id', 
    ], [
      'required' => 'El campo :attribute es obligatorio.',
    ]);

    $task = Task::create([
      'care_group_id' => $validated['care_group_id'],
      'title' => $validated['title'],
      'description' => $validated['description'] ?? null,
      'frequency' => $validated['frequency'],
      'category' => $validated['category'] ?? null,
      'begin_time' => $validated['begin_time'],
      'end_time' => $validated['end_time'] ?? null,
      'done' => false,
    ]);

    // 2. THE FIX: Manually save the relationship
    if (!empty($validated['assigned_to'])) {
        $task->assignedUsers()->attach($validated['assigned_to']);
    }

    return response()->json([
      'task' => $task,
      'message' => "Tarea creada con éxito"
    ], 201);
  }

  // Delete single task
  public function destroy(string $id){
    $task = Task::find($id);

    if(!$task){
      return response()->json(['message' => 'Tarea no encontrada'], 404);
    }

    $task->delete();

    return response()->json(['message' => "Tarea eliminada"], 202);
  }

  // Update single task
  public function update(Request $request, string $id){
    $task = Task::find($id);

    if(!$task){
      return response()->json(['message' => "Tarea no encontrada"], 404);
    }

    $validated = $request->validate([
      'title' => 'sometimes|nullable|string|max:255',
      'description' => 'sometimes|nullable|string',
      'frequency' => 'sometimes|nullable|string',
      'category' => 'sometimes|nullable|string',
      'begin_time' => 'sometimes|nullable|date',
      'end_time' => 'sometimes|nullable|date|after_or_equal:begin_time',
      'done' => 'sometimes|boolean',
      // 1. Validate the user
      'assigned_to' => 'sometimes|nullable|exists:users,user_id',
    ]);

    // Update the basic fields
    $task->update(collect($validated)->except(['task_id', 'assigned_to'])->toArray());

    // 2. THE FIX: Update the relationship
    if (array_key_exists('assigned_to', $validated)) {
        if ($validated['assigned_to']) {
            // If ID sent, replace assignments with this user
            $task->assignedUsers()->sync([$validated['assigned_to']]); 
        } else {
            // If null sent, remove all assignments
            $task->assignedUsers()->detach();
        }
    }

    return response()->json([
      'message' => "Tarea actualizada correctamente",
      'task' => $task->load('assignedUsers') // Reload so response shows the new user
    ], 202);
  }
  

  // CUSTOM ENDPOINTS

  // Read all tasks assigned to a care group
  public function indexByGroup(string $care_group_id){
    $care_group = CareGroup::find($care_group_id);

    if(!$care_group){
      return response()->json(['message' => 'No se encontró el grupo de cuidados indicado'], 404);
    }

    $tasks = Task::where('care_group_id', $care_group_id)
                  ->with(['assignedUsers:user_id,names,surnames']) 
                  ->get();

    $tasks->transform(function ($task) {
      // Create a string like "Ana Perez" or "Ana Perez, Jorge Silva"
      $names = $task->assignedUsers->map(function ($user) {
          return $user->names . ' ' . $user->surnames;
      })->join(', ');

      $task->assigned_to = $names ?: 'Sin asignar';

      unset($task->assignedUsers); 

      return $task;
    });

    return response()->json($tasks);
  }

  // All upcoming tasks assigned to a care group
  public function upcomingByGroup(string $care_group_id){
    $care_group = CareGroup::find($care_group_id);

    if(!$care_group){
      return response()->json(['message' => 'No se encontró el grupo de cuidados indicado'], 404);
    }

    $tasks = Task::where('care_group_id', $care_group_id)
                  ->where('begin_time', '>=', today('America/Santiago')->utc())
                  ->with(['assignedUsers:user_id,names,surnames']) 
                  ->get();

    $tasks->transform(function ($task) {
      // Create a string like "Ana Perez" or "Ana Perez, Jorge Silva"
      $names = $task->assignedUsers->map(function ($user) {
          return $user->names . ' ' . $user->surnames;
      })->join(', ');

      $task->assigned_to = $names ?: 'Sin asignar';

      unset($task->assignedUsers); 

      return $task;
    });

    return response()->json($tasks);
  }
}