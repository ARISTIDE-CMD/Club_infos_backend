<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
  public function run(): void
{
    // Vérifie si le super admin existe déjà
    $superAdmin = User::where('email', 'superadmin@example.com')->first();

    if (!$superAdmin) {
        User::create([
            'name' => 'Superadmin User',
            'email' => 'superadmin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'superadmin',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);
    }
}

}
