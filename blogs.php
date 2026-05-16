<?php
session_start();
require 'includes/config.php';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle like/unlike
if (isset($_POST['like'], $_POST['blog_id'], $_SESSION['user_id'])) {
    $blog_id = intval($_POST['blog_id']);
    $user_id = intval($_SESSION['user_id']);

    $stmt = $conn->prepare("SELECT id FROM blog_likes WHERE blog_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $blog_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM blog_likes WHERE blog_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $blog_id, $user_id);
        $stmt->execute();
    } else {
        $created_at = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO blog_likes (blog_id, user_id, created_at) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $blog_id, $user_id, $created_at);
        $stmt->execute();
    }

    header('Location: blogs.php');
    exit();
}

// Handle ratings
if (isset($_POST['rate'], $_POST['rating'], $_POST['blog_id'], $_SESSION['user_id'])) {
    $blog_id = intval($_POST['blog_id']);
    $rating = intval($_POST['rating']);
    $user_id = intval($_SESSION['user_id']);
    $created_at = date('Y-m-d H:i:s');

    if ($rating >= 1 && $rating <= 5) {
        $stmt = $conn->prepare("
            INSERT INTO blog_ratings (blog_id, user_id, rating, created_at)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = VALUES(rating), created_at = VALUES(created_at)
        ");
        $stmt->bind_param("iiis", $blog_id, $user_id, $rating, $created_at);
        $stmt->execute();
    }

    header('Location: blogs.php');
    exit();
}

// Get all blog posts with like and rating data
$blogs = [];
$sql = "
    SELECT 
        b.*, 
        COUNT(DISTINCT l.id) AS likes_count,
        ROUND(AVG(r.rating), 1) AS avg_rating,
        COUNT(DISTINCT r.id) AS ratings_count
    FROM blogs b
    LEFT JOIN blog_likes l ON b.id = l.blog_id
    LEFT JOIN blog_ratings r ON b.id = r.blog_id
    GROUP BY b.id
    ORDER BY b.created_at DESC
";
$result = $conn->query($sql);
if ($result) {
    $blogs = $result->fetch_all(MYSQLI_ASSOC);
}

// Get user's likes
$user_likes = [];
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
    $stmt = $conn->prepare("SELECT blog_id FROM blog_likes WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $user_likes[] = $row['blog_id'];
    }
}

// Get user's ratings
$user_ratings = [];
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
    $stmt = $conn->prepare("SELECT blog_id, rating FROM blog_ratings WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $user_ratings[$row['blog_id']] = $row['rating'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Posts - FlexiCash</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-dark: #1a237e;
            --primary-light: #534bae;
            --accent-color: #ffd700;
            --text-light: #ffffff;
            --transition-speed: 0.3s;
            --header-height: 70px;
            --gradient-primary: linear-gradient(135deg, #1a237e, #534bae);
            --gradient-accent: linear-gradient(135deg, #ffd700, #ffa000);
            --shadow-soft: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-strong: 0 8px 32px rgba(0, 0, 0, 0.15);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f7;
            color: #333;
        }

        /* Header and Navigation Styles */
        .header {
            background: var(--gradient-primary);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: var(--shadow-soft);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo a {
            color: var(--text-light);
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: 700;
            transition: opacity var(--transition-speed);
        }

        .logo a:hover {
            opacity: 0.9;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-links a {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all var(--transition-speed);
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        

        /* Hamburger Menu */
        .hamburger {
            display: none;
            flex-direction: column;
            justify-content: space-between;
            width: 30px;
            height: 21px;
            cursor: pointer;
            background: transparent;
            border: none;
            padding: 0;
        }

        .hamburger span {
            display: block;
            height: 3px;
            width: 100%;
            background: white;
            border-radius: 3px;
            transition: all 0.3s ease;
        }

        .hamburger.open span:nth-child(1) {
            transform: translateY(9px) rotate(45deg);
        }

        .hamburger.open span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.open span:nth-child(3) {
            transform: translateY(-9px) rotate(-45deg);
        }

        /* Main Content Styles */
        .main-content {
            max-width: 1200px;
            margin: 7rem auto 2rem;
            padding: 0 1rem;
        }

        .main-content h1 {
            font-size: 2.5rem;
            color: var(--primary-dark);
            margin-bottom: 2rem;
            text-align: center;
        }

        /* Blog Grid Layout */
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        /* Blog Card Styles */
        .blog-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-strong);
        }

        .blog-card-image {
            width: 100%;
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .blog-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .blog-card:hover .blog-card-image img {
            transform: scale(1.05);
        }

        .blog-card-content {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .blog-card h2 {
            color: var(--primary-dark);
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
            line-height: 1.4;
        }

        .blog-meta {
            font-size: 0.875rem;
            color: #666;
            margin-bottom: 1rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .blog-meta span {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .blog-excerpt {
            color: #444;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .blog-full-content {
            color: #444;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            display: none; /* Ensure full content is hidden by default */
        }

        .blog-actions {
            margin-top: auto;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary-dark);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-light);
        }

        .btn-outline-primary {
            background-color: transparent;
            color: var(--primary-dark);
            border: 1px solid var(--primary-dark);
        }

        .btn-outline-primary:hover {
            background-color: rgba(26, 35, 126, 0.1);
        }

        .btn-sm {
            padding: 0.3rem 0.6rem;
            font-size: 0.875rem;
        }

        .rating-stars {
            display: flex;
            gap: 0.2rem;
            margin-top: 0.5rem;
        }

        .rating-stars .star {
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
        }

        .rating-stars .star.active {
            color: var(--accent-color);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .blog-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 992px) {
            .blog-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .blog-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--gradient-primary);
                padding: 1rem;
                flex-direction: column;
                gap: 0.5rem;
                box-shadow: var(--shadow-soft);
            }

            .nav-links.active {
                display: flex;
            }

            .hamburger {
                display: flex;
            }
        }
    </style>
</head>
<body>
  
<header class="header">
    <nav class="nav-container">
        <div class="logo">
            <h1><a href="index.php">FlexiCash</a></h1>
        </div>
        <button class="hamburger" id="hamburger" aria-label="Toggle navigation" type="button">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <div class="nav-links" id="navLinks">
            <?php if(isset($_SESSION['user_id'])) : ?>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="withdraw.php" class="nav-link">Withdraws</a>
                <a href="referrals.php" class="nav-link">Referrals</a>
                <a href="videos.php" class="nav-link">Video</a>
                <a href="logout.php" class="nav-link">Logout</a>
            <?php else : ?>
                <a href="index.php" class="nav-link">Home</a>
                <a href="blogs.php" class="nav-link active">Blogs</a>
                <a href="login.php" class="nav-link">Login</a>
                <a href="register.php" class="nav-link">Register</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<main class="main-content">
    <h1>Blog Posts</h1>
    
    <div class="blog-grid">
        <?php foreach ($blogs as $blog): ?>
        <div class="blog-card">
            <div class="blog-card-image">
                <?php if (!empty($blog['image_path'])): ?>
                    <img src="<?= htmlspecialchars($blog['image_path']) ?>" alt="<?= htmlspecialchars($blog['title']) ?>">
                <?php else: ?>
                    <img src="assets/images/default-blog.jpg" alt="Default blog image">
                <?php endif; ?>
            </div>
            <div class="blog-card-content">
                <h2><?= htmlspecialchars($blog['title']) ?></h2>
                <div class="blog-meta">
                    <span><i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($blog['created_at'])) ?></span>
                    <span><i class="fas fa-heart"></i> <?= $blog['likes_count'] ?> likes</span>
                    <span><i class="fas fa-star"></i> 
                        <?= $blog['avg_rating'] ? $blog['avg_rating'] . '/5' : 'No ratings' ?>
                    </span>
                </div>
                <div class="blog-excerpt">
                    <?php 
                    $content = strip_tags($blog['content']);
                    $excerpt = substr($content, 0, 150);
                    echo nl2br(htmlspecialchars($excerpt . (strlen($content) > 150 ? '...' : '')));
                    ?>
                </div>
                <div class="blog-full-content" id="content-<?= $blog['id'] ?>">
                    <?= nl2br(htmlspecialchars($blog['content'])) ?>
                </div>
                <div class="blog-actions">
                    <a href="full_blog.php?id=<?= $blog['id'] ?>" class="btn btn-primary">Read More</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Like Button -->
                        <form method="post" action="blogs.php" style="display:inline;">
                            <input type="hidden" name="blog_id" value="<?= $blog['id'] ?>">
                            <button type="submit" name="like" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-heart<?= in_array($blog['id'], $user_likes) ? '' : '-o' ?>"></i>
                                <?= in_array($blog['id'], $user_likes) ? 'Unlike' : 'Like' ?>
                            </button>
                        </form>
                        <!-- Rating Stars -->
                        <form method="post" action="blogs.php" style="display:inline;">
                            <input type="hidden" name="blog_id" value="<?= $blog['id'] ?>">
                            <input type="hidden" name="rate" value="1">
                            <span class="rating-stars">
                                <?php
                                $user_rating = isset($user_ratings[$blog['id']]) ? $user_ratings[$blog['id']] : 0;
                                for ($star = 1; $star <= 5; $star++): ?>
                                    <button type="submit" name="rating" value="<?= $star ?>" class="star<?= $star <= $user_rating ? ' active' : '' ?>" style="background:none;border:none;padding:0;cursor:pointer;">
                                        <i class="fas fa-star"></i>
                                    </button>
                                <?php endfor; ?>
                            </span>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<script>
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        const hamburger = document.getElementById('hamburger');
        const navLinks = document.getElementById('navLinks');

        function toggleMenu(e) {
            e.stopPropagation();
            navLinks.classList.toggle('active');
            hamburger.classList.toggle('open');
        }

        function closeMenu(e) {
            // Only close if a nav-link is clicked
            if (e.target.classList.contains('nav-link')) {
                navLinks.classList.remove('active');
                hamburger.classList.remove('open');
            }
        }

        hamburger.addEventListener('click', toggleMenu);
        navLinks.addEventListener('click', closeMenu);

        // Optional: Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navLinks.contains(e.target) && !hamburger.contains(e.target)) {
                navLinks.classList.remove('active');
                hamburger.classList.remove('open');
            }
        });
    });

    // Toggle content visibility
    function toggleContent(button, blogId) {
        const content = document.getElementById('content-' + blogId);
        const excerpt = content.parentElement.querySelector('.blog-excerpt');

        if (content.style.display === 'none' || content.style.display === '') {
            content.style.display = 'block';
            excerpt.style.display = 'none';
            button.textContent = 'Read Less';
        } else {
            content.style.display = 'none';
            excerpt.style.display = 'block';
            button.textContent = 'Read More';
        }
    }</script>
</body>
</html>