<?php

namespace App\Services\Memo;

use App\Models\EmailCampaign;
use App\Models\SystemLetterhead;
use App\Services\Pdf\AdobePdfService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Builds the print/export PDF for a memo (EmailCampaign): letterhead +
 * memo body + the full chat thread + inline images, with PDF/Word/Excel/
 * PowerPoint attachments converted (via Adobe PDF Services) and appended as
 * branded "annexes".
 *
 * This logic was originally inline in HomeController::exportMemoPdf. It lives
 * here so three callers can share one identical builder without duplication:
 *   1. the memo chat "Export & Print" button (HomeController::exportMemoPdf),
 *   2. the form-scoped "View approval" link (FormSubmissionController), which
 *      lets the form trail open the approving memo without being memo
 *      participants, and
 *   3. the frozen snapshot captured when a form is created from a memo.
 *
 * The class only renders — callers own authorization.
 */
class MemoExportService
{
    public function __construct(
        protected AdobePdfService $adobe,
    ) {
    }

    /**
     * Render the memo's export PDF and return the raw bytes.
     *
     * If the memo has convertible document attachments they are merged in as
     * annexes; otherwise the standalone memo PDF bytes are returned. On any
     * merge failure we fall back to the memo-only PDF so export never breaks.
     */
    public function pdfBytes(EmailCampaign $memo): string
    {
        $memo->load([
            'creator.position', 'creator.department', 'currentAssignee',
            'toRecipients.user', 'ccRecipients.user', 'recipients.user', 'replies.user',
        ]);

        // ── Letterhead ──
        $letterheadRecord = SystemLetterhead::findBySlug($memo->letterhead ?? null);

        // ── Adobe-backed annex pipeline ──
        // Images and plain text are rendered inline. PDF / Word / Excel /
        // PowerPoint attachments are converted to PDF and appended as
        // full-fidelity "annexes" at the end of the export, then merged into
        // one file. $annexes collects them in document order; $annexNo numbers
        // them so the inline reference matches the appended page. When Adobe is
        // not configured (or a file can't be converted) the attachment degrades
        // to a name-only listing — the export still works.
        $annexes = [];
        $annexNo = 0;

        // ── Shared attachment processor (used for both memo and reply attachments) ──
        $processAttachment = function (array $attachment, int $index, string $sourceLabel) use (&$annexes, &$annexNo): array {
            $filePath = storage_path('app/public/' . ($attachment['path'] ?? ''));
            $mime     = $attachment['type'] ?? '';
            $name     = $attachment['name'] ?? 'attachment';
            $entry    = [
                'index' => $index,
                'name'  => $name,
                'size'  => isset($attachment['size']) ? round($attachment['size'] / 1024, 1) . ' KB' : '',
                'mime'  => $mime,
                'type'  => 'unsupported',
                'data'  => null,
                'text'  => null,
            ];

            if (!file_exists($filePath)) {
                $entry['type'] = 'missing';
                return $entry;
            }

            if (str_starts_with($mime, 'image/')) {
                // Inline-renderable: embed image directly in the PDF
                $entry['type'] = 'image';
                $entry['data'] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($filePath));

            } elseif ($mime === 'text/plain') {
                $entry['type'] = 'text';
                $entry['text'] = htmlspecialchars(file_get_contents($filePath));

            } else {
                // PDF / Word / Excel / PowerPoint: convert (if needed) and append as an
                // annex. Falls back to a name-only listing if Adobe is unavailable or
                // the file type can't be converted.
                $entry['type'] = 'file';
                if ($this->adobe->enabled()) {
                    $pdfPath = $this->adobe->ensurePdf($filePath, $mime, $name);
                    if ($pdfPath) {
                        $annexNo++;
                        $annexes[] = [
                            'number' => $annexNo,
                            'label'  => $sourceLabel,
                            'name'   => $name,
                            'path'   => $pdfPath,
                        ];
                        $entry['type']         = 'annex';
                        $entry['annex_number'] = $annexNo;
                    }
                }
            }

            return $entry;
        };

        // ── Process memo-level attachments ──
        $processedAttachments = [];
        if ($memo->attachments) {
            foreach ($memo->attachments as $index => $attachment) {
                $processedAttachments[] = $processAttachment($attachment, $index, 'Memo Attachment');
            }
        }

        // ── Normalise a chat note for the print/export ──
        // Chat minutes are stored as on-screen HTML: icons8 <img> icons and
        // flexbox "pills" (the forward tag, the THROUGH badge, etc.). None of
        // that survives dompdf — external images don't load and the pills space
        // their parts with CSS `gap`, not real spaces, so once the styling is
        // gone the words fuse and any appended remark gets buried. This flattens
        // a note into clean, formal minute text: drop the icons, keep meaningful
        // line breaks, re-introduce a space at every tag boundary so nothing
        // fuses, and keep only light emphasis (bold/italic).
        $sanitizeMinuteForPdf = function (?string $html): string {
            $html = (string) $html;
            // Icons / avatars never render in the PDF.
            $html = preg_replace('/<img\b[^>]*>/i', '', $html);
            // Preserve breaks that carried meaning on screen (the divider <div>,
            // explicit <br>, paragraphs) as real newlines.
            $html = preg_replace('#<br\s*/?>#i', "\n", $html);
            $html = preg_replace('#</(div|p)>#i', "\n", $html);
            // Pills separated their parts with `gap`, not whitespace — add a
            // space at each tag boundary so "Bernard"+"Through"+"Nana" don't fuse.
            $html = preg_replace('/>\s*</', '> <', $html);
            // Flatten to text + light emphasis only (no colours, no flexbox).
            $html = strip_tags($html, '<strong><b><em><i>');
            // Tidy whitespace and collapse blank lines.
            $html = preg_replace('/[ \t]+/', ' ', $html);
            $html = preg_replace('/ *\n */', "\n", $html);
            $html = preg_replace('/\n{2,}/', "\n", $html);
            return trim($html);
        };

        // ── Process every reply and its attachments ──
        $processedReplies = [];
        foreach ($memo->replies->sortBy('created_at') as $reply) {
            $senderName = trim(($reply->user->first_name ?? '') . ' ' . ($reply->user->last_name ?? ''));
            if (!$senderName) $senderName = $reply->user->name ?? 'Unknown';

            $replyAttachments = [];
            if ($reply->attachments) {
                foreach ($reply->attachments as $index => $attachment) {
                    $replyAttachments[] = $processAttachment($attachment, $index, 'From ' . $senderName);
                }
            }

            $processedReplies[] = [
                'id'          => $reply->id,
                'sender'      => $senderName,
                'sent_at'     => $reply->created_at ? $reply->created_at->format('d M Y, H:i') : '',
                'message'     => $sanitizeMinuteForPdf($reply->message ?? ''),
                'attachments' => $replyAttachments,
            ];
        }

        // ── Letterhead as base64 for DomPDF ──
        $letterheadBase64 = null;
        if ($letterheadRecord) {
            try {
                $rawPath = $letterheadRecord->image_path;
                if (preg_match('#^https?://#i', $rawPath)) {
                    $imgData = @file_get_contents($rawPath);
                } else {
                    $abs = public_path($rawPath);
                    $imgData = file_exists($abs) ? file_get_contents($abs) : null;
                }
                if ($imgData) {
                    $letterheadBase64 = 'data:image/png;base64,' . base64_encode($imgData);
                }
            } catch (\Exception $e) {
                $letterheadBase64 = null;
            }
        }

        // ── Generate the memo PDF (memo content + chat thread + inline images) ──
        $pdf = Pdf::loadView('admin.uimms.memo-export-pdf', [
            'memo'                 => $memo,
            'letterheadBase64'     => $letterheadBase64,
            'hasLetterhead'        => (bool) $letterheadRecord,
            'processedAttachments' => $processedAttachments,
            'processedReplies'     => $processedReplies,
            'annexes'              => $annexes,
            'toRecipients'         => $memo->toRecipients,
            'ccRecipients'         => $memo->ccRecipients,
        ])->setPaper('a4', 'portrait');

        // No convertible document attachments → return the memo PDF as generated.
        if (empty($annexes)) {
            return $pdf->output();
        }

        // Merge the memo PDF with a branded divider sheet + the converted document for
        // each annex, in order. On any failure, fall back to the memo PDF alone so
        // export never breaks.
        $memoRef   = $memo->reference ?? ('MEMO/' . str_pad($memo->id, 4, '0', STR_PAD_LEFT));
        $tempFiles = [];   // files we created here and must clean up (NOT the cached annex PDFs)
        try {
            $tmpDir = storage_path('app/adobe-tmp');
            if (!is_dir($tmpDir)) {
                @mkdir($tmpDir, 0775, true);
            }

            $memoTmp = $tmpDir . '/memo-' . $memo->id . '-' . uniqid() . '.pdf';
            file_put_contents($memoTmp, $pdf->output());
            $tempFiles[] = $memoTmp;

            // Build the merge order: [ memo, divider 1, annex 1, divider 2, annex 2, … ]
            $mergeOrder = [$memoTmp];
            foreach ($annexes as $annex) {
                $divider = Pdf::loadView('admin.uimms.annex-divider', [
                    'number'           => $annex['number'],
                    'name'             => $annex['name'],
                    'label'            => $annex['label'],
                    'memoRef'          => $memoRef,
                    'letterheadBase64' => $letterheadBase64,
                    'hasLetterhead'    => (bool) $letterheadRecord,
                ])->setPaper('a4', 'portrait');

                $divTmp = $tmpDir . '/annex-' . $memo->id . '-' . $annex['number'] . '-' . uniqid() . '.pdf';
                file_put_contents($divTmp, $divider->output());
                $tempFiles[]  = $divTmp;

                $mergeOrder[] = $divTmp;
                $mergeOrder[] = $annex['path'];
            }

            $merged = $this->adobe->combine($mergeOrder);

            foreach ($tempFiles as $t) {
                @unlink($t);
            }

            return $merged;
        } catch (\Throwable $e) {
            Log::error('Memo export merge failed for memo ' . $memo->id . ': ' . $e->getMessage());
            foreach ($tempFiles as $t) {
                if (file_exists($t)) {
                    @unlink($t);
                }
            }
            return $pdf->output();
        }
    }

    /**
     * Default download/inline filename for a memo export.
     */
    public function filename(EmailCampaign $memo): string
    {
        return 'Memo-' . ($memo->reference ?? $memo->id) . '.pdf';
    }

    /**
     * Render the memo and return it as an inline (in-browser) PDF response.
     */
    public function stream(EmailCampaign $memo, ?string $filename = null): Response
    {
        $filename ??= $this->filename($memo);

        return response($this->pdfBytes($memo), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
