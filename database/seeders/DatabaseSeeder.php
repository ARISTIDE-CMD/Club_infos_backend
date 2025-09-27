<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer un utilisateur administrateur de test
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Créer un utilisateur étudiant de test avec son profil associé
        $studentUser = User::factory()->create([
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        Student::create([
            'user_id' => $studentUser->id,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'student_id' => 'ETU00001',
            'class_group' => 'L1 Infos',
        ]);
    }
}
