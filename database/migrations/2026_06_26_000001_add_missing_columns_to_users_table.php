<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'blocked')) {
                $table->boolean('blocked')->default(false)->after('picture');
            }
            if (! Schema::hasColumn('users', 'blocked_reason')) {
                $table->string('blocked_reason', 500)->nullable()->after('blocked');
            }
            // Rename OTP columns to match standard naming
            if (Schema::hasColumn('users', 'otp_hash') && ! Schema::hasColumn('users', 'password_otp')) {
                $table->renameColumn('otp_hash', 'password_otp');
            }
            if (Schema::hasColumn('users', 'otp_expires_at') && ! Schema::hasColumn('users', 'password_otp_expires')) {
                $table->renameColumn('otp_expires_at', 'password_otp_expires');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_verified_at', 'blocked', 'blocked_reason']);
            if (Schema::hasColumn('users', 'password_otp')) {
                $table->renameColumn('password_otp', 'otp_hash');
            }
            if (Schema::hasColumn('users', 'password_otp_expires')) {
                $table->renameColumn('password_otp_expires', 'otp_expires_at');
            }
        });
    }
};
