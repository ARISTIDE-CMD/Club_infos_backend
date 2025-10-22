<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    /**
     * Les attributs qui peuvent être assignés en masse.
     * @var array<int, string>
     */
    protected $fillable = [
        'submission_id',
        'user_id',
        'grade',
        'comment',
    ];

    /**
     * Obtenir la soumission associée à cette évaluation.
     */
    public function submission()
{
    return $this->belongsTo(Submission::class);
}

public function user()
{
    return $this->belongsTo(User::class); // L’évaluateur (prof/admin)
}

}
