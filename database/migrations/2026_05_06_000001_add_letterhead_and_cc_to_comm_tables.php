<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comm_campaigns', function (Blueprint $table) {
            $table->string('letterhead')->nullable()->after('reference'); // 'cug' | 'internal_memo' | null
            $table->json('cc_users')->nullable()->after('letterhead');   // array of user IDs
        });

        Schema::table('comm_recipients', function (Blueprint $table) {
            $table->string('recipient_role')->default('to')->after('comm_campaign_id'); // 'to' | 'cc'
        });
    }

    public function down(): void
    {
        Schema::table('comm_campaigns', function (Blueprint $table) {
            $table->dropColumn(['letterhead', 'cc_users']);
        });

        Schema::table('comm_recipients', function (Blueprint $table) {
            $table->dropColumn('recipient_role');
        });
    }
};
