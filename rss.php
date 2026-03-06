<?php
require_once 'config/core.php';

if (!file_exists('config/db.php')) {
    exit;
}

header("Content-Type: application/rss+xml; charset=utf-8");

$site_name = get_setting($pdo, 'site_name', 'Automated News Portal');
$site_desc = get_setting($pdo, 'site_description', 'Últimas notícias');
$site_url = "http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);

$stmt = $pdo->query("SELECT a.*, c.name as cat_name FROM articles a LEFT JOIN categories c ON a.category_id = c.id WHERE a.status = 'published' ORDER BY a.created_at DESC LIMIT 20");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title>
            <?= htmlspecialchars($site_name) ?>
        </title>
        <link>
        <?= htmlspecialchars($site_url) ?>
        </link>
        <description>
            <?= htmlspecialchars($site_desc) ?>
        </description>
        <language>pt-br</language>
        <pubDate>
            <?= date('r') ?>
        </pubDate>
        <generator>AutomatedNews Portal Feed</generator>

        <?php foreach ($articles as $art): ?>
            <item>
                <title>
                    <?= htmlspecialchars($art['title']) ?>
                </title>
                <link>
                <?= htmlspecialchars($site_url) ?>/article.php?slug=
                <?= htmlspecialchars($art['slug']) ?>
                </link>
                <guid isPermaLink="true">
                    <?= htmlspecialchars($site_url) ?>/article.php?slug=
                    <?= htmlspecialchars($art['slug']) ?>
                </guid>
                <pubDate>
                    <?= date('r', strtotime($art['created_at'])) ?>
                </pubDate>
                <description>
                    <![CDATA[<?= htmlspecialchars($art['summary']) ?>]]>
                </description>
                <category>
                    <?= htmlspecialchars($art['cat_name'] ?? 'Notícia') ?>
                </category>
                <content:encoded>
                    <![CDATA[<?= nl2br($art['content']) ?>]]>
                </content:encoded>
                <?php if (!empty($art['image_url'])): ?>
                    <enclosure url="<?= htmlspecialchars($art['image_url']) ?>" type="image/jpeg" />
                <?php endif; ?>
            </item>
        <?php endforeach; ?>

    </channel>
</rss>