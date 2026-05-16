<?php
session_start();
require '../includes/config.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

$sql = "SELECT w.*, u.email FROM withdrawals w
        JOIN users u ON w.user_id = u.id 
        ORDER BY w.id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Withdrawals</title>
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
                <a href="users.php" class="nav-link">Users</a>
                <a href="deposits.php" class="nav-link">Deposits</a>
                <a href="withdrawals.php" class="nav-link active">Withdrawals</a>
                <a href="videos.php" class="nav-link">Videos</a>
                <a href="../logout.php" class="nav-link">Logout</a>
            </div>
        </nav>
    </header>
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
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0;
            overflow-x: auto;
            display: block;
        }
        
        .table th {
            background: var(--gradient-primary);
            color: white;
            font-weight: 600;
            padding: 1rem;
            text-align: left;
            white-space: nowrap;
            position: sticky;
            top: 0;
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
            white-space: nowrap;
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background: #f8fafc;
        }
        
        /* Status Badge Styles */
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.875rem;
            display: inline-block;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-approved {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
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
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white !important;
            border: none;
            box-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: white !important;
            border: none;
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
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
        
            .btn-sm {
                padding: 0.375rem 0.75rem;
                font-size: 0.75rem;
            }
        
            .status-badge {
                padding: 0.375rem 0.75rem;
                font-size: 0.75rem;
            }
        }
        
        @media screen and (max-width: 480px) {
            .main-content {
                margin-top: 3.5rem;
                padding: 0.5rem;
            }
        
            h1 {
                font-size: 1.75rem;
            }
        
            .card {
                padding: 0.75rem;
            }
        
            .table td, .table th {
                padding: 0.5rem;
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
    <main class="main-content">
        <h1>Manage Withdrawals</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Account</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td>₨<?= number_format($row['amount'], 2) ?></td>
                            <td><?= ucfirst($row['method']) ?></td>
                            <td>
                                <?= htmlspecialchars($row['account_number']) ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $row['status'] ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
                            <td>
                                <?php if ($row['status'] === 'pending'): ?>
                                    <a href="process_withdraw.php?id=<?= $row['id'] ?>&action=approved" 
                                       class="btn btn-success btn-sm"
                                       onclick="return confirm('Are you sure you want to approve this withdrawal?')">
                                        Approve
                                    </a>
                                    <a href="process_withdraw.php?id=<?= $row['id'] ?>&action=reject" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Are you sure you want to reject this withdrawal?')">
                                        Reject
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
<script>
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.querySelector('.nav-links');
    
    hamburger.addEventListener('click', function() {
        this.classList.toggle('open');
        navLinks.classList.toggle('open');
    });
</script>
<button class="hamburger" id="hamburger" aria-label="Toggle navigation">
    <span></span>
    <span></span>
    <span></span>
</button>