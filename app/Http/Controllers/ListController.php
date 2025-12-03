<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class ListController extends Controller
{
    public function getLimitedList()
{

        $content = null;

        if (Storage::disk('local')->exists('list.json')) {
            $content = Storage::disk('local')->get('list.json');
        } elseif (Storage::disk('public')->exists('list.json')) {
            $content = Storage::disk('public')->get('list.json');
        } elseif (file_exists(storage_path('app/list.json'))) {
            $content = file_get_contents(storage_path('app/list.json'));
        } else {
            return response()->json([
                'error' => 'Le fichier list.json est introuvable dans les emplacements attendus (storage/app/private, storage/app/public ou storage/app)'
            ], 404);
        }

        try {
            // Décoder le JSON en tableau
            $data = json_decode($content, true);
            $jsonError = json_last_error();
            if ($jsonError !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Format du fichier JSON invalide: ' . json_last_error_msg()
                ], 400);
            }
        } catch (\Exception $e) {
            logger()->error('Error reading list.json: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erreur serveur lors de la lecture du fichier list.json'
            ], 500);
        }

        // Si ce n'est pas un tableau valide
        if (!is_array($data)) {
            return response()->json([
                'error' => 'Format du fichier JSON invalide.'
            ], 400);
        }

    // → Filtrer pour garder seulement les éléments avec id entre 20 et 30
    $data = array_filter($data, function ($item) {
        $id = intval($item['id'] ?? 0);
        return $id >= 30 && $id <= 40;
    });

    // → Garder seulement les champs demandés
    $filtered = array_map(function ($item) {
        return [
            'id' => $item['id'] ?? null,
            'author' => $item['author'] ?? null,
            'download_url' => $item['download_url'] ?? null,
        ];
    }, $data);

    // Retourner les résultats filtrés
    return response()->json($filtered);
}

    // Backwards compatibility: some clients may call getList
    public function getList()
    {
        return $this->getLimitedList();
    }

}
