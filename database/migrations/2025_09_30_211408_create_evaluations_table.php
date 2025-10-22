<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter les migrations.
     */
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();

            // Clé étrangère vers la soumission
            // Chaque soumission n'aura qu'une seule évaluation
            $table->foreignId('submission_id')->constrained()->onDelete('cascade')->unique();

            // Clé étrangère vers l'utilisateur (le professeur/admin) qui a évalué
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // Note (grade) : stockée comme un nombre décimal (ex: 15.5/20)
            $table->decimal('grade', 5, 2)->nullable();

            // Commentaires de l'évaluateur
            $table->text('comment')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Annuler les migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
