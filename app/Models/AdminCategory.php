<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Relation avec les admins
    public function admins()
    {
        return $this->hasMany(\App\Models\User::class, 'category_id');
    }
}
