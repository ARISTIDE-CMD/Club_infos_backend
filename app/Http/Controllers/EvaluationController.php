<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evaluation;
use App\Models\Submission;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    /**
     * Enregistrer ou mettre à jour une évaluation (note et commentaire)
     * pour une soumission appartenant à un étudiant de l'admin connecté.
     */
    public function storeOrUpdate(Request $request, Submission $submission)
    {
        $user = Auth::user();

        // 1. Vérification du rôle
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé à effectuer cette action.'], 403);
        }

        // 2. Vérification : l’admin ne peut évaluer que ses propres étudiants
        $student = $submission->student()->first();

        if (!$student || $student->teacher_id !== $user->teacher->id) {
            return response()->json(['message' => 'Vous ne pouvez évaluer que vos propres étudiants.'], 403);
        }

        // 3. Validation des données
        $validatedData = $request->validate([
            'grade' => 'nullable|numeric|min:0|max:20',
            'comment' => 'nullable|string',
        ]);

        // 4. Enregistrement ou mise à jour de l’évaluation
        $evaluation = Evaluation::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'grade' => $validatedData['grade'],
                'comment' => $validatedData['comment'],
                'user_id' => $user->id, // admin évaluateur
            ]
        );

        // 5. Réponse
        return response()->json([
            'message' => 'Évaluation enregistrée avec succès.',
            'evaluation' => $evaluation->load('user')
        ], 200);
    }
}
