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

// Get user data and referral code
$stmt = $conn->prepare("SELECT username, referral_code FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get referral statistics
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total_referrals,
    SUM(CASE WHEN has_deposited = 1 THEN 1 ELSE 0 END) as active_referrals,
    SUM(CASE WHEN has_deposited = 1 THEN commission ELSE 0 END) as total_earnings
    FROM referrals WHERE referrer_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$referral_stats = $result->fetch_assoc();

// Get referral list
$stmt = $conn->prepare("SELECT 
    u.username, u.email, u.created_at, r.has_deposited, r.commission
    FROM referrals r
    JOIN users u ON r.referred_id = u.id
    WHERE r.referrer_id = ?
    ORDER BY r.created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$referrals = $result->fetch_all(MYSQLI_ASSOC);

$page_title = "Referral Program";
include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Earnings Platform</title>
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1e3a8a;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --text-dark: #1f2937;
            --text-light: #f9fafb;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-color);
            color: var(--text-dark);
            line-height: 1.6;
        }

        /* Header Styles */
        .header {
            background-color: var(--primary-dark);
            color: white;
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 1.5rem;
        }

        .logo a {
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
        }

        .hamburger {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
        }

        .hamburger span {
            display: block;
            width: 25px;
            height: 3px;
            background: white;
            margin: 5px 0;
            transition: all 0.3s ease;
        }

        /* Main Content */
        .main-content {
            margin-top: 80px;
            padding: 2rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Cards */
        .card {
            background: var(--card-bg);
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 0.5rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: 0.5rem;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.875rem;
        }

        /* Referral Code */
        .referral-code-container {
            text-align: center;
            margin: 2rem 0;
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border-radius: 0.5rem;
            color: white;
        }

        .referral-code {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 2px;
            margin: 1rem 0;
            background: rgba(255,255,255,0.1);
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            display: inline-block;
        }

        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .table th {
            background-color: #f9fafb;
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-active {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-pending {
            background-color: #fef9c3;
            color: #854d0e;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                position: fixed;
                top: 70px;
                left: 0;
                width: 100%;
                background-color: var(--primary-dark);
                flex-direction: column;
                padding: 1rem;
                gap: 0.5rem;
                transform: translateY(-100%);
                opacity: 0;
                transition: all 0.3s ease;
                pointer-events: none;
            }

            .nav-links.open {
                transform: translateY(0);
                opacity: 1;
                pointer-events: all;
            }

            .nav-link {
                display: block;
                padding: 0.75rem;
            }

            .hamburger {
                display: block;
            }

            .hamburger.open span:nth-child(1) {
                transform: translateY(8px) rotate(45deg);
            }

            .hamburger.open span:nth-child(2) {
                opacity: 0;
            }

            .hamburger.open span:nth-child(3) {
                transform: translateY(-8px) rotate(-45deg);
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<header class="header">
        <nav class="nav-container">
            <div class="logo">
                <h1><a href="../index.php">FlexiCash</a></h1>
            </div>
            <button class="hamburger" id="hamburger" aria-label="Toggle navigation" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="nav-links" id="navLinks">
                <?php if(isset($_SESSION['user_id'])) : ?>
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="../blogs.php" class="nav-link">blogs</a>
                    <a href="withdraw.php" class="nav-link">Withdraws</a>
                    <a href="referrals.php" class="nav-link">Referrals</a>
                    <a href="videos.php" class="nav-link">Video</a>
                    <a href="logout.php" class="nav-link">Logout</a>
                <?php else : ?>
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <script type='text/javascript' src='//tiedexceed.com/02/35/03/023503a1e39817f04ce19849275c08da.js'></script>
    <main class="main-content">
        <h1>Referral Program</h1>
        
        <!-- Referral Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $referral_stats['total_referrals'] ?></div>
                <div class="stat-label">Total Referrals</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $referral_stats['active_referrals'] ?></div>
                <div class="stat-label">Active Referrals</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">₨<?= number_format($referral_stats['total_earnings'] ?? 0, 2) ?></div>
                <div class="stat-label">Total Earnings</div>
            </div>
        </div>
        
        <!-- Referral Code -->
        <div class="referral-code-container">
            <h2>Your Personal Referral Code</h2>
            <div class="referral-code" id="referralCode"><?= htmlspecialchars($user['referral_code']) ?></div>
            <button class="btn btn-primary" onclick="copyReferralCode()">Copy Referral Code</button>
            <p>Share this code with friends and earn 10% of their investments!</p>
        </div>
        
        <!-- How It Works -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">How It Works</h2>
            </div>
            <ol style="padding-left: 1.5rem; line-height: 2;">
                <li>Share your referral code with friends</li>
                <li>They sign up using your code</li>
                <li>When they make their first deposit, you earn 10% commission</li>
                <li>Commission is credited to your account immediately</li>
            </ol>
        </div>
        <script type='text/javascript' src='//tiedexceed.com/02/35/03/023503a1e39817f04ce19849275c08da.js'></script>  
        <!-- Referral List -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Your Referrals</h2>
            </div>
            <?php if (count($referrals) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Signup Date</th>
                            <th>Status</th>
                            <th>Your Earnings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($referrals as $referral): ?>
                            <tr>
                                <td><?= htmlspecialchars($referral['username']) ?></td>
                                <td><?= htmlspecialchars($referral['email']) ?></td>
                                <td><?= date('M d, Y', strtotime($referral['created_at'])) ?></td>
                                <td>
                                    <span class="status-badge <?= $referral['has_deposited'] ? 'status-active' : 'status-pending' ?>">
                                        <?= $referral['has_deposited'] ? 'Active' : 'Pending' ?>
                                    </span>
                                </td>
                                <td class="text-success"><?= $referral['has_deposited'] ? '₨' . number_format($referral['commission'], 2) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You haven't referred anyone yet. Share your referral code to start earning!</p>
            <?php endif; ?>
        </div>
    </main>
    <script type='text/javascript' src='//tiedexceed.com/02/35/03/023503a1e39817f04ce19849275c08da.js'></script>
    <script>
   document.addEventListener('DOMContentLoaded', function() {
        const hamburger = document.getElementById('hamburger');
        const navLinks = document.getElementById('navLinks');
        const body = document.body;

        function toggleMenu() {
            const isOpen = hamburger.classList.toggle('open');
            navLinks.classList.toggle('open');
            body.classList.toggle('menu-open');
            hamburger.setAttribute('aria-expanded', isOpen);
        }

        function closeMenu() {
            hamburger.classList.remove('open');
            navLinks.classList.remove('open');
            body.classList.remove('menu-open');
            hamburger.setAttribute('aria-expanded', 'false');
        }

        hamburger.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleMenu();
        });

        document.addEventListener('click', function(event) {
            if (!navLinks.contains(event.target) && !hamburger.contains(event.target)) {
                closeMenu();
            }
        });

        // Close menu on resize if screen becomes larger than mobile
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeMenu();
            }
        });
    });
    // Copy referral code function
    function copyReferralCode() {
        const code = document.getElementById('referralCode').textContent;
        navigator.clipboard.writeText(code)
            .then(() => {
                alert('Referral code copied to clipboard!');
            })
            .catch(err => {
                console.error('Failed to copy: ', err);
            });
    }
    </script>
</body>
</html>
<style>
    :root {
        --primary-dark: #1a237e;
        --primary-light: #534bae;
        --accent-color: #ffd700;
        --text-light: #ffffff;
        --transition-speed: 0.3s;
        --gradient-primary: linear-gradient(135deg, #1a237e, #534bae);
        --gradient-accent: linear-gradient(135deg, #ffd700, #ffa000);
        --shadow-soft: 0 4px 6px rgba(0, 0, 0, 0.1);
        --shadow-strong: 0 8px 32px rgba(0, 0, 0, 0.15);
    }

    /* Base Styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        line-height: 1.6;
        color: #333;
        background: #f8f9fa;
    }

    /* Main Content */
    .main-content {
        padding: 2rem;
        max-width: 1200px;
        margin: 5rem auto;
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

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        text-align: center;
        box-shadow: var(--shadow-soft);
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
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-strong);
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: bold;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0.5rem 0;
    }

    .stat-label {
        color: #718096;
        font-size: 0.875rem;
        font-weight: 500;
    }

    /* Referral Code Section */
    .referral-code-container {
        background: var(--gradient-primary);
        border-radius: 15px;
        padding: 3rem 2rem;
        text-align: center;
        color: white;
        margin: 2rem 0;
        box-shadow: var(--shadow-strong);
    }

    .referral-code {
        background: rgba(255, 255, 255, 0.1);
        padding: 1rem 2rem;
        border-radius: 10px;
        font-size: 2rem;
        font-weight: bold;
        letter-spacing: 2px;
        margin: 1.5rem 0;
        display: inline-block;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* Card Styles */
    .card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: var(--shadow-soft);
        margin-bottom: 2rem;
        transition: transform 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-strong);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 1rem;
    }

    .card-title {
        font-size: 1.5rem;
        color: var(--primary-dark);
        margin: 0;
    }

    /* Table Styles */
    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 1rem;
    }

    .table th,
    .table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    .table th {
        background: #f8fafc;
        font-weight: 600;
        color: var(--primary-dark);
        position: sticky;
        top: 0;
    }

    .table tr:hover td {
        background: #f8fafc;
    }

    /* Status Badges */
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
    }

    .status-active {
        background: #dcfce7;
        color: #166534;
    }

    .status-pending {
        background: #fef9c3;
        color: #854d0e;
    }

    /* Button Styles */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 1rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
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
            padding: 1.5rem;
            margin-top: 4rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .referral-code {
            font-size: 1.5rem;
            padding: 0.75rem 1.5rem;
        }

        .card {
            padding: 1.5rem;
        }

        .table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        .btn {
            width: 100%;
            margin: 0.5rem 0;
        }
    }

    @media screen and (max-width: 480px) {
        .main-content {
            padding: 1rem;
        }

        h1 {
            font-size: 2rem;
        }

        .stat-value {
            font-size: 2rem;
        }

        .referral-code {
            font-size: 1.25rem;
            padding: 0.5rem 1rem;
        }

        .card {
            padding: 1.25rem;
        }

        .status-badge {
            padding: 0.375rem 0.75rem;
        }
    }

    /* Touch Device Optimizations */
    @media (hover: none) {
        .btn {
            min-height: 44px;
        }

        .card:hover,
        .stat-card:hover {
            transform: none;
        }
    }
</style>