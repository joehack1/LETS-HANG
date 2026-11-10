<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 36)->unique();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('activity_categories')->nullOnDelete();
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('area', 120)->nullable();
            $table->string('venue_name', 160)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('max_participants')->default(20);
            $table->enum('joining_mode', ['open', 'approval'])->default('open');
            $table->enum('visibility', ['public', 'community', 'private'])->default('public');
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 8)->nullable();
            $table->text('instructions')->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('published');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['city', 'starts_at']);
            $table->index(['status', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
