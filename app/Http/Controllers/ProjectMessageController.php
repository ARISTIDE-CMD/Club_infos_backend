<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectMessage;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class ProjectMessageController extends Controller
{
    /**
     * Affiche tous les messages des projets liés à l'admin connecté
     */
   public function index()
{
    $user = Auth::user();

    // 🔹 Déterminer les projets accessibles selon le rôle
    if ($user->role === 'admin') {
        // Récupère les IDs des étudiants de ce teacher
        $studentIds = $user->teacher->students()->pluck('id');

        // Récupère les projets auxquels ces étudiants participent
        $projectIds = Project::whereHas('students', function ($q) use ($studentIds) {
            $q->whereIn('students.id', $studentIds);
        })->pluck('id');
    } else {
        // 🔹 Superadmin ou autre rôle → accès à tous les projets
        $projectIds = Project::pluck('id');
    }

    // 🔹 Charger les messages avec relations
    $messages = ProjectMessage::with(['user', 'project.students.user'])
        ->whereIn('project_id', $projectIds)
        ->orderBy('created_at', 'asc')
        ->get();

    // 🔹 Réponse structurée
    return response()->json([
        'messages' => $messages->map(fn($msg) => [
            'project_id' => $msg->project_id,
            'project_name' => $msg->project?->title, // ✅ Ajout du nom du projet
            'message' => $msg->message,
            'user_name' => $msg->user?->name,
            'user_role' => $msg->user?->role,
            'created_at' => $msg->created_at,
        ]),
        'students' => $messages
            ->pluck('project.students')
            ->flatten()
            ->unique(fn($student) => $student->id . '-' . $student->pivot->project_id)
            ->map(fn($student) => [
                'student_id' => $student->id,
                'user_name' => $student->user?->name,
                'project_id' => $student->pivot->project_id,
                'project_name' => $student->pivot->project?->title ?? // sécurité
                    $student->projects->firstWhere('id', $student->pivot->project_id)?->title,
            ])
            ->values(),
    ]);
}


    /**
     * Affiche les messages d'un projet spécifique
     */
    public function show($projectId)
    {
        $user = Auth::user();

        // Vérifier que l'admin est bien responsable de ce projet
        if ($user->role === 'admin') {
            $studentIds = $user->teacher->students()->pluck('id');
            $isResponsible = Project::where('id', $projectId)
                ->whereHas('students', fn($q) => $q->whereIn('students.id', $studentIds))
                ->exists();

            if (!$isResponsible) {
                return response()->json(['message' => 'Accès refusé.'], 403);
            }
        }

        $messages = ProjectMessage::where('project_id', $projectId)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        $students = \App\Models\Student::whereHas('projects', fn($q) => $q->where('projects.id', $projectId))
            ->with('user')
            ->get();

        return response()->json([
            'messages' => $messages,
            'students' => $students,
        ]);
    }

    /**
     * Création d'un message pour un projet
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'message' => 'required|string|max:1000',
        ]);

        $projectId = $data['project_id'];

        // Vérifications selon le rôle
        if ($user->role === 'admin') {
            $studentIds = $user->teacher->students()->pluck('id');
            $isResponsible = Project::where('id', $projectId)
                ->whereHas('students', fn($q) => $q->whereIn('students.id', $studentIds))
                ->exists();

            if (!$isResponsible) {
                return response()->json(['message' => 'Accès refusé.'], 403);
            }
        }

        if ($user->role === 'student') {
            $isMember = Project::where('id', $projectId)
                ->whereHas('students', fn($q) => $q->where('user_id', $user->id))
                ->exists();

            if (!$isMember) {
                return response()->json(['message' => 'Accès refusé.'], 403);
            }
        }

        $message = ProjectMessage::create([
            'project_id' => $projectId,
            'user_id' => $user->id,  // ✅ ici, user_id existe dans project_messages
            'message' => $data['message'],
        ]);

        return response()->json([
            'project_id' => $message->project_id,
            'message' => $message->message,
            'user_name' => $user->name,
            'created_at' => $message->created_at->toIso8601String(),
        ], 201);
    }
}
