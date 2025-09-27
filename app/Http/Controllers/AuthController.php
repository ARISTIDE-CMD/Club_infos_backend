<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Gère la connexion des utilisateurs (admin ou étudiant).
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;
            $user->load('student'); // Charger la relation avec l'étudiant

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'student_id' => $user->student?->id // Ajouter l'ID de l'étudiant
                    // 'idStudent'=> $user->student?->id // Ajouter l'ID de l'étudiant
                ]
            ]);
        }

        return response()->json(['message' => 'Identifiants invalides.'], 401);
    }

    /**
     * Enregistre un nouvel étudiant (réservé à l'administrateur).
     */
    public function registerStudent(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
        ]);

        $password = Str::random(10);

        $user = User::create([
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'role' => 'student',
        ]);

        return response()->json([
            'message' => 'Étudiant créé avec succès.',
            'password' => $password, // À afficher à l'administrateur
        ], 201);
    }
    public function logout(Request $request)
    {
        // Supprime le jeton d'authentification de la session en cours
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnexion réussie.']);
    }
}
