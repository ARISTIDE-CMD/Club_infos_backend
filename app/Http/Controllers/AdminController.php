<?php
// app/Http/Controllers/AdminController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule; // Importation de la classe Rule

class AdminController extends Controller
{
    /**
     * Crée un nouvel étudiant et un compte utilisateur pour lui.
     */
    public function store(Request $request)
    {
        try {
            // 1. Validation des données de la requête
            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'student_id' => 'required|string|unique:students',
                'class_group' => 'required|string|max:255',
            ]);

            // 2. Création du compte utilisateur
            $user = User::create([
                'name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'role' => 'student',
            ]);

            // 3. Création du profil étudiant en incluant le nom et le prénom
            $student = Student::create([
                'user_id' => $user->id,
                'student_id' => $validatedData['student_id'],
                'class_group' => $validatedData['class_group'],
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
            ]);

            // 4. Retourner une réponse de succès
            return response()->json([
                'message' => 'Étudiant et compte utilisateur créés avec succès.',
                'user' => $user,
                'student' => $student,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'alredy',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Met à jour les informations d'un étudiant et de son compte utilisateur.
     */
    public function update(Request $request, $id)
    {
        try {
            // 1. Validation des données
            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'student_id' => [
                    'required',
                    'string',
                    Rule::unique('students', 'student_id')->ignore($id),
                ],
                'class_group' => 'required|string|max:255',
            ]);

            // 2. Trouver l'étudiant et son utilisateur associé
            $student = Student::where('id', $id)->with('user')->firstOrFail();

            // 3. Mettre à jour les informations de l'utilisateur
            $student->user->update([
                'name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'],
            ]);

            // 4. Mettre à jour les informations de l'étudiant
            $student->update([
                'student_id' => $validatedData['student_id'],
                'class_group' => $validatedData['class_group'],
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
            ]);

            // 5. Retourner une réponse de succès
            return response()->json(['message' => 'Étudiant mis à jour avec succès.']);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la mise à jour de l\'étudiant.'], 500);
        }
    }

    /**
     * Supprime l'utilisateur et son profil étudiant.
     */
    public function destroy(Request $request, $id)
    {
        $student = Student::where('id', $id)->with('user')->first();

        if (!$student) {
            return response()->json(['message' => 'Étudiant non trouvé.'], 404);
        }

        $student->user->delete();

        return response()->json(['message' => 'Étudiant supprimé avec succès.']);
    }
}
