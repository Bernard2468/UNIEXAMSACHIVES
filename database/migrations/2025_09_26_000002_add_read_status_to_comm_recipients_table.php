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
        Schema::table('comm_recipients', function (Blueprint $table) {
            if (!Schema::hasColumn('comm_recipients', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('status');
            }
            if (!Schema::hasColumn('comm_recipients', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comm_recipients', function (Blueprint $table) {
            if (Schema::hasColumn('comm_recipients', 'read_at')) {
                $table->dropColumn('read_at');
            }
            if (Schema::hasColumn('comm_recipients', 'is_read')) {
                $table->dropColumn('is_read');
            }
        });
    }
};


