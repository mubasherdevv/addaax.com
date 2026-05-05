<?php
/**
 * Common helper functions for the application
 */

/**
 * Format a date
 * 
 * @param string $date The date to format
 * @param string $format The format to use
 * @return string The formatted date
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Truncate text to a specific length
 * 
 * @param string $text The text to truncate
 * @param int $length The maximum length
 * @param string $append The string to append if truncated
 * @return string The truncated text
 */
function truncateText($text, $length = 100, $append = '...') {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . $append;
    }
    return $text;
}

/**
 * Creates a URL-friendly slug from a string
 */
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', ' ', $string);
    $string = preg_replace('/\s/', '-', $string);
    return trim($string, '-');
}

/**
 * Generates an SEO-friendly URL for a product
 */
function getProductUrl($id, $name) {
    $slug = createSlug($name);
    return "/ad/$slug-$id";
}

/**
 * Returns a human-readable "time ago" string
 */
function time_ago($timestamp) {
    if (!is_numeric($timestamp)) {
        $timestamp = strtotime($timestamp);
    }
    $diff = time() - $timestamp;
    
    if ($diff < 60) return "Just now";
    
    $intervals = [
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute'
    ];
    
    foreach ($intervals as $seconds => $label) {
        $count = floor($diff / $seconds);
        if ($count >= 1) {
            return ($label === 'day' ? $count . 'd' : $count . ' ' . $label . ($count > 1 ? 's' : '')) . ' ago';
        }
    }
}
 