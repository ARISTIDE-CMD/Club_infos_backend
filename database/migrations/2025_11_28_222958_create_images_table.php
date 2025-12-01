<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('images', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('student_id'); // clé étrangère vers students
        $table->string('path'); // chemin de l'image (ex: storage/images/...)
        $table->string('type')->nullable(); // ex: "profile", "document", etc.
        $table->timestamps();

        // Relation avec la table students
        $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
    });
}

public function down()
{
    Schema::dropIfExists('images');
}

};
