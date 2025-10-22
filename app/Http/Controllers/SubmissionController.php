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
        // 1. Validation de la requête
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'file' => 'required|file|max:10240', // Fichier requis, taille max de 10 Mo
        ]);

        // Vérifier si l'utilisateur est un étudiant
        if ($request->user()->role !== 'student') {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // 2. Stockage du fichier sur le serveur
        $filePath = $request->file('file')->store('submissions', 'public');

        // 3. Enregistrement de la soumission dans la base de données
        $submission = Submission::create([
            'project_id' => $request["project_id"],
            'file_path' => $filePath,
        ]);

        return response()->json([
            'message' => 'Fichier soumis avec succès.',
            'submission' => $submission
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





}
