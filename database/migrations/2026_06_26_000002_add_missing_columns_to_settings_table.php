<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'favicon')) {
                $table->string('favicon', 500)->nullable()->after('logo');
            }
            if (! Schema::hasColumn('settings', 'created_by')) {
                $table->string('created_by', 36)->nullable()->after('fe_template');
            }
            if (! Schema::hasColumn('settings', 'updated_by')) {
                $table->string('updated_by', 36)->nullable()->after('created_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['favicon', 'created_by', 'updated_by']);
        });
    }
};
