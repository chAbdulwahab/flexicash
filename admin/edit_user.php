<?php
session_start();
require '../includes/config.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Initialize MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get user ID from URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch user data
$user = null;
if ($user_id > 0) {
    $stmt = $conn->prepare("SELECT id, username, email, balance, is_admin FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $balance = floatval($_POST['balance']);
    
    // Determine admin status based on checkboxes
    if (isset($_POST['is_admin'])) {
        $is_admin = 1; // Admin user
    } elseif (isset($_POST['needs_referral'])) {
        $is_admin = 2; // Needs referral
    } else {
        $is_admin = 0; // Regular user
    }
    
    // Basic validation
    if (empty($username) || empty($email)) {
        $error = "Username and email are required";
    } else {
        // Update user in database
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, balance = ?, is_admin = ? WHERE id = ?");
        $stmt->bind_param("ssdii", $username, $email, $balance, $is_admin, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "User updated successfully";
            header("Location: users.php");
            exit();
        } else {
            $error = "Error updating user: " . $conn->error;
        }
    }
}

include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Earnings Platform</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
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
        
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: var(--shadow-soft);
            text-align: center;
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
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-strong);
        }
        
        .stat-card:hover::before {
            opacity: 1;
        }
        
        .stat-card h3 {
            color: var(--primary-dark);
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 1rem 0;
        }
        
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
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
        
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .main-content {
                margin-top: 4rem;
                padding: 0 1rem;
            }
        
            h1 {
                font-size: 2rem;
            }
        
            .stat-value {
                font-size: 2rem;
            }
        
            .hamburger {
                display: flex;
            }
        
            .nav-links {
                display: none;
                position: fixed;
                top: 60px;
                left: 0;
                right: 0;
                background: white;
                padding: 1rem;
                flex-direction: column;
                box-shadow: var(--shadow-soft);
            }
        
            .nav-links.open {
                display: flex;
            }
        
            .hamburger.open span:first-child {
                transform: rotate(45deg);
            }
        
            .hamburger.open span:nth-child(2) {
                opacity: 0;
            }
        
            .hamburger.open span:last-child {
                transform: rotate(-45deg);
            }
        }
        
        @media screen and (max-width: 480px) {
            .main-content {
                margin-top: 3.5rem;
            }
        
            h1 {
                font-size: 1.75rem;
            }
        
            .stat-value {
                font-size: 1.75rem;
            }
        
            .btn {
                padding: 0.875rem 1.5rem;
                width: 100%;
            }
        }
        
        /* Touch Device Optimizations */
        @media (hover: none) {
            .btn {
                min-height: 44px;
            }
        
            .stat-card:hover {
                transform: none;
            }
        
            .btn:hover {
                transform: none;
            }
        
            .nav-link {
                min-height: 44px;
                display: flex;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <div class="logo">
                <a href="../index.php">EarningsPK</a>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="blogs.php" class="nav-link active">blogs</a>
                <a href="users.php" class="nav-link">Users</a>
                <a href="deposits.php" class="nav-link">Deposits</a>
                <a href="withdrawals.php" class="nav-link">withdraws</a>
                <a href="../logout.php" class="nav-link">Logout</a>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <h1>Edit User</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($user): ?>
            <form method="POST" class="card">
                <input type="hidden" name="update_user" value="1">
                
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-input" 
                           required value="<?= htmlspecialchars($user['username']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           required value="<?= htmlspecialchars($user['email']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="balance" class="form-label">Balance</label>
                    <input type="number" id="balance" name="balance" class="form-input" 
                           step="0.01" required value="<?= htmlspecialchars($user['balance']) ?>">
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <div>
                            <input type="checkbox" id="is_admin" name="is_admin" 
                                   <?= $user['is_admin'] == 1 ? 'checked' : '' ?>>
                            <label for="is_admin">Admin User</label>
                        </div>
                        <div>
                            <input type="checkbox" id="needs_referral" name="needs_referral" 
                                   <?= $user['is_admin'] == 2 ? 'checked' : '' ?>>
                            <label for="needs_referral">Requires Active Referral</label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="users.php" class="btn btn-secondary">Cancel</a>
            </form>
        <?php else: ?>
            <div class="alert alert-error">User not found</div>
            <a href="users.php" class="btn btn-secondary">Back to Users</a>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>