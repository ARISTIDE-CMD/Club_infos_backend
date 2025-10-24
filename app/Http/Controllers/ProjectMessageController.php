<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectMessage;
use App\Models\Project;

class ProjectMessageController extends Controller
{
  public function index()
{
    $messages = ProjectMessage::with(['user', 'project.students.user'])
        ->orderBy('created_at', 'asc')
        ->get();

    return response()->json([
        'messages' => $messages->map(function ($msg) {
            return [
                'project_id' => $msg->project_id,
                'message' => $msg->message,
                'user_name' => $msg->user?->name,
                'created_at' => $msg->created_at,
            ];
        }),
    'students' => $messages
    ->pluck('project.students')
    ->flatten()
    ->unique(fn ($student) => $student->id . '-' . $student->pivot->project_id)
    ->map(function ($student) {
        return [
            'student_id' => $student->id,
            'user_name' => $student->user?->name,
            'project_id' => $student->pivot->project_id, // ✅ ici
        ];
    })
    ->values(),

    ]);
}


public function show($projectId)
{
    // Récupération des messages avec nom de l’expéditeur
    $messages = ProjectMessage::where('project_id', $projectId)
        ->join('users', 'project_messages.user_id', '=', 'users.id')
        ->orderBy('project_messages.created_at', 'asc')
        ->get([
            'project_messages.project_id',
            'project_messages.message',
            'users.name as user_name',
            'project_messages.created_at',
        ]);

    // Récupération des étudiants appartenant au projet
    $students = \App\Models\Student::whereHas('projects', function ($q) use ($projectId) {
            $q->where('projects.id', $projectId);
        })
        ->join('users', 'students.user_id', '=', 'users.id')
        ->get([
            'students.id as student_id',
            'users.name as user_name',
        ]);

    return response()->json([
        'messages' => $messages,
        'students' => $students,
    ]);
}



  public function store(Request $request)
{
    $user = auth()->user();
    if (!$user) {
        return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
    }

    $data = $request->validate([
        'project_id' => 'required|exists:projects,id',
        'message' => 'required|string|max:1000',
    ]);

    $projectId = $data['project_id'];

    // Si l'utilisateur n'est pas admin, vérifier qu'il fait partie des étudiants du projet
    if ($user->role !== 'admin') {
        $isMember = Project::where('id', $projectId)
            ->whereHas('students', function ($q) use ($user) {
                // On suppose ici que la table students a une colonne user_id liant au user
                $q->where('user_id', $user->id);
            })
            ->exists();

        if (! $isMember) {
            return response()->json(['message' => 'Accès refusé : vous n\'êtes pas membre de ce projet.'], 403);
        }
    }

    // création du message
    $message = ProjectMessage::create([
        'project_id' => $projectId,
        'user_id' => $user->id,
        'message' => $data['message'],
    ]);

    // Retourner la structure que le front attend
    return response()->json([
        'project_id' => $message->project_id,
        'message' => $message->message,
        'user_name' => $user->name,
        'created_at' => $message->created_at->toIso8601String(),
    ], 201);
}
}

