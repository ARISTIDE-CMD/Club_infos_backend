<?php
namespace App\Observers;

use App\Models\Project;
use App\Models\ProjectMessage;

class ProjectObserver
{
    public function created(Project $project)
    {
        // Création d'un message système pour le projet
        ProjectMessage::create([
            'project_id' => $project->id,
            'user_id' => 4, // null pour indiquer que c'est un message système
            'message' => "Salon de discussion créé pour le projet : {$project->title}",
        ]);
    }
}
