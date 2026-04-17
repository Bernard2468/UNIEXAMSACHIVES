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
        // Change enum to varchar to support staff category values
        DB::statement("ALTER TABLE comm_campaigns MODIFY COLUMN recipient_type VARCHAR(50) NOT NULL DEFAULT 'all'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum (this will truncate any staff category values)
        DB::statement("ALTER TABLE comm_campaigns MODIFY COLUMN recipient_type ENUM('all', 'selected') NOT NULL DEFAULT 'all'");
    }
};

