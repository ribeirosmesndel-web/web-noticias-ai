<?php
// config/core.php
session_start();

// Define ABSPATH for absolute paths
define('ABSPATH', dirname(__DIR__) . '/');

// Include DB if config file exists
if (file_exists(ABSPATH . 'config/db.php')) {
    require_once ABSPATH . 'config/db.php';
}

/**
 * Basic Helper to sanitize inputs
 */
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Generates SEO friendly slug
 */
function generate_slug($text) {
    // Replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // Transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // Remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    // Trim
    $text = trim($text, '-');
    // Remove duplicate -
    $text = preg_replace('~-+~', '-', $text);
    // Lowercase
    $text = strtolower($text);

    return empty($text) ? 'n-a' : $text;
}

/**
 * Get setting value from DB
 */
function get_setting($pdo, $key, $default = '') {
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? $result : $default;
    } catch(PDOException $e) {
        return $default;
    }
}
