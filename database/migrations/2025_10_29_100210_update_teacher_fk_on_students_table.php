<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            // Ajouter la colonne teacher_id si elle n'existe pas
            if (!Schema::hasColumn('students', 'teacher_id')) {
                $table->unsignedBigInteger('teacher_id')->nullable();
            }

            // Ajouter la clé étrangère
            $table->foreign('teacher_id')
                  ->references('id')
                  ->on('teachers')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            // Supprimer la clé étrangère
            $table->dropForeign(['teacher_id']);

            // Supprimer la colonne
            $table->dropColumn('teacher_id');
        });
    }
};
