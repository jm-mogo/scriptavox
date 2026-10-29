<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memorize_passages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('chapter');
            $table->unsignedSmallInteger('verse_from');
            $table->unsignedSmallInteger('verse_to');
            $table->unsignedTinyInteger('hide_level')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'book_id', 'chapter', 'verse_from', 'verse_to'], 'memorize_passages_unique_range');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memorize_passages');
    }
};
