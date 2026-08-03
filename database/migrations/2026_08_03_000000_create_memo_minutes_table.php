<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Formal, signed memo minutes (the "Minute-To" action). Distinct from
 * memo_replies: a reply is an informal discussion message, a minute is an
 * official routing instruction signed by the minuting officer. The signature
 * image is snapshotted per minute so later changes to the user's saved
 * signature never rewrite what was signed on an existing memo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memo_minutes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedInteger('minute_no');
            $table->json('to_user_ids')->nullable();
            $table->string('to_names', 500)->nullable();
            $table->text('remark')->nullable();
            $table->string('signature_image_path')->nullable();
            $table->timestamp('signed_at');
            $table->timestamps();

            $table->unique(['campaign_id', 'minute_no']);
        });

        // Links the auto-posted chat note to its minute so the PDF export can
        // print the signed minute once instead of duplicating it in Discussion.
        Schema::table('memo_replies', function (Blueprint $table) {
            $table->unsignedBigInteger('minute_id')->nullable()->index()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('memo_replies', function (Blueprint $table) {
            $table->dropColumn('minute_id');
        });

        Schema::dropIfExists('memo_minutes');
    }
};
