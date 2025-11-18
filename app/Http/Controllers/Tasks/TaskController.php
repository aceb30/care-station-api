<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Task;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller{
  // All tasks assigned to a care group
  public function readTasks(Request $request){
    $validated = $request->validate([
      'care_group_id' => 'required|integer'
    ], [
      'required' => 'El campo :attribute es obligatorio.',
    ]);

    $tasks = Task::where('care_group_id', $validated['care_group_id'])->get();

    return response()->json($tasks);
  }

   // All upcoming tasks assigned to a care group
  public function readUpcomingTasks(Request $request){
    $validated = $request->validate([
      'care_group_id' => 'required|integer'
    ], [
      'required' => 'El campo :attribute es obligatorio.',
    ]);

    $tasks = Task::where('care_group_id', $validated['care_group_id'])
                  ->where('begin_time', '>=', now('America/Santiago')->startOfDay()->setTimezone('UTC'))
                  ->get();

    return response()->json($tasks);
  }

  // Create single task
  public function createTask(Request $request){
    $validated = $request->validate([
      'care_group_id' => 'required|integer',
      'title' => 'required|string|max:255',
      'description' => 'nullable|string',
      'frequency' => 'required|string',
      'category' => 'nullable|string',
      'begin_time' => 'required|date',
      'end_date' => 'nullable|date|after_or_equal:start_date'
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

    return response()->json([
      'task' => $task,
      'message' => "Tarea creada con éxito"
    ], 201);
  }

  // Delete single task
  public function deleteTask(Request $request){
    $validated = $request->validate([
      'task_id' => 'required|integer',
    ], [
      'required' => 'El campo :attribute es obligatorio.',      
    ]);

    if(Task::where('task_id', $validated['task_id'])->exists()){
      $task = Task::find($validated['task_id']);
      $task->delete();

      return response()->json([
        'message' => "Tarea eliminada"
      ], 202);
    }
    else {
      return response()->json([
        'message' => "Tarea no encontrada"
      ], 404);
    }
  }

  // Update single task
  public function updateTask(Request $request){
    $validated = $request->validate([
      'task_id' => 'required|integer',
      'title' => 'sometimes|nullable|string|max:255',
      'description' => 'sometimes|nullable|string',
      'frequency' => 'sometimes|nullable|string',
      'category' => 'sometimes|nullable|string',
      'begin_time' => 'sometimes|nullable|date',
      'end_time' => 'sometimes|nullable|date|after_or_equal:start_date',
      'done' => 'sometimes|nullable|boolean'
    ], [
      'required' => 'El campo :attribute es obligatorio.', 
      'integer' => 'El campo :attribute debe ser un número entero.',
      'string' => 'El campo :attribute debe ser texto válido.',
      'date' => 'El campo :attribute debe ser una fecha válida.',
      'after_or_equal' => 'La fecha de término debe ser posterior o igual a la fecha de inicio.',
      'boolean' => 'El campo :attribute debe ser verdadero o falso.'
    ]);

    $task = Task::find($validated['task_id']);

    if(!$task){
      return response()->json([
        'message' => "Tarea no encontrada"
      ], 404);
    }

    $task->update(collect($validated)->except('task_id')->toArray());

    return response()->json([
      'message' => "Tarea actualizada correctamente",
      'task' => $task
    ], 202);
  }

}