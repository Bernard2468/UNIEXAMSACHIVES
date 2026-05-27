<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional saved signature per user. When present, the signature pad on a
     * form stage can offer "Use saved signature" so frequent signers (Finance,
     * Registrar, VC) don't have to re-draw every time.
     */
    public function up(): void
    {
        Schema::create('user_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('signature_image_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_signatures');
    }
};
