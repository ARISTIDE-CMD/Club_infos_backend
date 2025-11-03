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
use App\Models\Teacher;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // --- Relations existantes ---
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function assignments()
    {
        return $this->hasManyThrough(Project::class, Student::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    // --- Nouvelle relation pour les profs (admins) ---
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    // --- Helpers pour les rôles ---
    public function isSuperAdmin()
    {
        return $this->role === 'superadmin'; // ⚠️ minuscule pour cohérence
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }
}
