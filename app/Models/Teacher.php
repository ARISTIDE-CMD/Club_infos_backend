<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id',
    'department',
    'speciality',
    'category_id', // ✅ ajout du champ catégorie
];
public function category()
{
    return $this->belongsTo(AdminCategory::class, 'category_id');
}


    /**
     * L’enseignant est associé à un utilisateur.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * L’enseignant peut créer plusieurs étudiants.
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'teacher_id');
    }
}
