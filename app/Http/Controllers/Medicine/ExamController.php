<?php 

namespace App\Http\Controllers\Medicine;

// Controlador para los objetos de exámenes.

use App\Models\Exam;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// ¿Quizás se usen para validación de usuario?
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Hash;

class ExamController extends Controller{

    // Lectura
    public function index(Request $request){

        // Falta asegurarse que el usuario loggeado solo pueda ver
        // exámenes relacionados a él y/o sus pacientes.

        $query = Exam::query();

        if($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        $exams = $query->with('patient')->get();

        return response()->json($exams);
    }

    

    // Crear
    public function store(Request $request){
        $validatedData = $request->validate([
            'patient_id' => 'required|integer|exists:patients,patient_id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'emission_date' => 'required|date',
            'file_url' => 'nullable|url|max:2048',
        ]);

        // Falta lógica de autorización para que solo el usuario
        // asociado al paciente puede modificar este proceso

        $exam = Exam::create($validatedData);

        return response()->json([
            'message' => 'Exámeen creado exitosamente',
            'exam' => $exam
        ], 201);
    }

    // Leer uno

    public function show(string $id){
        $exam = Exam::with('patient')->where('exam_id', $id)->first();

        if(!$exam){
            return response()->json(['message' => 'Examen no encontrado'], 404);
        }

        return response()->json($exam);
    }

    public function update(Request $request, string $id){
        $exam = Exam::where('exam_id', $id)->first();

        if(!$exam) {
            return response()->json(['message' => 'Examen no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'patient_id' => 'required|integer|exists:patients,patient_id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'emission_date' => 'required|date',
            'file_url' => 'nullable|url|max:2048',
        ]);

        $exam->update($validatedData);

        return response()->json([
            'messsage' => 'Examen actualizado',
            'exam' => $exam->fresh()->with('patient')->first()
        ]);
    }

    // Eliminar
    public function destroy(string $id){ 
        $exam = Exam::where('exam_id', $id)->first();

        if(!$exam){
            return response()->json(['message' => 'Examen no encontrado'], 404);
        }

        $exam->delete();

        return response()->json(['message' => 'Examen eliminado'], 204);
    }
}