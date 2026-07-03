<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('name', 200);
            $table->string('method', 20)->default('GET');
            $table->string('guard_name', 20)->default('web');
            $table->string('status', 20)->default('Active');
            $table->string('desc', 255)->nullable();
            $table->string('created_by', 36)->nullable();
            $table->string('updated_by', 36)->nullable();
            $table->timestamps();
            // unique on name+method+guard_name (NOT on name alone)
            $table->unique(['name', 'method', 'guard_name'], 'permissions_name_method_guard_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
