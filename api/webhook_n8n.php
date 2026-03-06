<?php
// /api/webhook_n8n.php
require_once dirname(__DIR__) . '/config/core.php';

// Allow from any origin that n8n might use, or restrict if you know IP
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Optional: Simple Security Token
// Change this to something obscure or use a header bearer token
$secret_token = 'Mv3@AutomatedNews!2026';

// Check API Token
$headers = getallheaders();
$auth_token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : '';

if ($auth_token !== $secret_token) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get POST body JSON
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->title) &&
    !empty($data->content) &&
    !empty($data->category_slug)
) {
    try {
        // Resolve Category ID from Slug
        $stmt_cat = $pdo->prepare("SELECT id FROM categories WHERE slug = ? LIMIT 1");
        $stmt_cat->execute([$data->category_slug]);
        $category_id = $stmt_cat->fetchColumn();

        if (!$category_id) {
            // Default to 'Mundo' if category not found or create it dynamically (optional)
            $category_id = 1;
        }

        // Prepare Data
        $title = $data->title;
        $slug = generate_slug($title) . '-' . time(); // Append time to avoid duplicates
        $content = $data->content;
        $summary = !empty($data->summary) ? $data->summary : substr(strip_tags($content), 0, 150) . '...';
        $image_url = !empty($data->image_url) ? $data->image_url : null;
        $seo_title = !empty($data->seo_title) ? $data->seo_title : $title;
        $seo_description = !empty($data->seo_description) ? $data->seo_description : $summary;
        $seo_tags = !empty($data->seo_tags) ? $data->seo_tags : '';

        // Insert into DB
        $stmt = $pdo->prepare("INSERT INTO articles (title, slug, image_url, summary, content, category_id, ai_generated, seo_title, seo_description, seo_tags, status) VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?, 'published')");

        $stmt->execute([
            $title,
            $slug,
            $image_url,
            $summary,
            $content,
            $category_id,
            $seo_title,
            $seo_description,
            $seo_tags
        ]);

        http_response_code(201);
        echo json_encode(['message' => 'Article was created.', 'slug' => $slug]);

    } catch (PDOException $e) {
        http_response_code(503);
        echo json_encode(['error' => 'Unable to create article. Database Error: ' . $e->getMessage()]);
    }
} else {
    // Data is incomplete
    http_response_code(400);
    echo json_encode(['error' => 'Unable to create article. Data is incomplete.', 'data_received' => $data]);
}
?>