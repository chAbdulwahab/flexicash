<?php
session_start();
require '../includes/config.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Initialize MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_video'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $url = $conn->real_escape_string($_POST['url']);
        $user_id = $_SESSION['user_id'];
        
        $stmt = $conn->prepare("INSERT INTO youtube_videos (title, video_url, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $url, $user_id);
        $stmt->execute();
    } elseif (isset($_POST['delete_video'])) {
        $id = intval($_POST['video_id']);
        $conn->query("DELETE FROM youtube_videos WHERE id = $id");
    }
}

// Get all videos
$videos = $conn->query("SELECT * FROM youtube_videos ORDER BY id DESC");

$page_title = "Video Management - Admin Panel";

?>
 <header class="header">
        <nav class="nav-container">
            <div class="logo">
                <a href="../index.php">EarningsPK</a>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="blogs.php" class="nav-link active">blogs</a>
                <a href="users.php" class="nav-link">Users</a>
                <a href="deposits.php" class="nav-link">Deposits</a>
                <a href="videos.php" class="nav-link">videos</a>
                <a href="withdrawals.php" class="nav-link">withdraws</a>
                <a href="../logout.php" class="nav-link">Logout</a>
            </div>
        </nav>
    </header>
    <style>
        :root {
            --primary-dark: #1a237e;
            --primary-light: #534bae;
            --accent-color: #ffd700;
            --text-light: #ffffff;
            --gradient-primary: linear-gradient(135deg, #1a237e, #534bae);
            --gradient-accent: linear-gradient(135deg, #ffd700, #ffa000);
            --shadow-soft: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-strong: 0 8px 32px rgba(0, 0, 0, 0.15);
        }
        
        .main-content {
            max-width: 1200px;
            margin: 5rem auto 2rem;
            padding: 0 1.5rem;
            animation: fadeIn 0.5s ease;
        }
        
        h1 {
            font-size: 2.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2rem;
        }
        
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: var(--shadow-soft);
            text-align: center;
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-primary);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-strong);
        }
        
        .stat-card:hover::before {
            opacity: 1;
        }
        
        .stat-card h3 {
            color: var(--primary-dark);
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 1rem 0;
        }
        
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            margin-top: 1rem;
        }
        
        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            border: none;
            box-shadow: 0 4px 6px rgba(26, 35, 126, 0.2);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(26, 35, 126, 0.3);
        }
        
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .main-content {
                margin-top: 4rem;
                padding: 0 1rem;
            }
        
            h1 {
                font-size: 2rem;
            }
        
            .stat-value {
                font-size: 2rem;
            }
        
            .hamburger {
                display: flex;
            }
        
            .nav-links {
                display: none;
                position: fixed;
                top: 60px;
                left: 0;
                right: 0;
                background: white;
                padding: 1rem;
                flex-direction: column;
                box-shadow: var(--shadow-soft);
            }
        
            .nav-links.open {
                display: flex;
            }
        
            .hamburger.open span:first-child {
                transform: rotate(45deg);
            }
        
            .hamburger.open span:nth-child(2) {
                opacity: 0;
            }
        
            .hamburger.open span:last-child {
                transform: rotate(-45deg);
            }
        }
        
        @media screen and (max-width: 480px) {
            .main-content {
                margin-top: 3.5rem;
            }
        
            h1 {
                font-size: 1.75rem;
            }
        
            .stat-value {
                font-size: 1.75rem;
            }
        
            .btn {
                padding: 0.875rem 1.5rem;
                width: 100%;
            }
        }
        
        /* Touch Device Optimizations */
        @media (hover: none) {
            .btn {
                min-height: 44px;
            }
        
            .stat-card:hover {
                transform: none;
            }
        
            .btn:hover {
                transform: none;
            }
        
            .nav-link {
                min-height: 44px;
                display: flex;
                align-items: center;
            }
        }
    </style>

<div class="main-content">
    <h1>Video Management</h1>
    
    <!-- Add Video Form -->
    <div class="card" style="margin-bottom: 2rem;">
        <h2>Add New Video</h2>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">YouTube URL</label>
                <input type="url" name="url" class="form-input" placeholder="https://youtube.com/..." required>
            </div>
            <button type="submit" name="add_video" class="btn btn-primary">Add Video</button>
        </form>
    </div>
    
    <!-- Videos List -->
    <div class="card">
        <h2>Current Videos</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>URL</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($videos->num_rows > 0): ?>
                    <?php while ($video = $videos->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($video['video_url']) ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="video_id" value="<?= $video['id'] ?>">
                                    <button type="submit" name="delete_video" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3">No videos found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>