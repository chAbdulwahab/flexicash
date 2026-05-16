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

// Get user's deposit history
$stmt = $conn->prepare("SELECT * FROM deposits WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$deposits = $result->fetch_all(MYSQLI_ASSOC);

// Get user balance
$stmt = $conn->prepare("SELECT total_deposits FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user_balance = $result->fetch_column();

$page_title = "Deposit Funds";
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

        /* Balance Info */
        .balance-info {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .balance-amount {
            font-size: 2rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 1rem;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 1rem;
            background-color: white;
        }

        /* Payment Methods */
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .payment-method {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .payment-method:hover {
            border-color: var(--primary-color);
        }

        .payment-method.selected {
            border-color: var(--primary-color);
            background-color: rgba(59, 130, 246, 0.05);
        }

        .payment-method img {
            height: 40px;
            margin-bottom: 0.5rem;
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
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-block {
            display: block;
            width: 100%;
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

        .status-pending {
            color: var(--warning-color);
            font-weight: 500;
        }

        .status-approved {
            color: var(--success-color);
            font-weight: 500;
        }

        .status-rejected {
            color: var(--danger-color);
            font-weight: 500;
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

            .payment-methods {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .payment-methods {
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
        <h1>Deposit Funds</h1>
        
        <div class="balance-info">
            <h2>Current Balance</h2>
            <div class="balance-amount">₨<?= number_format($user_balance, 2) ?></div>
            <p>Deposit funds to start investing</p>
        </div>
        <div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Payment Account Details</h3>
    </div>
    <div class="payment-details">
        <div id="jazzcash-details" class="payment-detail">
            <p><strong>JazzCash Account:</strong></p>
            <p>Account Name: EarningsPK</p>
            <p>Account Number: 03001234567</p>
        </div>
        <div id="easypaisa-details" class="payment-detail active">
            <p><strong>EasyPaisa Account:</strong></p>
            <p>Account Number: <strong>03410867454</strong></p>
        </div>
    </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">New Deposit</h2>
            </div>
            <form action="process_deposit.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="amount" class="form-label">Amount (PKR)</label>
                    <input type="number" id="amount" name="amount" class="form-input" min="500" step="100" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Payment Method</label>
                    <div class="payment-methods">
                        <div class="payment-method selected" data-method="jazzcash">
                            <img src="../assets/images/jazzcash.png" alt="JazzCash">
                            <div>JazzCash</div>
                            <input type="radio" name="method" value="jazzcash" checked hidden>
                        </div>
                        <div class="payment-method" data-method="easypaisa">
                            <img src="../assets/images/easypaisa.png" alt="EasyPaisa">
                            <div>EasyPaisa</div>
                            <input type="radio" name="method" value="easypaisa" hidden>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="transaction_id" class="form-label">Transaction ID</label>
                    <input type="text" id="transaction_id" name="transaction_id" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="screenshot" class="form-label">Payment Screenshot</label>
                    <input type="file" id="screenshot" name="screenshot" class="form-input" accept="image/*" required>
                    <small>Upload clear image of your payment receipt</small>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Submit Deposit Request</button>
            </form>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Deposit History</h2>
            </div>
            <?php if (count($deposits) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deposits as $deposit): ?>
                            <tr>
                                <td><?= date('M d, Y h:i A', strtotime($deposit['created_at'])) ?></td>
                                <td>₨<?= number_format($deposit['amount'], 2) ?></td>
                                <td><?= ucfirst($deposit['method']) ?></td>
                                <td class="status-<?= $deposit['status'] ?>">
                                    <?= ucfirst($deposit['status']) ?>
                                    <?php if ($deposit['status'] == 'rejected' && !empty($deposit['admin_notes'])): ?>
                                        <br><small>Note: <?= htmlspecialchars($deposit['admin_notes']) ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No deposit history found.</p>
            <?php endif; ?>
        </div>
    </main>

    <script>
    // Hamburger menu functionality
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('navLinks');
    
    hamburger.addEventListener('click', function() {
        this.classList.toggle('open');
        navLinks.classList.toggle('open');
    });

    // Payment method selection
    const paymentMethods = document.querySelectorAll('.payment-method');
    paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
            // Remove selected class from all methods
            paymentMethods.forEach(m => {
                m.classList.remove('selected');
                m.querySelector('input[type="radio"]').checked = false;
            });
            
            // Add selected class to clicked method
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });

    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const amount = document.getElementById('amount').value;
        if (amount < 200) {
            e.preventDefault();
            alert('Minimum deposit amount is ₨200');
        }
    });
    </script>
</body>
</html>

<!-- Add account details section -->

</div>
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
        color: var(--primary-dark);
        margin-bottom: 2rem;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Balance Info Card */
    .balance-info {
        background: var(--gradient-primary);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        text-align: center;
        box-shadow: var(--shadow-strong);
        animation: fadeInUp 0.5s ease;
    }

    .balance-amount {
        font-size: 3rem;
        font-weight: bold;
        margin: 1rem 0;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Card Styles */
    .card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: var(--shadow-soft);
        margin-bottom: 2rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
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
        font-weight: 600;
        margin: 0;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.75rem;
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
    }

    .form-input:focus {
        border-color: var(--primary-dark);
        box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
        outline: none;
    }

    /* Payment Methods */
    .payment-methods {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin: 1.5rem 0;
    }

    .payment-method {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .payment-method:hover {
        border-color: var(--primary-dark);
        background: rgba(26, 35, 126, 0.05);
    }

    .payment-method.selected {
        border-color: var(--primary-dark);
        background: rgba(26, 35, 126, 0.05);
        transform: scale(1.02);
    }

    .payment-method img {
        height: 50px;
        margin-bottom: 1rem;
        transition: transform 0.3s ease;
    }

    .payment-method:hover img {
        transform: scale(1.1);
    }

    /* Payment Details */
    .payment-details {
        background: #f8fafc;
        border-radius: 10px;
        padding: 1.5rem;
        margin: 1.5rem 0;
    }

    .payment-detail {
        display: none;
    }

    .payment-detail.active {
        display: block;
        animation: fadeIn 0.3s ease;
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
        background: #f8fafc;
        font-weight: 600;
        color: var(--primary-dark);
    }

    .table tr:hover td {
        background: #f8fafc;
    }

    /* Status Colors */
    .status-pending {
        color: #f59e0b;
        font-weight: 500;
    }

    .status-approved {
        color: #10b981;
        font-weight: 500;
    }

    .status-rejected {
        color: #ef4444;
        font-weight: 500;
    }

    /* Button Styles */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 1rem 2rem;
        border-radius: 10px;
        font-weight: 500;
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

    .btn-block {
        width: 100%;
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive Design */
    @media screen and (max-width: 768px) {
        .main-content {
            padding: 1.5rem;
            margin-top: 4rem;
        }

        .balance-amount {
            font-size: 2.5rem;
        }

        .card {
            padding: 1.5rem;
        }

        .payment-methods {
            grid-template-columns: 1fr;
        }

        .table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }
    }

    @media screen and (max-width: 480px) {
        .main-content {
            padding: 1rem;
        }

        h1 {
            font-size: 2rem;
        }

        .balance-amount {
            font-size: 2rem;
        }

        .card {
            padding: 1.25rem;
        }

        .form-input {
            padding: 0.875rem;
        }

        .btn {
            padding: 0.875rem 1.5rem;
        }
    }

    /* Touch Device Optimizations */
    @media (hover: none) {
        .btn,
        .form-input,
        .payment-method {
            min-height: 44px;
        }

        .card:hover {
            transform: none;
        }

        .payment-method:hover {
            transform: none;
        }
    }
</style>