<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Student;
use App\Models\Project;
use App\Models\Message;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Les attributs qui peuvent être assignés en masse.
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Les attributs qui doivent être cachés lors de la sérialisation.
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Les attributs qui doivent être castés.
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Obtenir l'étudiant associé à cet utilisateur.
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Obtenir les attributions de projet de l'utilisateur (via son profil étudiant).
     */
    public function assignments()
    {
        return $this->hasManyThrough(Project::class, Student::class);
    }

    /**
     * Obtenir les messages envoyés par l'utilisateur.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Obtenir les messages reçus par l'utilisateur.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
}
