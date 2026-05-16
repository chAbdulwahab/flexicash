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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Invalid form submission";
    } else {
        // Sanitize inputs
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        // Validate inputs
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address";
        } elseif (empty($password)) {
            $error = "Please enter your password";
        } else {
            // Check user credentials using MySQLi
            $stmt = $conn->prepare("SELECT id, username, password, is_admin FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                // Successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin']; // Add this line

                // Remember me functionality
                if ($remember) {
                    $session_duration = 86400 * 30; // 30 days
                    $session_params = session_get_cookie_params();
                    setcookie(
                        session_name(),
                        session_id(),
                        time() + $session_duration,
                        $session_params["path"],
                        $session_params["domain"],
                        $session_params["secure"],
                        $session_params["httponly"]
                    );
                }

                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);

                // Redirect based on admin status
                if ($user['is_admin'] == 0) {
                    header('Location: user/dashboard.php');
                } 
                elseif ($user['is_admin'] == 1) {
                    header('Location: admin/dashboard.php');
                }
                else {
                    header('Location: needactiverefferal.php');
                }
                exit();

                // Remember me functionality
                if ($remember) {
                    $session_duration = 86400 * 30; // 30 days
                    $session_params = session_get_cookie_params();
                    setcookie(
                        session_name(),
                        session_id(),
                        time() + $session_duration,
                        $session_params["path"],
                        $session_params["domain"],
                        $session_params["secure"],
                        $session_params["httponly"]
                    );
                }

                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);

                header('Location: user/dashboard.php');
                exit();
            } else {
                $error = "Invalid email or password";
            }
        }
    }
}

// Now include the header (after all header/session logic)
include 'includes/header.php';
?>
<script type='text/javascript' src='//tiedexceed.com/02/35/03/023503a1e39817f04ce19849275c08da.js'></script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Earnings Platform</title>
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

        .auth-card {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-strong);
            padding: 2.5rem;
            margin: 2rem auto;
            transition: transform 0.3s ease;
        }

        .auth-card:hover {
            transform: translateY(-5px);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header h2 {
            font-size: 2rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .text-muted {
            color: #6b7280;
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

        .flex-between {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
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

        .password-input {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #6b7280;
            padding: 0.5rem;
            transition: color 0.3s ease;
        }

        .toggle-password:hover {
            color: var(--primary-dark);
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1.5rem 0;
        }

        .remember-me input[type="checkbox"] {
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 4px;
            border: 2px solid #e2e8f0;
            cursor: pointer;
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

        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
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

            .auth-card {
                padding: 2rem;
            }

            .auth-header h2 {
                font-size: 1.75rem;
            }
        }

        @media screen and (max-width: 480px) {
            .container {
                margin: 3rem auto;
            }

            .auth-card {
                padding: 1.5rem;
            }

            .auth-header h2 {
                font-size: 1.5rem;
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

            .auth-card:hover {
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
        <div class="container">
            <div class="auth-card">
                <div class="auth-header">
                    <h2 class="text-center">Welcome Back</h2>
                    <p class="text-center text-muted">Sign in to access your account</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="login" value="1">

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" 
                               required autocomplete="email" autofocus
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <div class="flex-between">
                            <label for="password" class="form-label">Password</label>
                            <a href="forgot-password.php" class="text-link">Forgot password?</a>
                        </div>
                        <div class="password-input">
                            <input type="password" id="password" name="password" 
                                   class="form-input" required autocomplete="current-password">
                            <button type="button" class="toggle-password" aria-label="Show password">
                                👁
                            </button>
                        </div>
                    </div>

                    <div class="form-group remember-me">
                        <input type="checkbox" id="remember" name="remember" <?= isset($_POST['remember']) ? 'checked' : '' ?>>
                        <label for="remember">Keep me logged in</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Sign In
                    </button>
                </form>

                <div class="auth-footer">
                    <p>Don't have an account? <a href="register.php" class="text-link">Register here</a></p>
                </div>
            </div>
           
    </main>

    <script>
    // Toggle password visibility
    document.querySelector('.toggle-password').addEventListener('click', function(e) {
        const input = e.currentTarget.previousElementSibling;
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        e.currentTarget.textContent = type === 'password' ? '👁' : '👁‍🗨';
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
    <?php include 'includes/footer.php'?>
</body>
</html>
<script type='text/javascript' src='//tiedexceed.com/02/35/03/023503a1e39817f04ce19849275c08da.js'></script>
<script type='text/javascript' src='//tiedexceed.com/02/35/03/023503a1e39817f04ce19849275c08da.js'></script>