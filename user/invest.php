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

// Get available investment plans
$stmt = $conn->prepare("SELECT * FROM plans ORDER BY investment ASC");
$stmt->execute();
$result = $stmt->get_result();
$plans = $result->fetch_all(MYSQLI_ASSOC);

// Get user balance
$stmt = $conn->prepare("SELECT total_deposits FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user_balance = $result->fetch_column();

$page_title = "Invest - Earnings Platform";
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

        /* Balance Info */
        .balance-info {
            background-color: var(--card-bg);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .balance-amount {
            font-size: 1.5rem;
            font-weight: 700;
        }

        /* Plans Grid */
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        /* Plan Card */
        .plan-card {
            background: var(--card-bg);
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 2px solid #e5e7eb;
        }

        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }

        .plan-card.recommended {
            border-color: var(--success-color);
            position: relative;
        }

        .recommended-badge {
            position: absolute;
            top: -10px;
            right: 20px;
            background-color: var(--success-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .plan-name {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .plan-price {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .plan-features {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }

        .plan-features li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
        }

        .plan-features li:before {
            content: "✓";
            color: var(--success-color);
            margin-right: 0.5rem;
            font-weight: bold;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            width: calc(100% - 3rem); /* Account for card padding */
            margin-top: 1rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: #e5e7eb;
            color: var(--text-dark);
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
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

            .plans-grid {
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
    <main class="main-content">
        <h1>Investment Plans</h1>
        
        <div class="balance-info">
            <div>
                <h2>Your Balance</h2>
                <p class="balance-amount">₨<?= number_format($user_balance, 2) ?></p>
            </div>
            <a href="deposit.php" class="btn btn-primary">Deposit Funds</a>
        </div>
        
        <div class="plans-grid">
            <?php foreach ($plans as $plan): ?>
                <div class="plan-card <?= $plan['id'] == 3 ? 'recommended' : '' ?>">
                    <?php if ($plan['id'] == 3): ?>
                        <span class="recommended-badge">Recommended</span>
                    <?php endif; ?>
                    <h3 class="plan-name"><?= htmlspecialchars($plan['name']) ?></h3>
                    <div class="plan-price">₨<?= number_format($plan['investment'], 2) ?></div>
                    <ul class="plan-features">
                        <li><?= $plan['daily_earnings_percent'] ?>% Daily Earnings</li>
                        <li>30 Days Duration</li>
                        <li>Daily Withdrawals</li>
                        <li>24/7 Support</li>
                    </ul>
                    
                    <?php if ($user_balance >= $plan['investment']): ?>
                        <form action="process_investment.php" method="POST" onsubmit="return confirmInvestment(event)">
                            <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>" 
                                   data-plan-name="<?= htmlspecialchars($plan['name']) ?>" 
                                   data-plan-amount="<?= number_format($plan['investment'], 2) ?>">
                            <button type="submit" class="btn btn-primary">Invest Now</button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-secondary" disabled>Insufficient Balance</button>
                        
                        <form action="process_deposit.php" method="POST">
                            <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                            <input type="hidden" name="required_amount" value="<?= $plan['investment'] ?>">
                            <button type="submit" class="btn btn-primary">Deposit Required Amount</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="card" style="margin-top: 2rem;">
            <h2>How Investing Works</h2>
            <ol style="padding-left: 1.5rem; line-height: 2;">
                <li>Choose an investment plan that fits your budget</li>
                <li>Your investment will be active for 30 days</li>
                <li>Earn <?= $plans[0]['daily_earnings_percent'] ?>% daily returns</li>
                <li>Withdraw your earnings daily or reinvest</li>
                <li>After 30 days, you can reinvest your capital</li>
            </ol>
        </div>
    </main>

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
    
    function confirmInvestment(event) {
        const form = event.target.closest('form');
        const planName = form.querySelector('input[name="plan_id"]').getAttribute('data-plan-name');
        const amount = form.querySelector('input[name="plan_id"]').getAttribute('data-plan-amount');
        return confirm(`Are you sure you want to invest ₨${amount} in the ${planName} plan?`);
    }
    
    // Attach event listeners to all forms
    document.querySelectorAll('form[onsubmit="return confirmInvestment()"]').forEach(form => {
        form.addEventListener('submit', confirmInvestment);
    });
    </script>
</body>
<?php include '../includes/footer.php';?>
</html>
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
        margin: 5rem auto 2rem;
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

    /* Balance Info */
    .balance-info {
        background: var(--gradient-primary);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-strong);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .balance-info h2 {
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
        opacity: 0.9;
    }

    .balance-amount {
        font-size: 2.5rem;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Plans Grid */
    .plans-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    /* Plan Card */
    .plan-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: var(--shadow-soft);
        transition: transform 0.3s ease;
        border: 2px solid #e5e7eb;
        position: relative;
        overflow: hidden;
    }

    .plan-card::before {
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

    .plan-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-strong);
    }

    .plan-card:hover::before {
        opacity: 1;
    }

    .plan-card.recommended {
        border-color: var(--accent-color);
        background: linear-gradient(to bottom right, #ffffff, #fdfbec);
    }

    .recommended-badge {
        position: absolute;
        top: 1rem;
        right: -2rem;
        background: var(--gradient-accent);
        color: var(--primary-dark);
        padding: 0.5rem 3rem;
        transform: rotate(45deg);
        font-weight: 600;
        box-shadow: var(--shadow-soft);
    }

    .plan-name {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--primary-dark);
        margin-bottom: 1rem;
    }

    .plan-price {
        font-size: 2.5rem;
        font-weight: 700;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 1.5rem;
    }

    .plan-features {
        list-style: none;
        padding: 0;
        margin: 1.5rem 0;
    }

    .plan-features li {
        padding: 0.75rem 0;
        display: flex;
        align-items: center;
        color: #4a5568;
    }

    .plan-features li:before {
        content: "✓";
        color: #10b981;
        margin-right: 0.75rem;
        font-weight: bold;
        background: #dcfce7;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
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
        width: 100%;
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

    .btn-secondary {
        background: #f3f4f6;
        color: #6b7280;
        border: 1px solid #e5e7eb;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    .btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    /* How It Works Section */
    .card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: var(--shadow-soft);
    }

    .card h2 {
        color: var(--primary-dark);
        margin-bottom: 1.5rem;
    }

    .card ol {
        color: #4a5568;
    }

    .card ol li {
        margin-bottom: 0.75rem;
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

        .balance-info {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }

        .balance-amount {
            font-size: 2rem;
        }

        .plan-card {
            padding: 1.5rem;
        }

        .recommended-badge {
            transform: rotate(0);
            right: 0;
            top: 0;
            padding: 0.25rem 1rem;
        }

        .plan-price {
            font-size: 2rem;
        }
    }

    @media screen and (max-width: 480px) {
        .main-content {
            padding: 1rem;
            margin-top: 3.5rem;
        }

        h1 {
            font-size: 1.75rem;
        }

        .balance-amount {
            font-size: 1.75rem;
        }

        .plan-name {
            font-size: 1.25rem;
        }

        .plan-price {
            font-size: 1.75rem;
        }

        .btn {
            padding: 0.875rem 1.5rem;
        }
    }

    /* Touch Device Optimizations */
    @media (hover: none) {
        .btn {
            min-height: 44px;
        }

        .plan-card:hover {
            transform: none;
        }

        .btn:hover {
            transform: none;
        }
    }
</style>