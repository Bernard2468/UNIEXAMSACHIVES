<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Distinguishes an "Individual Staff" account from an "Institutional Office"
 * account. Set on the manage-users Edit Info page. Drives where a folder the
 * account creates surfaces for the people it's shared with: individual-owned
 * folders under "Shared with me", office-owned folders under "Departmental
 * Folders". Existing accounts default to 'individual'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_type')->default('individual')->after('staff_category');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('account_type');
        });
    }
};
