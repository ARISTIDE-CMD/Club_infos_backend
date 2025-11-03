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
    Schema::table('students', function (Blueprint $table) {
        $table->dropForeign(['teacher_id']); // ❌ enlève l'ancienne contrainte
        $table->foreign('teacher_id')
              ->references('id')
              ->on('teachers')
              ->onDelete('cascade'); // ✅ crée la bonne relation
    });
}

public function down()
{
    Schema::table('students', function (Blueprint $table) {
        $table->dropForeign(['teacher_id']);
        $table->foreign('teacher_id')
              ->references('id')
              ->on('users')
              ->onDelete('cascade');
    });
}

};
