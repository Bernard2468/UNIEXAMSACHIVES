<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email_otp')->nullable()->after('email_verified_at');
            $table->timestamp('email_otp_expires_at')->nullable()->after('email_otp');
            $table->unsignedTinyInteger('email_otp_attempts')->default(0)->after('email_otp_expires_at');
            $table->timestamp('email_otp_last_sent_at')->nullable()->after('email_otp_attempts');
        });

        // Backfill: every existing user is treated as verified so we don't lock anyone out.
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_otp',
                'email_otp_expires_at',
                'email_otp_attempts',
                'email_otp_last_sent_at',
            ]);
        });
    }
};
