<?php
use Illuminate\Support\Facades\Log;

Broadcast::channel('chat.{projectId}', function ($user, $projectId) {
    // vérifier que l'utilisateur appartient au projet
    $project = \App\Models\Project::with('students')->find($projectId);
    if (!$project) return false;
    // autorise si admin
    if ($user->isAdmin()) return true;
    // sinon check student
    return $project->students->contains(function($s) use ($user) {
        return $s->user_id === $user->id;
    });
});
