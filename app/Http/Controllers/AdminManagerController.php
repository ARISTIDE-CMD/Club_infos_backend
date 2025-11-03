<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminManagerController extends Controller
{
    public function index()
    {
        return User::where('role', 'admin')->with('category')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'category_id' => 'nullable|exists:admin_categories,id'
        ]);

        $admin = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'admin',
            'category_id' => $request->category_id
        ]);

        return response()->json($admin, 201);
    }

    public function update(Request $request, User $admin)
    {
        $admin->update($request->only(['name', 'email', 'category_id']));
        return response()->json($admin);
    }

    public function toggleActive(User $admin)
    {
        $admin->update(['active' => !$admin->active]);
        return response()->json(['message' => 'Statut modifié', 'active' => $admin->active]);
    }

    public function destroy(User $admin)
    {
        $admin->delete();
        return response()->json(['message' => 'Admin supprimé']);
    }
}
