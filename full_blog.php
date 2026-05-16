<?php
session_start();
require 'includes/config.php';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$blog = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $blog_id = intval($_GET['id']);
    $stmt = $conn->prepare("
        SELECT 
            b.*, 
            COUNT(DISTINCT l.id) AS likes_count,
            ROUND(AVG(r.rating), 1) AS avg_rating,
            COUNT(DISTINCT r.id) AS ratings_count
        FROM blogs b
        LEFT JOIN blog_likes l ON b.id = l.blog_id
        LEFT JOIN blog_ratings r ON b.id = r.blog_id
        WHERE b.id = ?
        GROUP BY b.id
        LIMIT 1
    ");
    $stmt->bind_param("i", $blog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $blog = $result->fetch_assoc();
}

if (!$blog) {
    echo "<h2>Blog not found.</h2>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($blog['title']) ?> - FlexiCash Blog</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f7; margin: 0; padding: 0; }
        .container { max-width: 800px; margin: 3rem auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); padding: 2rem; }
        .blog-title { font-size: 2.2rem; color: #1a237e; margin-bottom: 1rem; }
        .blog-meta { color: #666; font-size: 0.95rem; margin-bottom: 1.5rem; }
        .blog-meta span { margin-right: 1.5rem; }
        .blog-image { width: 100%; max-height: 350px; object-fit: cover; border-radius: 8px; margin-bottom: 1.5rem; }
        .blog-content { color: #333; font-size: 1.1rem; line-height: 1.7; }
        .back-link { display: inline-block; margin-top: 2rem; color: #1a237e; text-decoration: none; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <a href="blogs.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Blogs</a>
        <h1 class="blog-title"><?= htmlspecialchars($blog['title']) ?></h1>
        <div class="blog-meta">
            <span><i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($blog['created_at'])) ?></span>
            <span><i class="fas fa-heart"></i> <?= $blog['likes_count'] ?> likes</span>
            <span><i class="fas fa-star"></i> <?= $blog['avg_rating'] ? $blog['avg_rating'] . '/5' : 'No ratings' ?> (<?= $blog['ratings_count'] ?>)</span>
        </div>
        <?php if (!empty($blog['image_path'])): ?>
            <img src="<?= htmlspecialchars($blog['image_path']) ?>" alt="<?= htmlspecialchars($blog['title']) ?>" class="blog-image">
        <?php else: ?>
            <img src="assets/images/default-blog.jpg" alt="Default blog image" class="blog-image">
        <?php endif; ?>
        <div class="blog-content">
            <?= nl2br(htmlspecialchars($blog['content'])) ?>
        </div>
    </div>
</body>
</html>