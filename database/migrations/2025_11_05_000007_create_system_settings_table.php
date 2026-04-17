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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            
            // Key-value pair
            $table->string('key')->unique();
            $table->text('value'); // JSON serializable
            
            // Categorization
            $table->enum('category', [
                'general',
                'payment_gateway',
                'subscription',
                'maintenance',
                'security',
                'email',
                'notifications',
                'api',
                'ui',
                'other'
            ])->default('general');
            
            // Metadata
            $table->string('label')->nullable(); // Display name
            $table->text('description')->nullable();
            $table->enum('data_type', ['string', 'integer', 'boolean', 'json', 'text'])->default('string');
            
            // Visibility and editing
            $table->boolean('is_public')->default(false); // Can regular users see this?
            $table->boolean('is_editable')->default(true); // Can be edited through UI?
            $table->boolean('requires_restart')->default(false); // Does changing this require system restart?
            
            // Validation
            $table->string('validation_rules')->nullable(); // Laravel validation rules
            $table->text('default_value')->nullable();
            
            // Administrative
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index('key');
            $table->index('category');
            $table->index(['category', 'is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};

