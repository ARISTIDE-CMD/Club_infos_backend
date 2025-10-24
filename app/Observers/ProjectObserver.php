<?php
namespace App\Observers;

use App\Models\Project;
use App\Models\ChatRoom;

class ProjectObserver
{
    public function created(Project $project)
    {
        // crée automatiquement la chat room liée au projet
        $chat = ChatRoom::create([
            'project_id' => $project->id,
            'name' => $project->title,
        ]);

        // Optionnel : message système annonçant la création
        $chat->messages()->create([
            'user_id' => 1, // system user id ou null suivant ton modèle
            'content' => "Salon de discussion créé pour le projet : {$project->title}",
            'is_system' => true,
        ]);
    }
}
