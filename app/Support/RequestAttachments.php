<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Multi-file attachments for Receivable / Expenses & Payments requests
 * (Cyd 2026-11-09, Mjolnir card "Upload and Edit").
 *
 * Rules from the card: a request carries 2-5 files, and a Requestor adding
 * files while the request is Pending may upload more.
 *
 * Interpretation: 5 is a MAXIMUM, not a minimum. A request with a single
 * receipt is legitimate, so nothing here forces a second file; the cap is what
 * is enforced, and the UI tells the user how many slots remain.
 */
class RequestAttachments
{
    /** Hard cap per request (card: "upload 2-5 files"). */
    public const MAX_PER_REQUEST = 5;

    /** Largest single file we accept, in kilobytes. */
    public const MAX_FILE_KB = 5120;

    /** Allowed extensions. */
    public const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];

    /** Validation rules for an `attachments[]` input. */
    public static function rules(): array
    {
        return [
            'attachments'   => ['nullable', 'array'],
            'attachments.*' => [
                'file',
                'max:'.self::MAX_FILE_KB,
                'mimes:'.implode(',', self::ALLOWED_EXTENSIONS),
            ],
        ];
    }

    /** How many slots are left on a request that already has $existing files. */
    public static function remaining(int $existing): int
    {
        return max(0, self::MAX_PER_REQUEST - $existing);
    }

    /**
     * Validate that $incoming files fit in the remaining slots.
     *
     * Returns an error message when they do not, or null when they fit.
     */
    public static function overflowMessage(int $existing, int $incoming): ?string
    {
        $remaining = self::remaining($existing);

        if ($incoming <= $remaining) {
            return null;
        }

        $label = $remaining === 1 ? '1 slot' : $remaining.' slots';

        return sprintf(
            'This request already has %d file(s). You can add %d more (%s left, %d maximum).',
            $existing,
            $remaining,
            $label,
            self::MAX_PER_REQUEST
        );
    }

    /**
     * Store the uploaded files and return their payloads.
     *
     * @param  UploadedFile[]  $files
     * @return array<int, array<string, mixed>>
     */
    public static function store(array $files, string $directory, ?int $userId = null): array
    {
        $payloads = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $payloads[] = [
                'path'          => $file->store($directory, 'public'),
                'original_name' => $file->getClientOriginalName(),
                'mime'          => $file->getClientMimeType(),
                'size'          => $file->getSize(),
                'uploaded_by'   => $userId,
            ];
        }

        return $payloads;
    }

    /** Public URL for an attachment path. */
    public static function url(?string $path): ?string
    {
        return $path ? Storage::url($path) : null;
    }
}
