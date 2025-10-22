<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    /**
     * Affiche la liste de toutes les soumissions avec les relations étudiants et utilisateurs.
     */
   public function index()
{
    try {
        // Récupère toutes les soumissions avec les relations nécessaires
        $submissions = Submission::with([
            'project.students.user', // Récupère le projet + ses étudiants + leurs infos user
            'student.user', // Récupère l'étudiant qui a soumis le fichier
            'evaluation' // Récupère l'évaluation et l'admin qui a évalué
        ])->get();

        return response()->json([
            'success' => true,
            'submissions' => $submissions,
            'count' => $submissions->count()
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des soumissions',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Enregistre une nouvelle soumission de projet par un étudiant.
     */
public function store(Request $request)
{
    // 1️⃣ Validation de la requête
    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'file' => 'required|file|max:10240', // max 10 Mo
    ]);

    // 2️⃣ Vérification du rôle
    if ($request->user()->role !== 'student') {
        return response()->json(['message' => 'Non autorisé.'], 403);
    }

    $projectId = $request->input('project_id');

    // 3️⃣ Vérifier s’il existe déjà une soumission pour ce projet
    $existingSubmission = Submission::where('project_id', $projectId)->first();

    // 4️⃣ Enregistrer le nouveau fichier
    $filePath = $request->file('file')->store('submissions', 'public');

    if ($existingSubmission) {
        // Supprimer l'ancien fichier si présent
        if ($existingSubmission->file_path && \Storage::disk('public')->exists($existingSubmission->file_path)) {
            \Storage::disk('public')->delete($existingSubmission->file_path);
        }

        // Mettre à jour la soumission existante
        $existingSubmission->update([
            'file_path' => $filePath,
        ]);

        return response()->json([
            'message' => 'Soumission mise à jour avec succès.',
            'submission' => $existingSubmission
        ], 200);
    }

    // 5️⃣ Sinon, créer une nouvelle soumission
    $newSubmission = Submission::create([
        'project_id' => $projectId,
        'file_path' => $filePath,
    ]);

    return response()->json([
        'message' => 'Fichier soumis avec succès.',
        'submission' => $newSubmission
    ], 201);
}


public function download(Submission $submission)
{
    if (!$submission->file_path) {
        abort(404, 'Pas de fichier associé à cette soumission.');
    }

    $filePath = storage_path('app/public/' . ltrim($submission->file_path, '/'));

    if (!file_exists($filePath)) {
        abort(404, 'Fichier introuvable sur le serveur : ' . $filePath);
    }

    return response()->download($filePath);
}

public function downloadFile($filename)
{
    $filePath = storage_path('app/public/' . $filename);

    // Vérifie que le fichier existe
    if (!file_exists($filePath)) {
        abort(404, 'Fichier introuvable sur le serveur.');
    }

    return response()->download($filePath);
}

public function show($id)
{
    $submission = Submission::with([
        'student.user',   // si la soumission appartient à un étudiant
        'project',        // si tu veux le projet lié
        'evaluations.user' // ⚡ ici on ajoute les évaluations + leur auteur
    ])->findOrFail($id);

    return response()->json(['submission' => $submission]);
}




}
