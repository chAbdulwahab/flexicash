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

// Get user data
$stmt = $conn->prepare("SELECT username, referral_code, balance FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get active investments
$stmt = $conn->prepare("SELECT p.name, p.investment, p.daily_earnings_percent, 
                      i.start_date, i.end_date 
                      FROM investments i
                      JOIN plans p ON i.plan_id = p.id
                      WHERE i.user_id = ? AND i.is_active = 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$active_investments = $result->fetch_all(MYSQLI_ASSOC);

// Get recent earnings
$stmt = $conn->prepare("SELECT amount, created_at FROM earnings 
                      WHERE user_id = ? 
                      ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$recent_earnings = $result->fetch_all(MYSQLI_ASSOC);

$page_title = "Dashboard - Earnings Platform";
include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <style>
    :root {
        --primary-dark: #1a237e;
        --primary-light: #534bae;
        --accent-color: #ffd700;
        --text-light: #ffffff;
        --transition-speed: 0.3s;
        --gradient-primary: linear-gradient(135deg, #1a237e, #534bae);
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
        max-width: 1400px;
        margin: 5rem auto;
        animation: fadeIn 0.5s ease;
    }

    h1 {
        font-size: 2.5rem;
        color: var(--primary-dark);
        margin-bottom: 2rem;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Dashboard Grid */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    /* Card Styles */
    .card {
        background: white;
        border-radius: 15px;
        box-shadow: var(--shadow-soft);
        padding: 2rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .card::before {
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

    .card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-strong);
    }

    .card:hover::before {
        opacity: 1;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .card-title {
        font-size: 1.25rem;
        color: var(--primary-dark);
        font-weight: 600;
    }

    /* Statistics */
    .stat-value {
        font-size: 2.5rem;
        font-weight: bold;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: #718096;
        font-size: 0.875rem;
    }

    .text-success {
        color: #48bb78;
        font-weight: 500;
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
        border-bottom: 1px solid #e2e8f0;
    }

    .table th {
        background: #f7fafc;
        font-weight: 600;
        color: var(--primary-dark);
        position: sticky;
        top: 0;
    }

    .table tr:last-child td {
        border-bottom: none;
    }

    .table tr:hover td {
        background: #f8fafc;
    }

    /* Button Styles */
    .btn {
        display: inline-block;
        padding: 0.875rem 1.5rem;
        border-radius: 10px;
        font-weight: 500;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        border: none;
        box-shadow: 0 2px 4px rgba(26, 35, 126, 0.1);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 35, 126, 0.2);
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Responsive Design */
    @media screen and (max-width: 1200px) {
        .dashboard-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media screen and (max-width: 768px) {
        .main-content {
            padding: 1.5rem;
            margin-top: 4rem;
        }

        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .card {
            padding: 1.5rem;
        }

        .stat-value {
            font-size: 2rem;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }

        .table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        .table th,
        .table td {
            padding: 0.875rem;
        }
    }

    @media screen and (max-width: 480px) {
        .main-content {
            padding: 1rem;
            margin-top: 3.5rem;
        }

        .card {
            padding: 1.25rem;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .stat-value {
            font-size: 1.75rem;
        }

        h1 {
            font-size: 1.75rem;
        }

        .btn {
            width: 100%;
            padding: 0.75rem 1rem;
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
                    <a href="blogs.php" class="nav-link">blogs</a>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <script type='text/javascript' src='//tiedexceed.com/02/35/03/023503a1e39817f04ce19849275c08da.js'></script>
    <main class="main-content">
        <h1>Welcome, <?= htmlspecialchars($user['username']) ?></h1>

        <div class="dashboard-grid">
            <!-- Balance Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Account Balance</h2>
                </div>
                <div class="stat-value">₨<?= number_format($user['balance'], 2) ?></div>
                <p class="stat-label">Available for withdrawal</p>
                <a href="withdraw.php" class="btn btn-primary" style="margin-top: 1rem;">Withdraw Funds</a>
            </div>

            <!-- Referral Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Referral Program</h2>
                </div>
                <div class="stat-value text-success">10%</div>
                <p class="stat-label">Commission rate</p>
                <p>Your code: <strong><?= htmlspecialchars($user['referral_code']) ?></strong></p>
                <a href="referrals.php" class="btn btn-primary" style="margin-top: 1rem;">View Referrals</a>
            </div>

            <!-- Quick Actions Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quick Actions</h2>
                </div>
                <div style="display: grid; gap: 0.75rem;">
                    <a href="invest.php" class="btn btn-primary">Deposit Funds</a>
                    <a href="videos.php" class="btn btn-primary">videos</a>
                    <a href="profile.php" class="btn btn-primary">View Profile</a>
                </div>
            </div>
        </div>

        <script type='text/javascript' src='//tiedexceed.com/02/35/03/023503a1e39817f04ce19849275c08da.js'></script>   <!-- Active Investments -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h2 class="card-title">Active Investments</h2>
                <a href="invest.php" class="btn btn-primary">+ New Investment</a>
            </div>
            <?php if (count($active_investments) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Daily Earnings</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($active_investments as $investment): ?>
                            <tr>
                                <td><?= htmlspecialchars($investment['name']) ?></td>
                                <td>₨<?= number_format($investment['investment'], 2) ?></td>
                                <td class="text-success">₨<?= number_format($investment['investment'] * ($investment['daily_earnings_percent'] / 100), 2) ?></td>
                                <td><?= date('M d, Y', strtotime($investment['start_date'])) ?></td>
                                <td><?= date('M d, Y', strtotime($investment['end_date'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You don't have any active investments yet.</p>
                <a href="invest.php" class="btn btn-primary">Start Investing</a>
            <?php endif; ?>
        </div>
        <script type='text/javascript' src='//tiedexceed.com/02/35/03/023503a1e39817f04ce19849275c08da.js'></script>
        <!-- Recent Earnings -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Recent Earnings</h2>
            </div>
            <?php if (count($recent_earnings) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_earnings as $earning): ?>
                            <tr>
                                <td class="text-success">+₨<?= number_format($earning['amount'], 2) ?></td>
                                <td><?= date('M d, Y h:i A', strtotime($earning['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No earnings recorded yet.</p>
            <?php endif; ?>
        </div>
    </main>
    <script type='text/javascript' src='//pl26605480.profitableratecpm.com/be/c2/be/bec2be69d28c8e8fe6dbbe1820c6172a.js'></script>
    <script type='text/javascript' src='//pl26605480.profitableratecpm.com/be/c2/be/bec2be69d28c8e8fe6dbbe1820c6172a.js'></script>
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
    
    </script>
    <?php include '../includes/footer.php'; ?>
</body>

</html>