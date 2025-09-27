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
     * Display a listing of the projects.
     */
    public function index()
    {
        $projects = Project::with('students.user')->get();
        return response()->json(['projects' => $projects]);
    }
    // public function result(){
    //     $result= Submission::with('students.user')->get();
    // }
    /**
     * Store a newly created project in storage.
     * This method handles both admin-assigned projects and student-submitted projects.
     */
    public function store(Request $request)
    {
        try {
            // Case 1: Student submits a project with a file
            if ($request->hasFile('file')) {
                $user = Auth::user();
                if (!$user || $user->role !== 'student') {
                    return response()->json(['message' => 'Non autorisé. Seuls les étudiants peuvent soumettre des projets.'], 403);
                }

                // Validation pour la soumission d'étudiant
                $request->validate([
                    'title' => 'required|string|max:255',
                    'description' => 'required|string',
                    'file' => 'required|file|mimes:pdf,zip,doc,docx|max:10240', // Max 10MB
                ]);

                $student = Student::where('user_id', $user->id)->firstOrFail();

                // Sauvegarde du fichier et création du projet
                $path = $request->file('file')->store('public/projects');
                $filePath = Storage::url($path);

                $project = Project::create([
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                    'file_path' => $filePath,
                ]);

                // Lier le projet à l'étudiant
                $student->projects()->attach($project->id);

                return response()->json([
                    'message' => 'Projet soumis avec succès.',
                    'project' => $project,
                ], 201);
            }

            // Case 2: Admin/Teacher assigns a project to students
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'student_ids' => 'required|array',
                'student_ids.*' => 'exists:students,id',
            ]);

            $project = Project::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
            ]);

            $project->students()->sync($validatedData['student_ids']);

            return response()->json(['message' => 'Projet créé avec succès', 'project' => $project], 201);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Project submission failed: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la soumission du projet.'], 500);
        }
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'student_ids' => 'array',
                'student_ids.*' => 'exists:students,id',
            ]);

            $project = Project::findOrFail($id);

            $project->update([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
            ]);

            if (isset($validatedData['student_ids'])) {
                $project->students()->sync($validatedData['student_ids']);
            }

            return response()->json(['message' => 'Projet mis à jour avec succès']);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la mise à jour du projet.'], 500);
        }
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Projet non trouvé.'], 404);
        }

        $project->delete();

        return response()->json(['message' => 'Projet supprimé avec succès.']);
    }
}
