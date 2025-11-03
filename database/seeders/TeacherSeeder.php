<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Teacher;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ Création du compte utilisateur correspondant à l’enseignant
     $category = \App\Models\AdminCategory::first(); // Récupère une catégorie existante

$teacherUser = \App\Models\User::factory()->create([
    'name' => 'Test Teacher',
    'email' => 'teacher@example.com',
    'password' => \Illuminate\Support\Facades\Hash::make('password'),
    'role' => 'admin',
]);

Teacher::create([
    'user_id' => $teacherUser->id,
    'department' => 'Informatique',
    'speciality' => 'Génie Logiciel',
    'category_id' => $category ? $category->id : null, // Associe la catégorie si elle existe
]);

    }
}
