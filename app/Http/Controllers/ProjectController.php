<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Student;
use App\Models\Submission;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    /**
     * Affiche les projets de tous les étudiants (ou seulement ceux de l'admin si admin).
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            // Projets liés aux étudiants de cet admin
            $studentIds = $user->teacher->students()->pluck('id');
            $projects = Project::whereHas('students', fn($q) => $q->whereIn('students.id', $studentIds))
                ->with('students.user')
                ->get();
        } else {
            // Superadmin ou autres rôles voient tous les projets
            $projects = Project::with('students.user')->get();
        }

        return response()->json(['projects' => $projects]);
    }

    /**
     * Affiche un projet spécifique avec ses relations
     */
    public function show($id)
    {
        $user = Auth::user();
        $project = Project::with(['students.user', 'submissions.student.user', 'submissions.evaluations.user'])
            ->findOrFail($id);

        // Restriction : admin ne voit que ses projets
        if ($user->role === 'admin') {
            $studentIds = $user->teacher->students()->pluck('id');
            if (!$project->students->pluck('id')->intersect($studentIds)->count()) {
                return response()->json(['message' => 'Accès refusé.'], 403);
            }
        }

        return response()->json(['project' => $project]);
    }

    /**
     * Création de projet
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Étudiant : soumission
        if ($request->hasFile('file')) {
            if ($user->role !== 'student') {
                return response()->json(['message' => 'Non autorisé.'], 403);
            }

            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'file' => 'required|file|mimes:pdf,zip,doc,docx|max:10240',
            ]);

            $student = Student::where('user_id', $user->id)->firstOrFail();
            $path = $request->file('file')->store('public/projects');
            $filePath = Storage::url($path);

            $project = Project::create([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'file_path' => $filePath,
            ]);

            $student->projects()->attach($project->id);

            return response()->json(['message' => 'Projet soumis avec succès.', 'project' => $project], 201);
        }

        // Admin/Teacher : création et assignation
        if ($user->role === 'admin') {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'student_ids' => 'required|array',
                'student_ids.*' => 'exists:students,id',
            ]);

            // Vérifier que les étudiants appartiennent à cet admin
            $teacherStudentIds = $user->teacher->students()->pluck('id')->toArray();
            foreach ($request->student_ids as $sid) {
                if (!in_array($sid, $teacherStudentIds)) {
                    return response()->json(['message' => 'Vous ne pouvez assigner que vos propres étudiants.'], 403);
                }
            }

            $project = Project::create([
                'title' => $request->title,
                'description' => $request->description,
            ]);

            $project->students()->sync($request->student_ids);

            return response()->json(['message' => 'Projet créé avec succès', 'project' => $project], 201);
        }

        return response()->json(['message' => 'Non autorisé.'], 403);
    }

    /**
     * Mise à jour du projet
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);

        if ($user->role === 'admin') {
            // Vérification : admin ne peut modifier que ses projets
            $studentIds = $user->teacher->students()->pluck('id');
            if (!$project->students->pluck('id')->intersect($studentIds)->count()) {
                return response()->json(['message' => 'Accès refusé.'], 403);
            }
        }

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'student_ids' => 'array',
            'student_ids.*' => 'exists:students,id',
        ]);

        $project->update([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
        ]);

        if (isset($validatedData['student_ids']) && $user->role === 'admin') {
            // Ne peut assigner que ses propres étudiants
            $teacherStudentIds = $user->teacher->students()->pluck('id')->toArray();
            $validIds = array_intersect($validatedData['student_ids'], $teacherStudentIds);
            $project->students()->sync($validIds);
        }

        return response()->json(['message' => 'Projet mis à jour avec succès']);
    }

    /**
     * Suppression du projet
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);

        if ($user->role === 'admin') {
            $studentIds = $user->teacher->students()->pluck('id');
            if (!$project->students->pluck('id')->intersect($studentIds)->count()) {
                return response()->json(['message' => 'Accès refusé.'], 403);
            }
        }

        $project->delete();

        return response()->json(['message' => 'Projet supprimé avec succès']);
    }
}
