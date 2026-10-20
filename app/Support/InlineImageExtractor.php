<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Converts inline base64 images (data:image/...;base64,...) into real files on
 * the public disk and rewrites the <img src> to the public URL.
 *
 * Why: DOMDocument/libxml silently drops *attribute values* that are very large,
 * so a pasted 800 KB base64 image comes back from the sanitizer as a bare <img>
 * with no source — the page then loses its image on the next save. Keeping
 * images as files also keeps the editor POST small (no more multi-MB payloads
 * hitting post_max_size).
 */
class InlineImageExtractor
{
    private const PATTERN = '#(<img\b[^>]*?\bsrc=")(data:image/(png|jpe?g|gif|webp);base64,([A-Za-z0-9+/=\s]+))(")#i';

    public static function extract(string $html, int $agencyId, string $disk = 'public'): string
    {
        if ($html === '' || ! str_contains($html, 'base64,')) {
            return $html;
        }

        $converted = preg_replace_callback(self::PATTERN, function (array $m) use ($agencyId, $disk) {
            $binary = base64_decode(preg_replace('/\s+/', '', $m[4]), true);

            if ($binary === false || $binary === '') {
                return $m[0];
            }

            $ext = strtolower($m[3]);
            $ext = $ext === 'jpeg' ? 'jpg' : $ext;
            $path = 'company-profile/'.$agencyId.'/'.Str::random(32).'.'.$ext;

            Storage::disk($disk)->put($path, $binary);

            return $m[1].'/storage/'.$path.$m[5];
        }, $html);

        return $converted ?? $html;
    }
}
