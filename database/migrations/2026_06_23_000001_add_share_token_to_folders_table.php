<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A revocable "anyone with the link" capability token for a folder.
     *
     * When present, a logged-in user who opens /folders/{folder}/join/{token}
     * is enrolled as a real viewer (a row in folder_shares), so they remain
     * visible/auditable in the member list. Rotating the token (regenerating
     * it) instantly invalidates the old link without revoking already-joined
     * members. A null token means link sharing is disabled.
     */
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->string('share_token', 64)->nullable()->unique()->after('color');
            $table->timestamp('share_token_created_at')->nullable()->after('share_token');
        });
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn(['share_token', 'share_token_created_at']);
        });
    }
};
