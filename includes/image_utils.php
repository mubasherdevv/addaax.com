<?php
/**
 * Image Compression Utility
 * Supports JPEG, PNG, WEBP and GIF
 */

function compressImage($source, $destination, $quality = 80, $apply_watermark = false) {
    $info = getimagesize($source);
    $mime = $info['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            imagepalettetotruecolor($image);
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

    if (!$image) return false;

    // Standardize to true color
    if (!imageistruecolor($image)) {
        $tmp = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagecopy($tmp, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        imagedestroy($image);
        $image = $tmp;
    }

    // 1. Resize if too large
    $max_width = 1200;
    $width = imagesx($image);
    $height = imagesy($image);

    if ($width > $max_width) {
        $new_width = $max_width;
        $new_height = ($height / $width) * $max_width;
        $tmp_img = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($tmp_img, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagedestroy($image);
        $image = $tmp_img;
        $width = $new_width;
        $height = $new_height;
    }

    // 2. Add Watermark (ONLY if requested)
   if ($apply_watermark) {
    $watermark_path = __DIR__ . '/../images/watermark.png';
    if (file_exists($watermark_path)) {
        $watermark = imagecreatefrompng($watermark_path);
        if ($watermark) {
            imagealphablending($watermark, true);
            imagesavealpha($watermark, true);

            $w_width  = imagesx($watermark);
            $w_height = imagesy($watermark);

            $target_w_width  = $width * 0.5;
            $target_w_height = ($w_height / $w_width) * $target_w_width;

            $dest_x = ($width  - $target_w_width)  / 2;
            $dest_y = ($height - $target_w_height) / 2;

            // Dark overlay — fixed
            $overlay = imagecreatetruecolor($width, $height);
            $black   = imagecolorallocate($overlay, 0, 0, 0);
            imagefill($overlay, 0, 0, $black);
            imagecopymerge($image, $overlay, 0, 0, 0, 0, $width, $height, 85); // 85% dark
            imagedestroy($overlay);

            // Watermark on top
            imagealphablending($image, true);
            imagecopyresampled(
                $image, $watermark,
                $dest_x, $dest_y,
                0, 0,
                $target_w_width, $target_w_height,
                $w_width, $w_height
            );
            imagedestroy($watermark);
        }
    }
}

    // 3. Save as WebP
    $dest_webp = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $destination);
    if (empty($dest_webp)) $dest_webp = $destination . '.webp';
    
    if (function_exists('imagewebp')) {
        imagewebp($image, $dest_webp, $quality);
        imagedestroy($image);
        return basename($dest_webp);
    } else {
        imagejpeg($image, $destination, $quality);
        imagedestroy($image);
        return basename($destination);
    }
}
?>
