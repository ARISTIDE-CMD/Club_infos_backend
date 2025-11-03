<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdminCategory;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use App\Models\Project;
use App\Models\Submission;
use App\Models\Evaluation;
use App\Models\Student;

class SuperAdminController extends Controller
{
    // public function __construct()
    // {
    //     // Vérifie que l'utilisateur est connecté via Sanctum
    //     $this->middleware('auth:sanctum');
    // }

    /**
     * Vérifie que l'utilisateur est bien superadmin.
     * Retourne une réponse JSON si non autorisé.
     */
    private function ensureSuperAdmin($user)
    {
        if (!$user || $user->role !== 'superadmin') {
            return response()->json([
                'message' => 'Accès refusé. Seul le super administrateur peut effectuer cette action.'
            ], 403);
        }

        return null; // ✅ sinon tout va bien
    }

    /**
     * Dashboard : statistiques globales.
     */
    public function dashboard(Request $request)
{
    // $user = $request->user();

    // // 🔒 Vérification du rôle
    // if (!$user || $user->role !== 'superadmin') {
    //     return response()->json(['message' => 'Accès refusé.'], 403);
    // }

    // =========================
    // 1️⃣ Statistiques globales
    // =========================
    $totalStudents   = User::where('role', 'student')->count();
    // $totalTeachers   = User::where('role', 'teacher')->count();
    $totalAdmins     = User::where('role', 'admin')->count();
    $totalProjects   = Project::count();
    $totalSubmissions = Submission::count();
    $totalEvaluations = Evaluation::count();

    // =========================
    // 2️⃣ Détails étudiants
    // =========================
    $students = Student::with([
        'user:id,name,email',
        'projects.submission.evaluation'
    ])->get();

    // Comptes dynamiques
    $studentsWithProjects = $students->filter(fn($s) => $s->projects->count() > 0)->count();
    $studentsWithoutProjects = $totalStudents - $studentsWithProjects;

    // =========================
    // 3️⃣ Statistiques sur les projets
    // =========================
    $projects = Project::with(['teacher.user', 'submission.evaluation'])->get();

    $projectsWithSubmissions = $projects->filter(fn($p) => $p->submission !== null)->count();
    $projectsWithoutSubmissions = $totalProjects - $projectsWithSubmissions;

    // Calcul de la moyenne générale des évaluations
   $topStudents = Student::withCount('projects')
    ->orderBy('projects_count', 'desc')
    ->take(5)
    ->get();


    // =========================
    // 4️⃣ Classement (Top)
    // =========================
    $topStudents = Student::with(['user', 'projects.submission.evaluation'])
        ->get()
        ->map(function ($student) {
            $avgScore = $student->projects
                ->flatMap(fn($p) => [$p->submission?->evaluation?->score])
                ->filter()
                ->avg();

            return [
                'name' => $student->user->name ?? 'Inconnu',
                'average_score' => round($avgScore, 2),
                'projects_count' => $student->projects->count(),
            ];
        })
        ->sortByDesc('average_score')
        ->take(5)
        ->values();

    // =========================
    // 5️⃣ Résumé par enseignants
    // =========================
    $teachers = Teacher::with(['user', 'projects'])->get()
        ->map(function ($teacher) {
            return [
                'name' => $teacher->user->name ?? 'Inconnu',
                'projects_count' => $teacher->projects->count(),
                'avg_project_evaluation' => $teacher->projects
                    ->flatMap(fn($p) => [$p->submission?->evaluation?->score])
                    ->filter()
                    ->avg(),
            ];
        });

    // =========================
    // 6️⃣ Structure de réponse
    // =========================
    return response()->json([
        'stats_globales' => [
            'total_students'   => $totalStudents,
            // 'total_teachers'   => $totalTeachers,
            'total_admins'     => $totalAdmins,
            'total_projects'   => $totalProjects,
            'total_submissions' => $totalSubmissions,
            'total_evaluations' => $totalEvaluations,
            'average_score'    => round($averageScore, 2),
        ],
        'stats_etudiants' => [
            'with_projects'    => $studentsWithProjects,
            'without_projects' => $studentsWithoutProjects,
            'top_students'     => $topStudents,
        ],
        'stats_projets' => [
            'with_submissions'    => $projectsWithSubmissions,
            'without_submissions' => $projectsWithoutSubmissions,
        ],
        'stats_teachers' => $teachers,
    ]);
}


    /**
     * Créer une catégorie d'admin.
     */
    public function storeCategory(Request $request)
    {
      $user = $request->user();

if (!$user || $user->role !== 'superadmin') {
    return response()->json(['message' => 'Accès refusé.'], 403);
}

        $request->validate([
            'name' => 'required|string|unique:admin_categories,name',
        ]);

        $category = AdminCategory::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'message'  => 'Catégorie créée avec succès',
            'category' => $category
        ]);
    }

    /**
     * Lister toutes les catégories d'admin.
     */
  public function indexCategories(Request $request)
{
    // Si tu veux réactiver la vérification du rôle plus tard :
    // $user = $request->user();
    // if (!$user || $user->role !== 'superadmin') {
    //     return response()->json(['message' => 'Accès refusé.'], 403);
    // }

    // Récupération uniquement des champs id et name
    $categories = AdminCategory::select('id', 'name')->get();

    return response()->json($categories);
}


    /**
     * Créer un nouvel admin.
     */
    public function createAdmin(Request $request)
    {
        $user = $request->user();

if (!$user || $user->role !== 'superadmin') {
    return response()->json(['message' => 'Accès refusé.'], 403);
}


        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:6',
            'category_id' => 'required|exists:admin_categories,id',
            'department'  => 'nullable|string|max:255',
            'speciality'  => 'nullable|string|max:255',
        ]);

        // Crée le compte utilisateur de l'admin
        $admin = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'admin',
        ]);

        // Associe son profil enseignant
        Teacher::create([
            'user_id'     => $admin->id,
            'category_id' => $request->category_id,
            'department'  => $request->department,
            'speciality'  => $request->speciality,
        ]);

        return response()->json([
            'message' => 'Administrateur créé avec succès',
            'admin'   => $admin,
        ]);
    }

    public function updateAdmin(Request $request, $id)
{
    $user = $request->user();

    // Vérification du rôle
    if (!$user || $user->role !== 'superadmin') {
        return response()->json(['message' => 'Accès refusé.'], 403);
    }

    // Récupération de l'utilisateur (admin à modifier)
    $admin = User::where('id', $id)->where('role', 'admin')->first();

    if (!$admin) {
        return response()->json(['message' => 'Administrateur introuvable.'], 404);
    }

    // Validation des champs modifiables
    $request->validate([
        'name'        => 'sometimes|required|string|max:255',
        // 'email'       => 'sometimes|required|email|unique:users,email,' . $admin->id,
        'department'  => 'nullable|string|max:255',
        'speciality'  => 'nullable|string|max:255',
        'category_id' => 'sometimes|exists:admin_categories,id',
    ]);

    // Mise à jour des infos du compte utilisateur
    if ($request->filled('name')) {
        $admin->name = $request->name;
    }

    if ($request->filled('email')) {
        $admin->email = $request->email;
    }

    $admin->save();

    // Mise à jour du profil enseignant (Teacher)
    $teacher = $admin->teacher; // Relation User -> Teacher (à avoir dans ton modèle User)

    if ($teacher) {
        if ($request->filled('department')) {
            $teacher->department = $request->department;
        }

        if ($request->filled('speciality')) {
            $teacher->speciality = $request->speciality;
        }

        if ($request->filled('category_id')) {
            $teacher->category_id = $request->category_id;
        }

        $teacher->save();
    }

    return response()->json([
        'message' => 'Informations de l’administrateur mises à jour avec succès.',
        'admin'   => $admin->load('teacher.category'),
    ]);
}



    /**
     * Lister tous les admins avec leur catégorie.
     */
    public function indexAdmins(Request $request)
    {
//        $user = $request->user();

// if (!$user || $user->role !== 'superadmin') {
//     return response()->json(['message' => 'Accès refusé.'], 403);
// }

        $admins = User::where('role', 'admin')
        ->select('id', 'name', 'email', 'created_at', 'role')
        ->with(['teacher' => function ($query) {
            $query->select(
                'id',
                'user_id',
                'department',
                'speciality',
                'category_id'
            )->with(['category' => function ($catQuery) {
                $catQuery->select('id', 'name');
            }]);
        }])
        ->get();

        return response()->json($admins);
    }


    public function deleteAdmin($id, Request $request)
{
    $user = $request->user();

    // Vérification de l'autorisation
    if (!$user || $user->role !== 'superadmin') {
        return response()->json(['message' => 'Accès refusé.'], 403);
    }

    // Récupération du compte à supprimer
    $admin = User::with('teacher')->where('id', $id)->where('role', 'admin')->first();

    if (!$admin) {
        return response()->json(['message' => 'Administrateur introuvable.'], 404);
    }

    // Suppression du profil enseignant associé (si existe)
    if ($admin->teacher) {
        $admin->teacher->delete();
    }

    // Suppression de l'utilisateur
    $admin->delete();

    return response()->json([
        'message' => 'Administrateur supprimé avec succès.'
    ]);
}

}
