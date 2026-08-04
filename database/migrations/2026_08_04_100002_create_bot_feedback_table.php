<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 👍 / 👎 feedback on bot answers.
 *
 * PRIVACY: `question` and `answer` remain NULL unless the Super Admin explicitly
 * enables transcript storage (bot_store_transcripts). By default only the rating,
 * the answer source and the matched knowledge-base key are stored — enough to see
 * WHICH topics need improvement without recording WHAT individuals typed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('rating', 8);                  // up | down
            $table->string('source', 16)->nullable();     // live | kb | cache | api
            $table->string('matched_key')->nullable();    // KB entry key/title that answered
            $table->text('question')->nullable();         // only when transcripts enabled
            $table->text('answer')->nullable();           // only when transcripts enabled
            $table->timestamps();

            $table->index(['rating', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_feedback');
    }
};
