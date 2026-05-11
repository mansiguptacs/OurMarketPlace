<?php
/**
 * Resolve product image for display. Call after config/database.php is loaded (uses baseUrl).
 *
 * @return array{0: bool, 1: string} [ showImage, srcForImgTag ]
 */
function productImageForDisplay(?string $imageUrl): array
{
    $imageUrl = trim((string) $imageUrl);
    if ($imageUrl === '') {
        return [false, ''];
    }
    if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        return [true, $imageUrl];
    }
    $localPath = __DIR__ . '/../' . ltrim($imageUrl, '/');
    if (is_file($localPath)) {
        return [true, baseUrl('/' . ltrim($imageUrl, '/'))];
    }

    return [false, ''];
}
