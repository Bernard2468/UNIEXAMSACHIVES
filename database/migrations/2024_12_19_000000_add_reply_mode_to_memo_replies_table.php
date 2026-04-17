<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('memo_replies', function (Blueprint $table) {
            $table->string('reply_mode')->default('all')->after('attachments');
            $table->json('specific_recipients')->nullable()->after('reply_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memo_replies', function (Blueprint $table) {
            $table->dropColumn(['reply_mode', 'specific_recipients']);
        });
    }
};
