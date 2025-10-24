<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Project;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Student::with(['user','projects.submission.evaluation' ]);

        if ($request->has('class_group')) {
            $query->where('class_group', $request->input('class_group'));
        }

        $students = $query->get();

        return response()->json(['students' => $students]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
 public function show($id)
{
    $student = Student::with([
        'user',
        'projects.submission.evaluation' // ✅ relations uniques
    ])->find($id);

    if (!$student) {
        return response()->json(['message' => 'Étudiant non trouvé'], 404);
    }

    // Comme chaque projet n’a qu’une seule soumission,
    // on peut récupérer les soumissions sous forme d’objets
    $submissions = $student->projects->map(function ($project) {
        return [
            'project' => $project,
            'submission' => $project->submission,
            'evaluation' => $project->submission?->evaluation
        ];
    });

    return response()->json([
        'student' => $student,
        'submissions' => $submissions,
    ], 200);
}


// public function show($id)
// {
//     // Rechercher l'étudiant par student_id
//     $student = Student::with(['user', 'assignments.project'])
//         ->where('student_id', $id)
//         ->first();

//     if (!$student) {
//         return response()->json(['message' => 'Étudiant non trouvé'], 404);
//     }

//     return response()->json(['student' => $student], 200);
// }



//     public function show($id)
// {
//     $student = Student::with(['user', 'projects'])->where('student_id', $id)->first();

//     if (!$student) {
//         return response()->json(['message' => 'Étudiant non trouvé'], 404);
//     }

//     return response()->json(['student' => $student], 200);
// }

}
