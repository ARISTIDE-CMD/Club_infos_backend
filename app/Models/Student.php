<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasMany; // ✅ Le bon import
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // J'ai remplacé HasMany par BelongsToMany

class Student extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
   protected $fillable = [
    'user_id',
    'student_id',
    'class_group',
    'first_name',
    'last_name',
    'teacher_id', // ✅ nouveau champ
];

public function teacher()
{
    return $this->belongsTo(Teacher::class, 'teacher_id');
}

    /**
     * Get the user that owns the student profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the projects for the student.
     * CORRECTION: Utilisation de BelongsToMany pour une relation many-to-many
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }

     // 🔹 Relation ajoutée : un étudiant a plusieurs soumissions
public function submissions()
{
    // On récupère les soumissions à travers les projets liés
    return $this->hasManyThrough(
        Submission::class,
        Project::class,
        'id',          // Foreign key sur Project (table intermédiaire)
        'project_id',  // Foreign key sur Submission
        null,          // Local key sur Student (id)
        'id'           // Local key sur Project
    );
}
}
