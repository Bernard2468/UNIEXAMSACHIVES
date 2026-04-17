<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_subscriptions', function (Blueprint $table) {
            $table->string('subscription_plan', 100)->change();
        });
    }

    public function down(): void
    {
        Schema::table('system_subscriptions', function (Blueprint $table) {
            $table->enum('subscription_plan', ['basic', 'standard', 'premium', 'enterprise'])
                  ->default('basic')
                  ->change();
        });
    }
};
