<?php

namespace App\Forms\Contracts;

use App\Models\FormSignature;
use App\Models\FormSubmission;
use App\Models\User;

/**
 * Pluggable signing backend.
 *
 * The default `InAppSignatureProvider` captures a hand-drawn signature on the
 * server and chains stage hashes for tamper detection. A future
 * `DocusignSignatureProvider` (or PandaDoc/SignNow) can implement this same
 * interface to delegate signing to a third-party envelope service without any
 * controller/UI changes upstream.
 */
interface SignatureProvider
{
    /**
     * Capture a signature for a given stage of a submission and persist it,
     * returning the saved FormSignature.
     *
     * @param  array<string, mixed> $payload  Stage data being signed (used for the payload hash).
     * @param  array<string, mixed> $context  ['signature_data' => base64 PNG, 'ip' => ..., 'user_agent' => ...].
     */
    public function sign(
        FormSubmission $submission,
        string $stageSlug,
        User $signer,
        array $payload,
        array $context = []
    ): FormSignature;

    /**
     * Provider identifier persisted on the signature row (e.g. 'in_app', 'docusign').
     */
    public function name(): string;
}
