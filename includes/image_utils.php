<?php
// Image utilities for CampMart
// Usage: compressImage($srcPath, $destPath);

require_once __DIR__ . '/../config/config.php';

function compressImage($sourcePath, $destPath, $quality = IMAGE_COMPRESSION_QUALITY) {
    if (!file_exists($sourcePath)) {
        return false;
    }

    $info = getimagesize($sourcePath);
    if ($info === false) return false;

    $mime = $info['mime'];
    $width = $info[0];
    $height = $info[1];

    // Calculate new dimensions keeping aspect ratio
    $maxW = defined('MAX_IMAGE_WIDTH') ? MAX_IMAGE_WIDTH : 1600;
    $maxH = defined('MAX_IMAGE_HEIGHT') ? MAX_IMAGE_HEIGHT : 1600;

    $ratio = min($maxW / $width, $maxH / $height, 1);
    $newW = (int) round($width * $ratio);
    $newH = (int) round($height * $ratio);

    try {
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $src = imagecreatefromjpeg($sourcePath);
                $dst = imagecreatetruecolor($newW, $newH);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
                // Ensure destination directory exists
                if (!is_dir(dirname($destPath))) mkdir(dirname($destPath), 0755, true);
                imagejpeg($dst, $destPath, (int)$quality);
                imagedestroy($src);
                imagedestroy($dst);
                return true;

            case 'image/png':
                $src = imagecreatefrompng($sourcePath);
                $dst = imagecreatetruecolor($newW, $newH);
                // preserve transparency
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
                if (!is_dir(dirname($destPath))) mkdir(dirname($destPath), 0755, true);
                // PNG quality: 0 (no compression) to 9
                $pngQuality = (int) round((100 - $quality) / 11.1111); // convert 0-100 to 0-9 inverse
                imagepng($dst, $destPath, max(0, min(9, $pngQuality)));
                imagedestroy($src);
                imagedestroy($dst);
                return true;

            case 'image/gif':
                // For GIFs we will attempt to resize but animation will be lost; copy as-is if small
                if ($ratio === 1) {
                    if (!is_dir(dirname($destPath))) mkdir(dirname($destPath), 0755, true);
                    return copy($sourcePath, $destPath);
                }
                $src = imagecreatefromgif($sourcePath);
                $dst = imagecreatetruecolor($newW, $newH);
                // preserve transparency
                $transparent_index = imagecolortransparent($src);
                if ($transparent_index >= 0) {
                    $transparent_color = imagecolorsforindex($src, $transparent_index);
                    $transparent_index = imagecolorallocate($dst, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                    imagefill($dst, 0, 0, $transparent_index);
                    imagecolortransparent($dst, $transparent_index);
                }
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
                if (!is_dir(dirname($destPath))) mkdir(dirname($destPath), 0755, true);
                imagegif($dst, $destPath);
                imagedestroy($src);
                imagedestroy($dst);
                return true;

            default:
                // Unsupported mime type
                return false;
        }
    } catch (Exception $e) {
        return false;
    }
}
