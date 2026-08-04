<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user closing salutation (e.g. "Thank you.", "Yours faithfully,") shown
 * in the Settings → Signature tab and appended — together with the saved
 * signature, name and position — to outgoing memos when the composer ticks
 * "Add salutation & signature".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('memo_salutation', 120)->nullable()->after('ui_font_scale');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('memo_salutation');
        });
    }
};
