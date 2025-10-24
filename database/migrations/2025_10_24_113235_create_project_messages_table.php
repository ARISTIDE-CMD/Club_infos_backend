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
        Schema::create('project_messages', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('project_id');
    $table->unsignedBigInteger('user_id'); // étudiant qui envoie
    $table->text('message');
    $table->timestamps();
    $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_messages');
    }
};
