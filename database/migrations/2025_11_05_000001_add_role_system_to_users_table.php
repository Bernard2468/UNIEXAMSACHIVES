<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add new role system (super_admin, admin, user)
            $table->enum('role', ['super_admin', 'admin', 'user'])->default('user')->after('is_approve');
            
            // Super admin tracking
            $table->timestamp('super_admin_access_granted_at')->nullable()->after('role');
            $table->foreignId('super_admin_granted_by')->nullable()->constrained('users')->onDelete('set null')->after('super_admin_access_granted_at');
            
            // Add index for performance
            $table->index('role');
        });

        // Migrate existing is_admin flags to role system
        DB::table('users')->where('is_admin', 1)->update(['role' => 'admin']);
        DB::table('users')->where('is_admin', 0)->update(['role' => 'user']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['super_admin_granted_by']);
            $table->dropIndex(['role']);
            $table->dropColumn([
                'role',
                'super_admin_access_granted_at',
                'super_admin_granted_by'
            ]);
        });
    }
};

