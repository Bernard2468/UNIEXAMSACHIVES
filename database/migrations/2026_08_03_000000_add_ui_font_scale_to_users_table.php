<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user text / display size preference. Stored as a multiplier applied via
 * a root `zoom` (browser-zoom-equivalent) so it scales EVERY text and UI
 * element consistently across the whole system. 1.0 = default. The Appearance
 * tab on the Profile Settings page writes this; the base layout reads it and
 * applies it before paint. Existing accounts default to 1.0 (unchanged look).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'ui_font_scale')) {
                $table->decimal('ui_font_scale', 3, 2)->default(1.00)->after('profile_picture');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'ui_font_scale')) {
                $table->dropColumn('ui_font_scale');
            }
        });
    }
};
