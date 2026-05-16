<?php
session_start();
require_once '../includes/config.php';

// Initialize MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

/**
 * Improved YouTube video ID extraction
 * Handles various YouTube URL formats
 */
function extractYouTubeID($url) {
    // Standardize URL format first
    $url = trim($url);
    $url = str_replace(['http://', 'https://'], '', $url);
    
    // Common URL patterns
    $patterns = [
        'youtube\.com/watch\?v=([^&]+)',
        'youtube\.com/embed/([^/?]+)',
        'youtu\.be/([^/?]+)',
        'youtube\.com/v/([^/?]+)',
        'youtube\.com/shorts/([^/?]+)'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match("#{$pattern}#i", $url, $matches)) {
            return $matches[1];
        }
    }
    
    return '';
}

/**
 * Gets last watched time for a user's plan
 */
function getLastWatchedTime($conn, $userId, $planId) {
    $stmt = $conn->prepare("SELECT watched_at FROM video_watches WHERE user_id = ? AND plan_id = ? ORDER BY watched_at DESC LIMIT 1");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return null;
    }
    $stmt->bind_param("ii", $userId, $planId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_column() : null;
}

// Fetch videos for active investments
$stmt = $conn->prepare("
    SELECT p.id, p.name, p.video_duration, y.video_url 
    FROM investments i
    JOIN plans p ON i.plan_id = p.id
    JOIN youtube_videos y ON y.plan_id = p.id
    WHERE i.user_id = ? AND i.is_active = 1
");
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$videos = $result->fetch_all(MYSQLI_ASSOC);

// Select next available video
$currentVideo = null;
foreach ($videos as $video) {
    $lastWatched = getLastWatchedTime($conn, $_SESSION['user_id'], $video['id']);
    if ($lastWatched === null || (time() - strtotime($lastWatched)) >= 86400) {
        $videoId = extractYouTubeID($video['video_url']);
        if ($videoId) {
            $video['video_id'] = $videoId;
            $currentVideo = $video;
            break;
        }
    }
}

$page_title = "Videos - Earnings Platform";
include '../includes/header.php';
?>
<script type='text/javascript' src='//tiedexceed.com/02/35/03/023503a1e39817f04ce19849275c08da.js'></script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Watch videos and earn rewards">
    <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></title>

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

        /* Base Styles */
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

        /* Video Card Styles */
        .card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-strong);
        }

        .card h2 {
            color: var(--primary-dark);
            margin-bottom: 1.5rem;
            font-size: 1.75rem;
        }

        /* Video Container */
        .video-container {
            position: relative;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background: #f8fafc;
            border-radius: 10px;
            padding: 1.5rem;
        }

        #ytplayer {
            width: 100%;
            aspect-ratio: 16/9;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-soft);
        }

        /* Progress Bar */
        .progress-container {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin: 1rem 0;
        }

        .progress-bar {
            width: 0%;
            height: 100%;
            background: var(--gradient-primary);
            transition: width 1s linear;
        }

        /* Countdown and Status */
        .countdown {
            font-size: 1.25rem;
            font-weight: bold;
            color: var(--primary-dark);
        }

        .video-unavailable {
            color: #dc3545;
            font-weight: 500;
            text-align: center;
            padding: 2rem;
            background: #fff5f5;
            border-radius: 10px;
            border: 1px solid #feb2b2;
        }

        /* Cooldown Timer */
        .cooldown-timer {
            text-align: center;
            font-size: 1.25rem;
            color: var(--primary-dark);
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 10px;
            margin-top: 1rem;
        }

        /* Button Styles */
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            border: none;
            box-shadow: 0 4px 6px rgba(26, 35, 126, 0.2);
        }

        .btn-success {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            border: none;
            box-shadow: 0 4px 6px rgba(5, 150, 105, 0.2);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(26, 35, 126, 0.3);
        }

        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .claim-btn {
            width: 100%;
            margin-top: 1rem;
            font-size: 1.125rem;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .main-content {
                padding: 1rem;
                margin-top: 4rem;
            }

            h1 {
                font-size: 2rem;
            }

            .card {
                padding: 1.5rem;
            }

            .video-container {
                padding: 1rem;
            }

            .countdown {
                font-size: 1.125rem;
            }
        }

        @media screen and (max-width: 480px) {
            .main-content {
                margin-top: 3.5rem;
            }

            h1 {
                font-size: 1.75rem;
            }

            .card {
                padding: 1.25rem;
            }

            .btn {
                padding: 0.875rem 1.5rem;
                font-size: 1rem;
            }

            .cooldown-timer {
                font-size: 1rem;
                padding: 1rem;
            }
        }

        /* Touch Device Optimizations */
        @media (hover: none) {
            .btn {
                min-height: 44px;
            }

            .card:hover {
                transform: none;
            }
        }
    </style>
</head>
<body>
<header class="header">
    <nav class="nav-container">
        <div class="logo"><a href="../index.php">FlexiCash</a></div>
        <button class="hamburger" id="hamburger" aria-label="Toggle navigation">
            <span></span><span></span><span></span>
        </button>
        <div class="nav-links" id="navLinks">
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="../blogs.php" class="nav-link">blogs</a>
            <a href="referrals.php" class="nav-link">Referrals</a>
            <a href="invest.php" class="nav-link">Invest</a>
            <a href="videos.php" class="nav-link active">Videos</a>
            <a href="../logout.php" class="nav-link">Logout</a>
        </div>
    </nav>
</header>
<script type='text/javascript' src='//tiedexceed.com/02/35/03/023503a1e39817f04ce19849275c08da.js'></script>
<main class="main-content">
    <h1>Watch & Earn</h1>

    <?php if ($currentVideo): ?>
        <div class="card video-task" data-plan-id="<?= htmlspecialchars($currentVideo['id'], ENT_QUOTES, 'UTF-8') ?>" 
             data-duration="<?= intval($currentVideo['video_duration']) ?>">
            <h2><?= htmlspecialchars($currentVideo['name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <div class="video-container">
                <?php if (!empty($currentVideo['video_id'])): ?>
                    <div id="ytplayer"></div>
                    <p>Watch for <span class="countdown"><?= intval($currentVideo['video_duration']) ?></span> seconds to claim your reward</p>
                    <button class="claim-btn btn btn-success" style="display:none;">Claim Reward</button>
                    <div class="progress-container">
                        <div class="progress-bar"></div>
                    </div>
                <?php else: ?>
                    <p class="video-unavailable">This video is currently unavailable. Please try another one.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif (count($videos) > 0): ?>
        <div class="card">
            <h2>All Videos Completed</h2>
            <p>You've watched all videos for today. Please return when the cooldown expires.</p>
            <?php
            // Get the most recent watch time for any of the user's plans
            $stmt = $conn->prepare("SELECT watched_at FROM video_watches 
                                  WHERE user_id = ? AND plan_id IN (" . implode(',', array_column($videos, 'id')) . ") 
                                  ORDER BY watched_at DESC LIMIT 1");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $lastWatch = $stmt->get_result()->fetch_column();
            if ($lastWatch): 
                $resetTime = strtotime($lastWatch) + 86400; // 24 hours from last watch
            ?>
                <div class="cooldown-timer" data-reset-time="<?= $resetTime ?>"></div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <h2>No Videos Available</h2>
            <p>You don't have any active investment plans with video tasks.</p>
            <a href="invest.php" class="btn btn-primary">Explore Plans</a>
        </div>
    <?php endif; ?>
</main>
<script type='text/javascript' src='//tiedexceed.com/02/35/03/023503a1e39817f04ce19849275c08da.js'></script>
<script>
    // Hamburger menu toggle
    document.getElementById('hamburger').addEventListener('click', function() {
        this.classList.toggle('open');
        document.getElementById('navLinks').classList.toggle('open');
    });

    // Video reward system
    document.addEventListener('DOMContentLoaded', function() {
        const videoCard = document.querySelector('.video-task');
        if (!videoCard) {
            // Handle cooldown timer if needed
            const cooldownElement = document.querySelector('.cooldown-timer');
            if (cooldownElement) {
                updateCooldownTimer(parseInt(cooldownElement.dataset.resetTime));
            }
            return;
        }

        // Player variables
        let player;
        let isPlaying = false;
        let timer = null;
        const duration = parseInt(videoCard.dataset.duration);
        let secondsLeft = duration;
        const videoId = "<?= $currentVideo['video_id'] ?? '' ?>";

        // Load YouTube API if not already loaded
        if (!window.YT) {
            const tag = document.createElement('script');
            tag.src = "https://www.youtube.com/iframe_api";
            const firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        }

        // Called when YouTube API is ready
        window.onYouTubeIframeAPIReady = function() {
            if (!videoId) return;
            
            player = new YT.Player('ytplayer', {
                height: '315',
                width: '560',
                videoId: videoId,
                playerVars: {
                    'enablejsapi': 1,
                    'rel': 0,
                    'origin': window.location.origin,
                    'autoplay': 0,
                    'controls': 1
                },
                events: {
                    'onReady': onPlayerReady,
                    'onStateChange': onPlayerStateChange,
                    'onError': onPlayerError
                }
            });
        };

        function onPlayerReady(event) {
            console.log('YouTube player ready');
            // Ensure player is not muted
            if (event.target.isMuted()) {
                event.target.unMute();
            }
        }

        function onPlayerError(event) {
            console.error('YouTube Player Error:', event.data);
            stopTimer();
            alert('Error loading video. Please try another one.');
        }

        function onPlayerStateChange(event) {
            switch(event.data) {
                case YT.PlayerState.PLAYING:
                    if (!isPlaying) {
                        isPlaying = true;
                        startTimer();
                    }
                    break;
                case YT.PlayerState.PAUSED:
                case YT.PlayerState.ENDED:
                    isPlaying = false;
                    stopTimer();
                    break;
            }
        }

        function startTimer() {
            if (timer) clearInterval(timer);
            
            timer = setInterval(() => {
                if (!player || !isPlaying) {
                    stopTimer();
                    return;
                }

                secondsLeft--;
                document.querySelector('.countdown').textContent = secondsLeft;
                
                // Update progress bar
                const progress = ((duration - secondsLeft) / duration) * 100;
                document.querySelector('.progress-bar').style.width = `${progress}%`;

                if (secondsLeft <= 0) {
                    stopTimer();
                    document.querySelector('.claim-btn').style.display = 'inline-block';
                    // Remove or comment out the next line to prevent auto-pausing the video
                    // if (player.pauseVideo) {
                    //     player.pauseVideo();
                    // }
                }
            }, 1000);
        }

        function stopTimer() {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        }

        // Claim button handler
        document.querySelector('.claim-btn')?.addEventListener('click', function() {
            if (this.disabled) return;
            
            this.disabled = true;
            this.textContent = "Processing...";
            
            fetch('claim_reward.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `plan_id=${encodeURIComponent(videoCard.dataset.planId)}`
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Reward claimed successfully!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    throw new Error(data.message || 'Failed to claim reward');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.disabled = false;
                this.textContent = "Claim Reward";
                alert(error.message);
            });
        });

        // Cooldown timer update
        function updateCooldownTimer(resetTime) {
            const timerElement = document.querySelector('.cooldown-timer');
            if (!timerElement) return;

            function update() {
                const now = Math.floor(Date.now() / 1000);
                const diff = resetTime - now;
                
                if (diff <= 0) {
                    timerElement.innerHTML = '<p>You can now watch videos again!</p>';
                    setTimeout(() => location.reload(), 2000);
                    return;
                }
                
                const hours = Math.floor(diff / 3600);
                const minutes = Math.floor((diff % 3600) / 60);
                const seconds = diff % 60;
                
                timerElement.innerHTML = `
                    <p>Next video available in: 
                    ${hours.toString().padStart(2, '0')}h 
                    ${minutes.toString().padStart(2, '0')}m 
                    ${seconds.toString().padStart(2, '0')}s</p>
                `;
                
                setTimeout(update, 1000);
            }
            
            update();
        }
    });
    
</script>
<script type='text/javascript' src='//tiedexceed.com/02/35/03/023503a1e39817f04ce19849275c08da.js'></script>
<script type='text/javascript' src='//tiedexceed.com/02/35/03/023503a1e39817f04ce19849275c08da.js'></script>
<script type='text/javascript' src='//pl26605480.profitableratecpm.com/be/c2/be/bec2be69d28c8e8fe6dbbe1820c6172a.js'></script>
</body>
</html>
