<?php
require_once 'config/core.php';

if (!file_exists('config/db.php')) {
    exit;
}

header("Content-Type: application/xml; charset=utf-8");
$site_url = "http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);

$stmt = $pdo->query("SELECT slug, updated_at FROM articles WHERE status = 'published' ORDER BY created_at DESC LIMIT 1000");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt_cats = $pdo->query("SELECT slug FROM categories");
$categories = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Home
echo "<url>\n";
echo "  <loc>{$site_url}/index.php</loc>\n";
echo "  <changefreq>hourly</changefreq>\n";
echo "  <priority>1.0</priority>\n";
echo "</url>\n";

// Categories
foreach ($categories as $cat) {
    echo "<url>\n";
    echo "  <loc>{$site_url}/category.php?slug=" . htmlspecialchars($cat['slug']) . "</loc>\n";
    echo "  <changefreq>daily</changefreq>\n";
    echo "  <priority>0.8</priority>\n";
    echo "</url>\n";
}

// Articles
foreach ($articles as $art) {
    echo "<url>\n";
    echo "  <loc>{$site_url}/article.php?slug=" . htmlspecialchars($art['slug']) . "</loc>\n";
    echo "  <lastmod>" . date('Y-m-d\TH:i:sP', strtotime($art['updated_at'])) . "</lastmod>\n";
    echo "  <changefreq>weekly</changefreq>\n";
    echo "  <priority>0.6</priority>\n";
    echo "</url>\n";
}

echo "</urlset>";
