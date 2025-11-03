<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Ajouter la colonne category_id dans users pour lier un admin à sa catégorie
//        Schema::table('teachers', function (Blueprint $table) {
//     // Ajoute la colonne category_id (facultative)
//     $table->foreignId('category_id')
//           ->nullable()
//           ->constrained('admin_categories')
//           ->nullOnDelete(); // équivaut à ->onDelete('set null')
// });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        Schema::dropIfExists('admin_categories');
    }
};
