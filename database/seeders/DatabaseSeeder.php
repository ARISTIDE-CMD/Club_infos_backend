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
        // 🔹 Création du Super Admin principal
        // User::factory()->create([
        //     'name' => 'Super Admin',
        //     'email' => 'superadmin@example.com',
        //     'password' => Hash::make('password'),
        //     'role' => 'superadmin',
        // ]);

        // 🔹 Création d’un utilisateur admin de test
        // User::factory()->create([
        //     'name' => 'Admin User',
        //     'email' => 'admin@example.com',
        //     'password' => Hash::make('password'),
        //     'role' => 'admin',
        // ]);

        // ou récupère le teacher spécifique que tu veux assigner
        // 🔹 Création d’un étudiant avec son profil associé
      for ($i = 1; $i <= 1000; $i++) {
    $studentUser = User::factory()->create([
        'name' => "Test Student $i",
        'email' => "student{$i}_" . uniqid() . "@example.com",
        'password' => Hash::make('password'),
        'role' => 'student',
    ]);
$teacher = \App\Models\Teacher::first();
    Student::create([
        'user_id' => $studentUser->id,
        'first_name' => 'Test',
        'last_name' => "Student $i",
        'student_id' => 'ETU' . str_pad($i, 5, '0', STR_PAD_LEFT),
        'class_group' => 'L1 Infos',
        'teacher_id' =>2 //$teacher ? $teacher->id : null,
    ]);
}


        // 🔹 Appel du seeder des enseignants
        $this->call([
            TeacherSeeder::class, // ✅ Appel correct
        ]);
        $this->call([
            SuperAdminSeeder::class, // ✅ Appel correct
        ]);
    }
}
