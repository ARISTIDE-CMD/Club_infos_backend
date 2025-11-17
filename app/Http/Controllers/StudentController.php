<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Services\TypesenseService;
use App\Models\Project;
use Typesense\Client;
class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
public function index(Request $request)
{
    $user = $request->user(); // utilisateur connecté

    // Charger user, projects avec submission + evaluation, et le teacher associé
    $query = Student::with(['user', 'projects.submission.evaluation', 'teacher.user']);

    // Si admin, ne voit que ses étudiants
    if ($user->role === 'admin') {
        $teacher = $user->teacher;
        if (!$teacher) {
            return response()->json([
                'message' => 'Aucun profil enseignant associé à cet utilisateur.'
            ], 403);
        }

        $query->where('teacher_id', $teacher->id);
    }

    // Filtre par classe si demandé
    if ($request->has('class_group')) {
        $query->where('class_group', $request->input('class_group'));
    }

    $students = $query->get();

    // Transformer les résultats pour ajouter le nom du teacher directement
    $students = $students->map(function ($student) {
        return [
            'id' => $student->id,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'student_id' => $student->student_id,
            'class_group' => $student->class_group,
            'created_at' => $student->created_at,
            'user' => $student->user,
            'projects' => $student->projects,
            'teacher_name' => $student->teacher ? $student->teacher->user->name : null,
        ];
    });

    return response()->json([
        'students' => $students,
        'user_role' => $user->role,
        'teacher_id' => $user->role === 'teacher' ? $user->teacher->id : null
    ]);
}



public function indexStudentsInTypesense(Request $request)
{
    // 1️⃣ Récupérer les étudiants comme dans ton index existant
    $user = $request->user();

    $query = Student::with(['user', 'projects.submission.evaluation', 'teacher.user']);

    if ($user->role === 'admin') {
        $teacher = $user->teacher;
        if (!$teacher) {
            return response()->json([
                'message' => 'Aucun profil enseignant associé à cet utilisateur.'
            ], 403);
        }
        $query->where('teacher_id', $teacher->id);
    }

    if ($request->has('class_group')) {
        $query->where('class_group', $request->input('class_group'));
    }

    $students = $query->get();

    // 2️⃣ Préparer Typesense client
    $client = new Client([
        'api_key' => env('TYPESENSE_API_KEY'),
        'nodes' => [
            [
                'host' => env('TYPESENSE_HOST'), // ex: localhost
                'port' => env('TYPESENSE_PORT'), // ex: 8108
                'protocol' => env('TYPESENSE_PROTOCOL', 'http')
            ]
        ],
        'connection_timeout_seconds' => 2
    ]);

    // 3️⃣ Définir le schéma Typesense
    $schema = [
        'name' => 'students',
        'fields' => [
            ['name' => 'id', 'type' => 'string'],
            ['name' => 'first_name', 'type' => 'string'],
            ['name' => 'last_name', 'type' => 'string'],
            ['name' => 'student_id', 'type' => 'string'],
            ['name' => 'class_group', 'type' => 'string'],
            ['name' => 'teacher_name', 'type' => 'string'],
        ],
        'default_sorting_field' => 'student_id'
    ];

    // 4️⃣ Créer la collection si elle n'existe pas
    try {
        $client->collections['students']->retrieve();
    } catch (\Exception $e) {
        $client->collections->create($schema);
    }

    // 5️⃣ Indexer les étudiants
    foreach ($students as $student) {
        $client->collections['students']->documents->upsert([
            'id' => (string)$student->id,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'student_id' => $student->student_id,
            'class_group' => $student->class_group,
            'teacher_name' => $student->teacher ? $student->teacher->user->name : null,
        ]);
    }

    return response()->json([
        'message' => 'Étudiants indexés dans Typesense avec succès.',
        'count' => $students->count()
    ]);
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


}
