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

// Get user balance
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$balance = $user['balance'];

// Get withdrawal history
$stmt = $conn->prepare("SELECT * FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$withdrawals = $result->fetch_all(MYSQLI_ASSOC);

// Generate CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page_title = "Withdraw Funds";
include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - FlexiCash</title>
    
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

        .withdraw-container {
            max-width: 800px;
            margin: 5rem auto 2rem;
            padding: 0 1.5rem;
            animation: fadeIn 0.5s ease;
        }

        .page-title {
            font-size: 2.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2rem;
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            animation: slideIn 0.3s ease;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Balance Card */
        .balance-card {
            background: var(--gradient-primary);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: var(--shadow-strong);
        }

        .balance-amount {
            font-size: 3rem;
            font-weight: 700;
            margin: 1rem 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Form Card */
        .form-card, .history-card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-soft);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .form-card:hover, .history-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-strong);
        }

        .form-header {
            padding: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
            background: #f8fafc;
        }

        .form-header h2 {
            color: var(--primary-dark);
            margin: 0;
            font-size: 1.5rem;
        }

        .form-body {
            padding: 2rem;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.75rem;
            color: #4a5568;
            font-weight: 500;
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

        /* Payment Method Options */
        .method-option {
            display: flex;
            align-items: center;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .method-option:hover {
            border-color: var(--primary-dark);
            background: rgba(26, 35, 126, 0.05);
        }

        .method-option.selected {
            border-color: var(--primary-dark);
            background: rgba(26, 35, 126, 0.05);
            transform: scale(1.02);
        }

        .method-icon {
            width: 48px;
            height: 48px;
            margin-right: 1.5rem;
            transition: transform 0.3s ease;
        }

        .method-option:hover .method-icon {
            transform: scale(1.1);
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
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

        /* Status Styles */
        .status-pending { color: #f59e0b; }
        .status-approved { color: #10b981; }
        .status-rejected { color: #ef4444; }

        /* Button Styles */
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            border: none;
            width: 100%;
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

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .withdraw-container {
                margin-top: 4rem;
                padding: 0 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .balance-amount {
                font-size: 2.5rem;
            }

            .form-body {
                padding: 1.5rem;
            }

            .method-icon {
                width: 40px;
                height: 40px;
            }
        }

        @media screen and (max-width: 480px) {
            .withdraw-container {
                margin-top: 3.5rem;
            }

            .page-title {
                font-size: 1.75rem;
            }

            .balance-amount {
                font-size: 2rem;
            }

            .form-body {
                padding: 1.25rem;
            }

            .method-option {
                padding: 0.75rem;
            }

            .table th,
            .table td {
                padding: 0.75rem;
                font-size: 0.875rem;
            }
        }

        /* Touch Device Optimizations */
        @media (hover: none) {
            .btn,
            .form-input,
            .method-option {
                min-height: 44px;
            }

            .form-card:hover,
            .history-card:hover {
                transform: none;
            }

            .method-option:hover {
                transform: none;
            }
        }
    </style>
</head>
<body>
<header class="header">
        <nav class="nav-container">
            <div class="logo">
                <h1><a href="../index.php">Flexicash</a></h1>
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
    <script type="text/javascript">
	atOptions = {
		'key' : 'f879eb2451fceb8c3ab1a2bbd430fe0f',
		'format' : 'iframe',
		'height' : 50,
		'width' : 320,
		'params' : {}
	};
</script>
<script type="text/javascript" src="//tiedexceed.com/f879eb2451fceb8c3ab1a2bbd430fe0f/invoke.js"></script>
<script type="text/javascript">
	atOptions = {
		'key' : 'f879eb2451fceb8c3ab1a2bbd430fe0f',
		'format' : 'iframe',
		'height' : 50,
		'width' : 320,
		'params' : {}
	};
</script>
<script type="text/javascript" src="//tiedexceed.com/f879eb2451fceb8c3ab1a2bbd430fe0f/invoke.js"></script>
<script type="text/javascript">
	atOptions = {
		'key' : 'f879eb2451fceb8c3ab1a2bbd430fe0f',
		'format' : 'iframe',
		'height' : 50,
		'width' : 320,
		'params' : {}
	};
</script>
<script type="text/javascript" src="//tiedexceed.com/f879eb2451fceb8c3ab1a2bbd430fe0f/invoke.js"></script>
<script type="text/javascript">
	atOptions = {
		'key' : 'f879eb2451fceb8c3ab1a2bbd430fe0f',
		'format' : 'iframe',
		'height' : 50,
		'width' : 320,
		'params' : {}
	};
</script>
<script type="text/javascript" src="//tiedexceed.com/f879eb2451fceb8c3ab1a2bbd430fe0f/invoke.js"></script>

    <div class="withdraw-container">
        <h1 class="page-title">Withdraw Funds</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="balance-card">
            <h2>Available Balance</h2>
            <div class="balance-amount">₨<?= number_format($balance, 2) ?></div>
            <p>Minimum withdrawal amount: ₨50</p>
        </div>
        <script type="text/javascript">
	atOptions = {
		'key' : 'f879eb2451fceb8c3ab1a2bbd430fe0f',
		'format' : 'iframe',
		'height' : 50,
		'width' : 320,
		'params' : {}
	};
</script>
<script type="text/javascript" src="//tiedexceed.com/f879eb2451fceb8c3ab1a2bbd430fe0f/invoke.js"></script>
<script type="text/javascript">
	atOptions = {
		'key' : 'f879eb2451fceb8c3ab1a2bbd430fe0f',
		'format' : 'iframe',
		'height' : 50,
		'width' : 320,
		'params' : {}
	};
</script>
<script type="text/javascript" src="//tiedexceed.com/f879eb2451fceb8c3ab1a2bbd430fe0f/invoke.js"></script>
<script type="text/javascript">
	atOptions = {
		'key' : 'f879eb2451fceb8c3ab1a2bbd430fe0f',
		'format' : 'iframe',
		'height' : 50,
		'width' : 320,
		'params' : {}
	};
</script>
<script type="text/javascript" src="//tiedexceed.com/f879eb2451fceb8c3ab1a2bbd430fe0f/invoke.js"></script>
<script type='text/javascript' src='//tiedexceed.com/be/c2/be/bec2be69d28c8e8fe6dbbe1820c6172a.js'></script>
        <div class="form-card">
            <div class="form-header">
                <h2>New Withdrawal Request</h2>
            </div>
            <div class="form-body">
                <form action="process_withdraw.php" method="POST">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="form-group">
                        <label class="form-label">Amount (PKR)</label>
                        <input type="number" name="amount" class="form-input" min="50" step="10" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Payment Method</label>
                        <div class="method-option selected" data-method="jazzcash">
                            <img src="../assets/images/jazzcash.png" class="method-icon" alt="JazzCash">
                            <div>JazzCash</div>
                            <input type="radio" name="method" value="jazzcash" checked hidden>
                        </div>
                        <div class="method-option" data-method="easypaisa">
                            <img src="../assets/images/easypaisa.png" class="method-icon" alt="EasyPaisa">
                            <div>EasyPaisa</div>
                            <input type="radio" name="method" value="easypaisa" hidden>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="account_number" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Account Name</label>
                        <input type="text" name="account_name" class="form-input" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Request Withdrawal</button>
                </form>
            </div>
        </div>

        <div class="history-card">
            <div class="form-header">
                <h2>Withdrawal History</h2>
            </div>
            <div class="form-body">
                <?php if (count($withdrawals) > 0): ?>
                    <div class="table-responsive">
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
                                <?php foreach ($withdrawals as $withdrawal): ?>
                                    <tr>
                                        <td><?= date('M d, Y h:i A', strtotime($withdrawal['created_at'])) ?></td>
                                        <td>₨<?= number_format($withdrawal['amount'], 2) ?></td>
                                        <td><?= ucfirst($withdrawal['method']) ?></td>
                                        <td class="status-<?= $withdrawal['status'] ?>"><?= ucfirst($withdrawal['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center">No withdrawal history found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script type="text/javascript">
	atOptions = {
		'key' : 'f879eb2451fceb8c3ab1a2bbd430fe0f',
		'format' : 'iframe',
		'height' : 50,
		'width' : 320,
		'params' : {}
	};
</script>
<script type="text/javascript" src="//tiedexceed.com/f879eb2451fceb8c3ab1a2bbd430fe0f/invoke.js"></script>
<script type="text/javascript">
	atOptions = {
		'key' : 'f879eb2451fceb8c3ab1a2bbd430fe0f',
		'format' : 'iframe',
		'height' : 50,
		'width' : 320,
		'params' : {}
	};
</script>
<script type="text/javascript" src="//tiedexceed.com/f879eb2451fceb8c3ab1a2bbd430fe0f/invoke.js"></script>
<script type="text/javascript">
	atOptions = {
		'key' : 'f879eb2451fceb8c3ab1a2bbd430fe0f',
		'format' : 'iframe',
		'height' : 50,
		'width' : 320,
		'params' : {}
	};
</script>
<script type="text/javascript" src="//tiedexceed.com/f879eb2451fceb8c3ab1a2bbd430fe0f/invoke.js"></script>
<script type="text/javascript">
	atOptions = {
		'key' : 'f879eb2451fceb8c3ab1a2bbd430fe0f',
		'format' : 'iframe',
		'height' : 50,
		'width' : 320,
		'params' : {}
	};
</script>
<script type="text/javascript" src="//tiedexceed.com/f879eb2451fceb8c3ab1a2bbd430fe0f/invoke.js"></script>
<script type="text/javascript">
	atOptions = {
		'key' : 'f879eb2451fceb8c3ab1a2bbd430fe0f',
		'format' : 'iframe',
		'height' : 50,
		'width' : 320,
		'params' : {}
	};
</script>
<script type="text/javascript" src="//tiedexceed.com/f879eb2451fceb8c3ab1a2bbd430fe0f/invoke.js"></script>
<script type="text/javascript">
	atOptions = {
		'key' : 'f879eb2451fceb8c3ab1a2bbd430fe0f',
		'format' : 'iframe',
		'height' : 50,
		'width' : 320,
		'params' : {}
	};
</script>
<script type="text/javascript" src="//tiedexceed.com/f879eb2451fceb8c3ab1a2bbd430fe0f/invoke.js"></script>
<script type="text/javascript">
	atOptions = {
		'key' : 'f879eb2451fceb8c3ab1a2bbd430fe0f',
		'format' : 'iframe',
		'height' : 50,
		'width' : 320,
		'params' : {}
	};
</script>
<script type="text/javascript" src="//tiedexceed.com/f879eb2451fceb8c3ab1a2bbd430fe0f/invoke.js"></script>
    <script>
    // Method selection functionality
    document.querySelectorAll('.method-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.method-option').forEach(el => el.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });

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
    <?php './includes/footer.php'?>
    <script type="text/javascript">
	atOptions = {
		'key' : 'f879eb2451fceb8c3ab1a2bbd430fe0f',
		'format' : 'iframe',
		'height' : 50,
		'width' : 320,
		'params' : {}
	};
</script>
<script type="text/javascript" src="//tiedexceed.com/f879eb2451fceb8c3ab1a2bbd430fe0f/invoke.js"></script>
</body>
</html>
