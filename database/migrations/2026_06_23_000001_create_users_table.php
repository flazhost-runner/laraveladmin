<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 50)->nullable();
            $table->string('name', 200);
            $table->string('email', 200)->unique();
            $table->string('phone', 50)->nullable();
            $table->string('password');
            $table->string('picture', 500)->nullable();
            $table->string('status', 20)->default('Active');
            $table->string('otp_hash', 255)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->boolean('remember_token_flag')->default(false);
            $table->string('remember_token', 100)->nullable();
            $table->string('timezone', 50)->default('UTC');
            $table->string('created_by', 36)->nullable();
            $table->string('updated_by', 36)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
