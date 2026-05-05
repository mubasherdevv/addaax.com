<?php
/**
 * Image Compression Utility
 * Supports JPEG, PNG, WEBP and GIF
 */

function compressImage($source, $destination, $quality = 80) {
    $info = getimagesize($source);
    $mime = $info['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            // Handle transparency
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($source);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }

    // Optional: Resize if too large (e.g., max width 1200px)
    $max_width = 1200;
    $width = imagesx($image);
    $height = imagesy($image);

    if ($width > $max_width) {
        $new_width = $max_width;
        $new_height = ($height / $width) * $max_width;
        $tmp_img = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency for PNG/WebP
        if ($mime == 'image/png' || $mime == 'image/webp') {
            imagealphablending($tmp_img, false);
            imagesavealpha($tmp_img, true);
            $transparent = imagecolorallocatealpha($tmp_img, 255, 255, 255, 127);
            imagefill($tmp_img, 0, 0, $transparent);
        }

        imagecopyresampled($tmp_img, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagedestroy($image);
        $image = $tmp_img;
    }

    // Save as WebP for best compression (if server supports it)
    if (function_exists('imagewebp')) {
        // Change destination extension to .webp
        $dest_webp = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $destination);
        imagewebp($image, $dest_webp, $quality);
        imagedestroy($image);
        return basename($dest_webp);
    } else {
        // Fallback to original format
        if ($mime == 'image/jpeg') {
            imagejpeg($image, $destination, $quality);
        } elseif ($mime == 'image/png') {
            imagepng($image, $destination, round(9 * $quality / 100));
        } else {
            // Just move it if we can't compress it specifically
            move_uploaded_file($source, $destination);
            return basename($destination);
        }
        imagedestroy($image);
        return basename($destination);
    }
}
?>
