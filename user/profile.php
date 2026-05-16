<?php
session_start();
require '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_email'])) {
        $new_email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->bind_param("si", $new_email, $_SESSION['user_id']);
            if ($stmt->execute()) {
                $message = "Email updated successfully!";
            } else {
                $error = "Error updating email";
            }
        } else {
            $error = "Invalid email format";
        }
    }
    elseif (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 8) {
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
                    if ($stmt->execute()) {
                        $message = "Password updated successfully!";
                    } else {
                        $error = "Error updating password";
                    }
                } else {
                    $error = "Password must be at least 8 characters";
                }
            } else {
                $error = "New passwords do not match";
            }
        } else {
            $error = "Current password is incorrect";
        }
    }
}

// Get user data
$stmt = $conn->prepare("SELECT username, email, phone, balance, total_deposits FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    
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
    
        .profile-container {
            max-width: 800px;
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
    
        .profile-section {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-soft);
            padding: 2rem;
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }
    
        .profile-section:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-strong);
        }
    
        .profile-section h2 {
            color: var(--primary-dark);
            margin: 0 0 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
            font-size: 1.5rem;
        }
    
        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }
    
        .profile-info div {
            padding: 1rem;
            background: #f8fafc;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
    
        .profile-info div:hover {
            background: #f1f5f9;
            transform: translateY(-2px);
        }
    
        .profile-info strong {
            color: var(--primary-dark);
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    
        .profile-info p {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }
    
        .form-group {
            margin-bottom: 1.5rem;
        }
    
        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            color: #4a5568;
            font-weight: 500;
        }
    
        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
    
        .form-group input:focus {
            border-color: var(--primary-dark);
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
            outline: none;
        }
    
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: var(--gradient-primary);
            color: white;
            border: none;
            box-shadow: 0 4px 6px rgba(26, 35, 126, 0.2);
        }
    
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(26, 35, 126, 0.3);
        }
    
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
    
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
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
            .profile-container {
                margin: 4rem auto;
                padding: 0 1rem;
            }
    
            h1 {
                font-size: 2rem;
            }
    
            .profile-section {
                padding: 1.5rem;
            }
    
            .profile-info {
                grid-template-columns: 1fr;
            }
    
            .profile-info p {
                font-size: 1.125rem;
            }
        }
    
        @media screen and (max-width: 480px) {
            .profile-container {
                margin: 3rem auto;
            }
    
            h1 {
                font-size: 1.75rem;
            }
    
            .profile-section {
                padding: 1.25rem;
            }
    
            .btn {
                width: 100%;
                padding: 0.875rem 1.5rem;
            }
        }
    
        /* Touch Device Optimizations */
        @media (hover: none) {
            .btn {
                min-height: 44px;
            }
    
            .profile-section:hover,
            .profile-info div:hover {
                transform: none;
            }
    
            .btn:hover {
                transform: none;
            }
        }
    </style>
</head>
<script type='text/javascript' src='//tiedexceed.com/02/35/03/023503a1e39817f04ce19849275c08da.js'></script>
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
    <div class="profile-container">
        <h1>My Profile</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="profile-section">
            <h2>Account Information</h2>
            <div class="profile-info">
                <div>
                    <strong>Username</strong>
                    <p><?= htmlspecialchars($user['username']) ?></p>
                </div>
                <div>
                    <strong>Email</strong>
                    <p><?= htmlspecialchars($user['email']) ?></p>
                </div>
                <div>
                    <strong>Phone</strong>
                    <p><?= htmlspecialchars($user['phone']) ?></p>
                </div>
                <div>
                    <strong>Balance</strong>
                    <p>PKR<?= number_format($user['balance'], 2) ?></p>
                </div>
                <div>
                    <strong>Total Deposits</strong>
                    <p>PKR<?= number_format($user['total_deposits'], 2) ?></p>
                </div>
            </div>
        </div>

        <div class="profile-section">
            <h2>Update Email</h2>
            <form method="POST">
                <div class="form-group">
                    <label>New Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <button type="submit" name="update_email" class="btn">Update Email</button>
            </form>
        </div>
        <script type='text/javascript' src='//tiedexceed.com/02/35/03/023503a1e39817f04ce19849275c08da.js'></script>
        <div class="profile-section">
            <h2>Update Password</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required minlength="8">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required minlength="8">
                </div>
                <button type="submit" name="update_password" class="btn">Update Password</button>
            </form>
        </div>
    </div>
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
<script type='text/javascript' src='//tiedexceed.com/02/35/03/023503a1e39817f04ce19849275c08da.js'></script>
</html>