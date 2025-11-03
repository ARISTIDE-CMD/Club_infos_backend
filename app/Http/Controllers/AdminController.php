<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Crée un nouvel étudiant et un compte utilisateur pour lui.
     * L'étudiant est automatiquement lié à l'enseignant connecté.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'student_id' => 'required|string|unique:students',
                'class_group' => 'required|string|max:255',
            ]);

            // Récupère le teacher associé à l'utilisateur connecté
            $teacher = Teacher::where('user_id', auth()->id())->first();

            if (!$teacher) {
                return response()->json(['message' => 'Aucun enseignant associé à cet utilisateur.'], 403);
            }

            // Création du compte utilisateur pour l'étudiant
            $user = User::create([
                'name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'role' => 'student',
            ]);

            // Création du profil étudiant lié à cet enseignant
            $student = Student::create([
                'user_id' => $user->id,
                'student_id' => $validatedData['student_id'],
                'class_group' => $validatedData['class_group'],
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'teacher_id' => $teacher->id,
            ]);

            return response()->json([
                'message' => 'Étudiant et compte utilisateur créés avec succès.',
                'user' => $user,
                'student' => $student,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation échouée.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Met à jour les informations d'un étudiant appartenant à l'enseignant connecté.
     */
    public function update(Request $request, $id)
    {
        try {
            $teacher = Teacher::where('user_id', auth()->id())->first();

            if (!$teacher) {
                return response()->json(['message' => 'Non autorisé.'], 403);
            }

            $student = Student::where('id', $id)
                ->where('teacher_id', $teacher->id)
                ->with('user')
                ->firstOrFail();

            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'student_id' => [
                    'required',
                    'string',
                    Rule::unique('students', 'student_id')->ignore($student->id),
                ],
                'class_group' => 'required|string|max:255',
            ]);

            $student->user->update([
                'name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'],
            ]);

            $student->update([
                'student_id' => $validatedData['student_id'],
                'class_group' => $validatedData['class_group'],
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
            ]);

            return response()->json(['message' => 'Étudiant mis à jour avec succès.']);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Cet étudiant ne vous appartient pas.'], 403);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la mise à jour.'], 500);
        }
    }

    /**
     * Supprime un étudiant appartenant à l'enseignant connecté.
     */
    public function destroy($id)
    {
        try {
            $teacher = Teacher::where('user_id', auth()->id())->first();

            if (!$teacher) {
                return response()->json(['message' => 'Non autorisé.'], 403);
            }

            $student = Student::where('id', $id)
                ->where('teacher_id', $teacher->id)
                ->with('user')
                ->firstOrFail();

            $student->user->delete();
            $student->delete();

            return response()->json(['message' => 'Étudiant supprimé avec succès.']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Cet étudiant ne vous appartient pas ou n’existe pas.'], 403);
        }
    }
}
