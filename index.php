<?php
session_start();
$pageTitle = "Dawlence Earns - Home";
require __DIR__ . '/includes/config.php'; // make sure this sets $conn

if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['user_id']);
    }
}

// Fetch plans for the calculator dropdown
$plans = [];
$sql = "SELECT * FROM plans ORDER BY investment ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $plans[] = $row;
    }
}
include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" href="./assets/images/logo.jpeg" type="image/jpeg">
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

    /* Hero Section */
    .hero {
        position: relative;
        height: 100vh;
        min-height: 600px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--gradient-primary);
        overflow: hidden;
    }

    .background-video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 1;
        opacity: 0.15;
    }

    .welcome-note {
        position: relative;
        z-index: 2;
        padding: 3rem;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        box-shadow: var(--shadow-strong);
        backdrop-filter: blur(10px);
        max-width: 800px;
        width: 90%;
        animation: fadeInUp 1s ease;
        text-align: center;
    }

    .welcome-note h1 {
        font-size: 3.5rem;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 2rem;
        line-height: 1.2;
    }

    /* Calculator Section */
    .dashboard-card {
        background: white;
        border-radius: 15px;
        box-shadow: var(--shadow-soft);
        padding: 2rem;
        margin: 2rem auto;
        max-width: 1200px;
        width: 90%;
    }

    .calculator-form {
        max-width: 600px;
        margin: 2rem auto;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #2d3748;
    }

    .form-input {
        width: 100%;
        padding: 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8fafc;
    }

    .form-input:focus {
        border-color: var(--primary-dark);
        box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
        outline: none;
    }

    /* Referral Section */
    .referral-section {
        background: var(--gradient-primary);
        color: white;
        padding: 4rem 2rem;
        text-align: center;
        margin: 3rem auto;
    }

    .percentage {
        font-size: 5rem;
        font-weight: bold;
        background: var(--gradient-accent);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 1rem 0;
        animation: pulse 2s infinite;
    }

    /* How It Works Section */
    .how-it-works {
        padding: 5rem 0rem;
        background: #f8fafc;
    }

    .steps-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin-top: 3rem;
        padding: 0 1rem;
    }

    .step {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: var(--shadow-soft);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-align: center;
    }

    .step:hover {
        transform: translateY(-10px);
        box-shadow: var(--shadow-strong);
    }

    .step-number {
        width: 50px;
        height: 50px;
        background: var(--gradient-primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 1.25rem;
        font-weight: bold;
    }

    /* Buttons */
    .primary-btn {
        display: inline-block;
        padding: 1rem 2.5rem;
        background: var(--gradient-primary);
        color: white;
        text-decoration: none;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        text-align: center;
    }

    .primary-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(26, 35, 126, 0.3);
    }

    .auth-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .welcome-note {
            padding: 2rem;
            width: 95%;
        }

        .welcome-note h1 {
            font-size: 2.5rem;
        }

        .auth-buttons {
            flex-direction: column;
            gap: 1rem;
        }

        .primary-btn {
            width: 100%;
            padding: 1rem;
        }

        .percentage {
            font-size: 4rem;
        }

        .step {
            padding: 1.5rem;
        }
    }

    @media (max-width: 480px) {
        .welcome-note {
            padding: 1.5rem;
            width: 92%;
        }

        .welcome-note h1 {
            font-size: 2rem;
        }

        .dashboard-card {
            padding: 1.5rem;
            margin: 1rem auto;
        }

        .percentage {
            font-size: 3rem;
        }

        .steps-container {
            gap: 1.5rem;
        }

        .form-input {
            padding: 0.875rem;
        }
    }

    /* Additional Responsive Improvements */
    @media (max-width: 360px) {
        .welcome-note h1 {
            font-size: 1.75rem;
        }

        .step-number {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
    }

    /* Touch Device Optimizations */
    @media (hover: none) {
        .primary-btn,
        .form-input,
        select {
            min-height: 48px;
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
    <script type='text/javascript' src='//pl26605480.profitableratecpm.com/be/c2/be/bec2be69d28c8e8fe6dbbe1820c6172a.js'></script>
    <!-- Hero Section -->
    <section class="hero">
        <img class="background-video" src="assets/images/money-animation.gif" alt="Money animation" />
        <div class="welcome-note">
            <h1>Welcome to your Earning Platform</h1>
            <?php if (is_logged_in()): ?>
                <a href="user/dashboard.php" class="primary-btn">Go to Dashboard</a>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="login.php" class="primary-btn">Login</a>
                    <a href="register.php" class="primary-btn">Register</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Profit Calculator -->
    <section class="dashboard-card">
        <h2>Profit Calculator</h2>
        <form id="calculatorForm" class="calculator-form">
            <div class="form-group">
                <label for="planSelect" class="form-label">Select Plan:</label>
                <select id="planSelect" class="form-input" required>
                    <option value="" disabled selected>Choose a plan</option>
                    <?php foreach ($plans as $plan): ?>
                        <?php
                            $planName = htmlspecialchars($plan['name']);
                            $investment = number_format($plan['investment']);
                            $dailyPercent = number_format($plan['daily_earnings_percent'], 1);
                        ?>
                        <option value="<?= $plan['investment'] ?>" data-percent="<?= $plan['daily_earnings_percent'] ?>">
                            <?= "$planName - ₨$investment ({$dailyPercent}% daily)" ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
           
            <button type="submit" class="primary-btn">Calculate Profit</button>
            <div id="result" class="result-display" aria-live="polite"></div>
        </form>
    </section>

    <!-- Referral Bonus -->
    <section class="dashboard-card referral-section">
        <h2>Referral Program</h2>
        <div class="bonus-display">
            <div class="percentage" aria-label="10 percent">10%</div>
            <p>Earn 10% commission on every referral's investment</p>
            <?php if (is_logged_in()): ?>
                <a href="user/referrals.php" class="text-link">View your referral code</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works">
        <h2>How It Works</h2>
        <div class="steps-container">
            <div class="step dashboard-card" tabindex="0">
                <span class="step-number" aria-hidden="true">1</span>
                <h3>Register Account</h3>
                <p>Create your free account in minutes</p>
            </div>
            <div class="step dashboard-card" tabindex="0">
                <span class="step-number" aria-hidden="true">2</span>
                <h3>Choose Plan</h3>
                <p>Select your investment package</p>
            </div>
            <div class="step dashboard-card" tabindex="0">
                <span class="step-number" aria-hidden="true">3</span>
                <h3>Start Earning</h3>
                <p>Watch your profits grow daily</p>
            </div>
        </div>
    </section>

   

    <script>
        document.getElementById('calculatorForm').addEventListener('submit', function(e) {
            e.preventDefault();
            calculateProfit();
        });

        function calculateProfit() {
            const planSelect = document.getElementById('planSelect');
            const selectedOption = planSelect.options[planSelect.selectedIndex];
            const dailyPercent = selectedOption.getAttribute('data-percent');
            const investmentAmount = selectedOption.value;

            if (investmentAmount && dailyPercent) {
                const dailyProfit = (investmentAmount * (dailyPercent / 100)).toFixed(2);
                const monthlyProfit = (dailyProfit * 30).toFixed(2);

                document.getElementById('result').innerHTML = `
                    <div class="profit-result">
                        <p><strong>Daily Profit:</strong> ₨${dailyProfit}</p>
                        <p><strong>Monthly Profit (30 days):</strong> ₨${monthlyProfit}</p>
                    </div>
                `;
            } else {
                document.getElementById('result').innerHTML = `
                    <p class="error">Please select a plan</p>
                `;
            }
        }

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
</body>
<?php include 'includes/footer.php'; ?>
</html>