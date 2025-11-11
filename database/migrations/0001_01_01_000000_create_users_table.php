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
        // Table users
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id'); // évite les problèmes de unsigned
            $table->string('name');
            $table->string('email')->unique(); // tu peux réactiver unique ici sans souci
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->string('role')->default('student');
        });

        // Table password_reset_tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            // $table->bigIncrements('id'); // Ajoute une clé primaire explicite (PostgreSQL l’aime bien)
            $table->string('email');
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Table sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // correspond à bigIncrements
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();

            // Optionnel : si tu veux la contrainte FK
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
