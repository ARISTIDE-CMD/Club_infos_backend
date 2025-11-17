<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('students', function (Blueprint $table) {
    $table->id();
    $table->string('first_name');
    $table->string('last_name');
    $table->string('student_id');
    $table->string('class_group');

    // Ajoute la colonne teacher_id
    // $table->unsignedBigInteger('teacher_id');

    // // Définis la clé étrangère
    // $table->foreign('teacher_id')
    //       ->references('id')
    //       ->on('teachers')
    //       ->onDelete('cascade');

    $table->timestamps();
});

    }
// database/migrations/2025_09_19_183638_create_students_table.php
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
