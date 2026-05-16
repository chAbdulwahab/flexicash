<?php
session_start();
require 'includes/config.php';

// Initialize MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: user/dashboard.php');
    exit();
}

// Generate CSRF token if doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
$error = null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    try {
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            throw new Exception("Invalid form submission");
        }

        $username = trim($_POST['username'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $referral_code = strtoupper(trim($_POST['referral_code'] ?? ''));

        if (empty($username) || !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            throw new Exception("Username must be 3-20 characters (letters, numbers, underscores)");
        } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address");
        } elseif (empty($phone) || !preg_match('/^[0-9]{11}$/', $phone)) {
            throw new Exception("Please enter a valid 11-digit phone number");
        } elseif (empty($password) || strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters");
        } elseif (!empty($referral_code) && !preg_match('/^[A-Z0-9]{6,8}$/', $referral_code)) {
            throw new Exception("Referral code must be 6-8 uppercase letters/numbers");
        }

        $conn->begin_transaction();

        $checkQuery = "SELECT id FROM users WHERE email = ? OR username = ? OR phone = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("sss", $email, $username, $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            throw new Exception("Email, username, or phone already exists.");
        }

        $user_referral_code = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', 5)), 0, 8);
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password, referral_code) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $phone, $hashed_password, $user_referral_code);
        $stmt->execute();
        $user_id = $stmt->insert_id;

        if (!empty($referral_code)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
            $stmt->bind_param("s", $referral_code);
            $stmt->execute();
            $result = $stmt->get_result();
            $referrer = $result->fetch_assoc();

            if ($referrer) {
                $stmt = $conn->prepare("INSERT INTO referrals (referrer_id, referred_id, has_deposited) VALUES (?, ?, 0)");
                $stmt->bind_param("ii", $referrer['id'], $user_id);
                $stmt->execute();

                // Add 20rs to the new user's balance
                $stmt = $conn->prepare("UPDATE users SET balance = balance + 20 WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
            } else {
                throw new Exception("Invalid referral code - please check or leave blank");
            }
        }

        $conn->commit();
        $_SESSION['success'] = "Registration successful! Please login.";
        header('Location: login.php');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}
include 'includes/header.php';
?>

<?php if ($error): ?>
    <div class="alert alert-error mb-2"><?= htmlspecialchars($error) ?></div>
    <script>
        alert("<?= addslashes($error) ?>");
    </script>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success mb-2"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Earnings Platform</title>
    <link rel="stylesheet" href="assets/css/styles.css">
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

        .container {
            max-width: 800px;
            margin: 6rem auto;
            padding: 0 1.5rem;
            animation: fadeIn 0.5s ease;
        }

        .flex-center {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-strong);
            padding: 2.5rem;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        h2 {
            font-size: 2rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
            text-align: center;
        }

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
            width: 90%;
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

        .form-input:invalid {
            border-color: #ef4444;
        }

        .form-input:invalid:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

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

        .text-center {
            text-align: center;
        }

        .text-link {
            color: var(--primary-dark);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .text-link:hover {
            color: var(--primary-light);
            text-decoration: underline;
        }

        .mt-2 {
            margin-top: 1rem;
        }

        .mb-2 {
            margin-bottom: 1rem;
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            animation: slideIn 0.3s ease;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
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
            .container {
                margin: 4rem auto;
                padding: 0 1rem;
            }

            .card {
                padding: 2rem;
            }

            h2 {
                font-size: 1.75rem;
            }
        }

        @media screen and (max-width: 480px) {
            .container {
                margin: 3rem auto;
            }

            .card {
                padding: 1.5rem;
            }

            h2 {
                font-size: 1.5rem;
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
            .btn {
                min-height: 44px;
            }

            .card:hover {
                transform: none;
            }

            .btn:hover {
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
    <main class="main-content">
        <div class="container flex-center">
            <div class="card">
                <h2 class="text-center mb-2">Create Account</h2>

                <?php if ($error): ?>
                    <div class="alert alert-error mb-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success mb-2"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="register" value="1">

                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-input" required
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            pattern="[a-zA-Z0-9_]{3,20}"
                            title="3-20 characters (letters, numbers, underscores)">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input" required
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-input" required
                            value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                            pattern="[0-9]{11}"
                            title="11-digit phone number (e.g., 03001234567)">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input" required
                            minlength="8"
                            title="Minimum 8 characters">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Referral Code (Optional)</label>
                        <input type="text" name="referral_code" class="form-input"
                            value="<?= htmlspecialchars($_POST['referral_code'] ?? '') ?>"
                            pattern="[A-Z0-9]{6,8}"
                            title="6-8 uppercase letters/numbers"
                            placeholder="Enter referral code if any">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Create Account
                    </button>
                </form>

                <div class="text-center mt-2">
                    <p>Already have an account? <a href="login.php" class="text-link">Login here</a></p>
                </div>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php' ?>
</body>
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

</html>
<script type="text/javascript">
	atOptions = {
		'key' : 'f879eb2451fceb8c3ab1a2bbd430fe0f',
		'format' : 'iframe',
		'height' : 50,
		'width' : 320,
		'params' : {}
	};
</script>
<script type="text/javascript" src="//www.highperformanceformat.com/f879eb2451fceb8c3ab1a2bbd430fe0f/invoke.js"></script>