<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per signature applied at a stage of a form submission.
     *
     * Tamper-evidence model:
     *   payload_hash = sha256(canonical JSON of the stage's section data)
     *   chain_hash   = sha256(prior_hash + payload_hash + signer_user_id + signed_at)
     *
     * If any prior section is altered after signing, the next stage's
     * chain_hash will no longer reproduce, exposing the tamper.
     *
     * provider is 'in_app' for v1; the field exists so a future DocuSign /
     * PandaDoc adapter can be added without schema changes.
     */
    public function up(): void
    {
        Schema::create('form_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_submission_id')->constrained('form_submissions')->cascadeOnDelete();
            $table->string('stage_slug');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            $table->string('signature_image_path')->nullable();
            $table->longText('signature_image_data')->nullable();

            $table->timestamp('signed_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->string('prior_hash', 64)->nullable();
            $table->string('payload_hash', 64);
            $table->string('chain_hash', 64);

            $table->string('provider', 32)->default('in_app');
            $table->string('provider_envelope_id')->nullable();

            $table->timestamps();

            $table->index(['form_submission_id', 'stage_slug']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_signatures');
    }
};
