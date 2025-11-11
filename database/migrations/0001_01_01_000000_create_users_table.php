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
        // ===== TABLE USERS =====
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id'); // Compatible PostgreSQL
            $table->string('name');
            $table->string('email');//->unique(); // OK pour PostgreSQL
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->string('role')->default('student');
        });

        // ===== TABLE PASSWORD_RESET_TOKENS =====
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->bigIncrements('id'); // ✅ clé primaire obligatoire pour PostgreSQL
            $table->string('email')->index(); // index pour la recherche
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // ===== TABLE SESSIONS =====
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();

            // ✅ Optionnel mais recommandé : contrainte d’intégrité
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
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
