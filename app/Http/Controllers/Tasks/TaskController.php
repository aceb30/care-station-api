<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Task;
use App\Models\CareGroup;
use App\Models\TaskAssignment;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TaskController extends Controller{
  // RESTFUL STYLE ENDPOINTS
  public function show(string $id){
    $task = Task::with(['assignedUsers:user_id,names,surnames,email'])->find($id);

    if(!$task){
      return response()->json(['message' => 'Tarea no encontrada'], 404);
    }

    return response()->json($task);
  }

  // Create one or multiple tasks based on the frequency
  public function store(Request $request)
  {
    $validated = $request->validate([
      'care_group_id' => 'required|exists:care_groups,care_group_id',
      'title' => 'required|string|max:255',
      'description' => 'nullable|string',
      'frequency' => 'required|integer|between:0,3',
      'category' => 'nullable|string',

      // begin_time = startDate + startTime
      'begin_time' => 'required|date',

      // end_time = startDate + endTime (time only)
      'end_time' => 'required|date|after_or_equal:begin_time',

      // loop_end_date = only date, no time required
      'loop_end_date' => 'nullable|date|after_or_equal:begin_time',

      'assigned_to' => 'nullable|exists:users,user_id',
    ]);

    // Extract original timestamps
    $start = Carbon::parse($validated['begin_time']);
    $loopEnd = isset($validated['loop_end_date'])
      ? Carbon::parse($validated['loop_end_date'])
      : $start->copy();

    $frequency = intval($validated['frequency']);

    // Extract the hours/minutes from the original times
    $startHour = $start->hour;
    $startMinute = $start->minute;

    $end = Carbon::parse($validated['end_time']);
    $endHour = $end->hour;
    $endMinute = $end->minute;

    $tasksCreated = [];

    // Frequency increments
    $intervals = [
      0 => null,
      1 => '1 day',
      2 => '1 week',
      3 => '1 month',
    ];

    // Set current day pointer for loop
    $current = $start->copy()->startOfDay();

    while ($current->lte($loopEnd)) {
      // Build begin_time for this iteration (date = current, time from original)
      $beginForDay = $current->copy()->setTime($startHour, $startMinute);

      // Build end_time for this iteration (same date but end HH:mm)
      $endForDay = $current->copy()->setTime($endHour, $endMinute);

      // Create the task
      $task = Task::create([
          'care_group_id' => $validated['care_group_id'],
          'title' => $validated['title'],
          'description' => $validated['description'] ?? null,
          'frequency' => $frequency,
          'category' => $validated['category'] ?? null,
          'begin_time' => $beginForDay,
          'end_time' => $endForDay,
          'done' => false,
      ]);

      if (!empty($validated['assigned_to'])) {
          TaskAssignment::create([
              'task_id' => $task->task_id,
              'user_id' => $validated['assigned_to']
          ]);
      }

      $tasksCreated[] = $task;

      if ($frequency === 0) break; // only once

      $current->add($intervals[$frequency]);
    }

    return response()->json([
        'tasks' => $tasksCreated,
        'count' => count($tasksCreated),
        'message' => "Tareas creadas con éxito"
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
      'done' => 'sometimes|boolean'
    ], [
      'required' => 'El campo :attribute es obligatorio.', 
      'integer' => 'El campo :attribute debe ser un número entero.',
      'string' => 'El campo :attribute debe ser texto válido.',
      'date' => 'El campo :attribute debe ser una fecha válida.',
      'after_or_equal' => 'La fecha de término debe ser posterior o igual a la fecha de inicio.',
      'boolean' => 'El campo :attribute debe ser verdadero o falso.'
    ]);

    $task->update(collect($validated)->except('task_id')->toArray());

    return response()->json([
      'message' => "Tarea actualizada correctamente",
      'task' => $task
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