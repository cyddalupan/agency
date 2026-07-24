<?php

if (!function_exists('tenant_agency')) {
    function tenant_agency(): ?\App\Models\Agency
    {
        return app()->has('tenant_agency') ? app('tenant_agency') : null;
    }
}

if (!function_exists('is_tenant_request')) {
    function is_tenant_request(): bool
    {
        return tenant_agency() !== null;
    }
}

if (!function_exists('app_brand_name')) {
    function app_brand_name(): string
    {
        return config('app.universe', 1) == 2 ? 'LANDAS' : 'Agency Super';
    }
}

if (!function_exists('app_brand_icon')) {
    function app_brand_icon(): string
    {
        return config('app.universe', 1) == 2 ? '⛩️' : '⚡';
    }
}

if (!function_exists('app_brand_favicon_emoji')) {
    function app_brand_favicon_emoji(): string
    {
        return config('app.universe', 1) == 2 ? '⛩️' : '⚡';
    }
}

if (!function_exists('resolve_agency_id')) {
    function resolve_agency_id(): ?int
    {
        $user = auth()->user();
        if ($user && $user->agency_id) {
            return $user->agency_id;
        }
        $tenanted = tenant_agency();
        return $tenanted?->id;
    }
}

if (!function_exists('resize_and_save_photo')) {
    function resize_and_save_photo($file, string $directory = 'applicant-photos', int $maxDim = 600): string
    {
        $image = imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (!$image) {
            // Fallback: just store original
            return $file->store($directory, 'public');
        }

        $origW = imagesx($image);
        $origH = imagesy($image);

        // Only resize if larger than max dim
        if ($origW <= $maxDim && $origH <= $maxDim) {
            imagedestroy($image);
            return $file->store($directory, 'public');
        }

        $ratio = min($maxDim / $origW, $maxDim / $origH);
        $newW = (int) round($origW * $ratio);
        $newH = (int) round($origH * $ratio);

        $resized = imagecreatetruecolor($newW, $newH);

        // Preserve transparency for PNG
        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($image);

        // Save as JPEG for smaller size
        $filename = uniqid() . '_' . time() . '.jpg';
        $dir = storage_path('app/public/' . $directory);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $path = $directory . '/' . $filename;
        imagejpeg($resized, storage_path('app/public/' . $path), 85);
        imagedestroy($resized);

        return $path;
    }
}
