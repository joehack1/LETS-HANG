<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'waitlisted', 'attended', 'no_show', 'cancelled'])->default('pending');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['activity_id', 'user_id']);
            $table->index(['activity_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_participants');
    }
};
