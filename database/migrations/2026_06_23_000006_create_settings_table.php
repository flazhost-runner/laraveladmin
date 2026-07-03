<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('initial', 10)->nullable();
            $table->string('name', 200)->nullable();
            $table->text('description')->nullable(); // rich HTML
            $table->string('icon', 500)->nullable();
            $table->string('logo', 500)->nullable();
            $table->string('login_image', 500)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 200)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('copyright', 200)->nullable();
            $table->string('theme', 50)->default('blue');
            $table->string('fe_template', 200)->default('agency-consulting-002-creative-agency');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
