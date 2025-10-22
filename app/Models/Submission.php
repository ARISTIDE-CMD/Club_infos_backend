<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use App\Models\Project;

class Submission extends Model
{
    use HasFactory;

    /**
     * Les attributs qui peuvent être assignés en masse.
     * @var array<int, string>
     */
    protected $fillable = [
        // 'student_id',
        'project_id',
        'file_path',
        // 'comment',
    ];

    /**
     * Obtenir l'étudiant associé à cette attribution.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Obtenir le projet associé à cette attribution.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    // Exemple dans Submission.php
public function evaluation()
{
    return $this->hasOne(Evaluation::class);
}

}

