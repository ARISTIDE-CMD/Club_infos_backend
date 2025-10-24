<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    protected $fillable = ['project_id', 'name'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    // utile : récupère les étudiants via le projet
    public function students()
    {
        return $this->project->students(); // attention : lazy access
    }
}
