<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_letterheads', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('image_path');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'display_order']);
        });

        // Seed the two existing hardcoded letterheads so legacy memos keep rendering.
        DB::table('system_letterheads')->insert([
            [
                'slug'          => 'cug',
                'name'          => 'CUG Official',
                'description'   => 'Standard institutional header',
                'image_path'    => 'https://res.cloudinary.com/dsypclqxk/image/upload/v1778084083/1908c951-dd89-405e-8b29-ec367df1969e.png',
                'is_active'     => true,
                'display_order' => 1,
                'uploaded_by'   => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'slug'          => 'internal_memo',
                'name'          => 'Internal Memo',
                'description'   => 'Formal internal communication',
                'image_path'    => 'https://res.cloudinary.com/dsypclqxk/image/upload/v1778066477/81d0f580-93e2-429e-b86a-d3221b0ff84e.png',
                'is_active'     => true,
                'display_order' => 2,
                'uploaded_by'   => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_letterheads');
    }
};
