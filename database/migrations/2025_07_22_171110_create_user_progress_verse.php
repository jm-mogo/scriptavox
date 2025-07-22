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
        Schema::create('user_verse_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('verse_id')->constrained()->onDelete('cascade');
            // $table->foreignId('lesson_id')->nullable()->constrained()->onDelete('set null');
            $table->string('status')->default('new');
            $table->dateTime('review_at');
            $table->unsignedInteger('interval')->default(1);
            $table->float('ease_factor')->default(2.5);
            $table->timestamps();

            $table->unique(['user_id', 'verse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_progress_verse');
    }
};
