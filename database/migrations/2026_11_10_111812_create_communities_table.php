<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('category', 60)->nullable();
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('visibility');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communities');
    }
};
