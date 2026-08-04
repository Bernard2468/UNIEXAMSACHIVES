<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Super-Admin-editable knowledge base for the UDTS Assistant bot.
 *
 * These entries are merged ON TOP of the built-in static system map in
 * App\Services\Bot\KnowledgeBase, so the institution can "feed" the bot more
 * local answers over time without touching code. `keywords` (space/comma list)
 * drives the token-overlap retrieval; `links` is a JSON array of {label, url}
 * deep-links the answer offers.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_knowledge_entries', function (Blueprint $table) {
            $table->id();
            $table->string('category')->default('general');
            $table->string('title');
            $table->text('keywords');            // matched against the user's question
            $table->longText('answer');          // markdown
            $table->json('links')->nullable();   // [{label, url}]
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('hits')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_knowledge_entries');
    }
};
