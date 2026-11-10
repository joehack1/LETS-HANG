<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 32)->nullable()->unique()->after('email');
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->string('country', 2)->nullable()->after('date_of_birth');
            $table->string('city', 100)->nullable()->after('country');
            $table->string('role', 20)->default('user')->after('city');
            $table->string('account_status', 20)->default('active')->after('role');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->timestamp('terms_accepted_at')->nullable()->after('phone_verified_at');
            $table->string('public_id', 36)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'date_of_birth', 'country', 'city', 'role',
                'account_status', 'phone_verified_at', 'terms_accepted_at', 'public_id',
            ]);
        });
    }
};
