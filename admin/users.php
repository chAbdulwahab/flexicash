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

// Get all users
$query = "SELECT id, username, email, balance, created_at, is_admin FROM users ORDER BY created_at DESC";
$result = $conn->query($query);
$users = $result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Earnings Platform</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
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
                <a href="users.php" class="nav-link active">Users</a>
                <a href="deposits.php" class="nav-link">Deposits</a>
                <a href="videos.php" class="nav-link">videos</a>
                <a href="withdrawals.php" class="nav-link">withdraws</a>
                <a href="../logout.php" class="nav-link">Logout</a>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <h1>User Management</h1>
        
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Balance</th>
                        <th>Joined</th>
                        <th>Admin</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>₨<?= number_format($user['balance'], 2) ?></td>
                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <?php 
                                    if ($user['is_admin'] == 1) {
                                        echo 'Yes';
                                    } elseif ($user['is_admin'] == 2) {
                                        echo 'Referral Required';
                                    } else {
                                        echo 'No';
                                    }
                                ?>
                            </td>
                            <td>
                                <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-primary">Edit</a>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
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

    .card {
        background: white;
        border-radius: 15px;
        box-shadow: var(--shadow-soft);
        padding: 1.5rem;
        margin-bottom: 2rem;
        overflow: hidden;
    }

    /* Table Styles */
    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 0;
    }

    .table th {
        background: var(--gradient-primary);
        color: white;
        font-weight: 600;
        padding: 1rem;
        text-align: left;
        white-space: nowrap;
    }

    .table th:first-child {
        border-top-left-radius: 10px;
    }

    .table th:last-child {
        border-top-right-radius: 10px;
    }

    .table td {
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background: #f8fafc;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Button Styles */
    .btn {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        margin: 0.25rem;
    }

    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        border: none;
        box-shadow: 0 2px 4px rgba(26, 35, 126, 0.2);
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc2626, #ef4444);
        color: white;
        border: none;
        box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    }

    /* Navigation Styles */
    .nav-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 2rem;
        background: white;
        box-shadow: var(--shadow-soft);
    }

    .nav-links {
        display: flex;
        gap: 1.5rem;
        align-items: center;
    }

    .nav-link {
        color: var(--primary-dark);
        text-decoration: none;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .nav-link:hover {
        background: rgba(26, 35, 126, 0.1);
    }

    .nav-link.active {
        background: var(--gradient-primary);
        color: white;
    }

    /* Hamburger Menu */
    .hamburger {
        display: none;
        flex-direction: column;
        justify-content: space-around;
        width: 30px;
        height: 25px;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 0;
        z-index: 10;
    }

    .hamburger span {
        width: 30px;
        height: 3px;
        background: var(--primary-dark);
        border-radius: 10px;
        transition: all 0.3s linear;
        position: relative;
        transform-origin: 1px;
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

        .card {
            padding: 1rem;
        }

        .table td, .table th {
            padding: 0.75rem;
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
    }

    @media screen and (max-width: 480px) {
        .main-content {
            margin-top: 3.5rem;
        }

        h1 {
            font-size: 1.75rem;
        }

        .btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
    }

    /* Touch Device Optimizations */
    @media (hover: none) {
        .btn {
            min-height: 44px;
        }

        .btn:hover {
            transform: none;
        }

        .table tbody tr:hover {
            background: none;
        }

        .nav-link {
            min-height: 44px;
            display: flex;
            align-items: center;
        }
    }
</style>