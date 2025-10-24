<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Events\MessageSent; // event qui sera broadcasté
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Récupère la chat room d'un projet (et messages paginés)
    public function showProjectChat($projectId)
    {
        // Règle d'autorisation : verifier que l'user est assigné au projet ou a un rôle admin
        $user = Auth::user();

        $project = \App\Models\Project::with('students.user')->findOrFail($projectId);

        // check membership
        if (!$user->isAdmin() && !$project->students->contains(function($s) use ($user) {
            return $s->user_id == $user->id;
        })) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $chat = ChatRoom::where('project_id', $projectId)->firstOrFail();

        $messages = $chat->messages()->with('user')->latest()->paginate(50);
        return response()->json([
            'chat' => $chat,
            'messages' => $messages->items(),
            'meta' => [
                'total' => $messages->total(),
                'per_page' => $messages->perPage(),
            ],
        ]);
    }

    // Envoie un message (stocke et broadcast)
    public function sendMessage(Request $request, $projectId)
    {
        $request->validate(['content' => 'required|string|max:2000']);
        $user = Auth::user();

        $project = \App\Models\Project::with('students')->findOrFail($projectId);

        // vérification d'appartenance au projet
        if (!$user->isAdmin() && !$project->students->contains(function($s) use ($user) {
            return $s->user_id == $user->id;
        })) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $chat = ChatRoom::where('project_id', $projectId)->firstOrFail();

        $message = Message::create([
            'chat_room_id' => $chat->id,
            'user_id' => $user->id,
            'content' => $request->content,
        ]);

        // chargement user pour le broadcast
        $message->load('user');

        // broadcast via event
        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['message' => $message], 201);
    }
}
