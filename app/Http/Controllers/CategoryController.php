<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AdminCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index() {
        return AdminCategory::all();
    }

    public function store(Request $request) {
        $request->validate(['name' => 'required|string|unique:admin_categories']);
        return AdminCategory::create(['name' => $request->name]);
    }

    public function update(Request $request, AdminCategory $category) {
        $category->update($request->only('name'));
        return response()->json($category);
    }

    public function destroy(AdminCategory $category) {
        $category->delete();
        return response()->json(['message' => 'Catégorie supprimée']);
    }
}
