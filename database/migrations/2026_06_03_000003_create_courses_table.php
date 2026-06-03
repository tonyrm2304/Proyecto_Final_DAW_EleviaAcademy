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
    Schema::create('courses', function (Blueprint $table) {
        $table->id();
        $table->string('title', 150);
        $table->string('short_description', 255);
        $table->text('long_description');
        $table->integer('duration_hours');
        
        // Claves foráneas configuradas al estilo Laravel
        $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
        $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
        
        $table->boolean('is_hidden')->default(false);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
