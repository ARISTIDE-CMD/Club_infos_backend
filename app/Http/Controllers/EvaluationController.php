<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evaluation;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EvaluationController extends Controller
{
    /**
     * Enregistrer ou mettre à jour une évaluation (note et commentaire) pour une soumission.
     */
    public function storeOrUpdate(Request $request, Submission $submission)
    {
        // 1. Validation : Seul l'admin devrait pouvoir évaluer
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé à effectuer cette action.'], 403);
        }

        // 2. Validation des données de l'évaluation
        $validatedData = $request->validate([
            'grade' => 'nullable|numeric|min:0|max:100', // Assumant une note sur 100
            'comment' => 'nullable|string',
        ]);

        // 3. Récupérer ou créer l'évaluation
        $evaluation = Evaluation::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'grade' => $validatedData['grade'],
                'comment' => $validatedData['comment'],
                'user_id' => Auth::id(), // Enregistrer qui a fait l'évaluation (l'admin)
            ]
        );

        // 4. Retourner la réponse
        return response()->json([
            'message' => 'Évaluation enregistrée avec succès.',
            'evaluation' => $evaluation->load('user')
        ], 200);
    }
}
