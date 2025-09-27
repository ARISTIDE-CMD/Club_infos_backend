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
        $query = Student::with('user');

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
        // Find the student with their user and projects data
        $student = Student::with(['user', 'projects'])->find($id);

        // Return a 404 response if the student is not found
        if (!$student) {
            return response()->json(['message' => 'Étudiant non trouvéeeee'], 404);
        }

        // Return the student data with a 200 success code
        return response()->json(['student' => $student], 200);
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
