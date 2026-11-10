<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_low_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_high_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['friendship', 'dating']);
            $table->timestamps();

            $table->unique(['user_low_id', 'user_high_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('friendships');
    }
};
