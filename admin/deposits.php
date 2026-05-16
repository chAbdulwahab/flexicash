<?php
session_start();
require '../includes/config.php';

$sql = "SELECT deposits.*, users.email FROM deposits 
        JOIN users ON deposits.user_id = users.id 
        ORDER BY deposits.id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Deposits</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
            max-width: 1200px;
            margin: 5rem auto 2rem;
            padding: 0 1.5rem;
            animation: fadeIn 0.5s ease;
        }

        h2 {
            font-size: 2rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2rem;
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 2rem;
            border-radius: 15px;
            box-shadow: var(--shadow-soft);
        }

        .table {
            width: 100%;
            background: white;
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

        .table tbody tr:last-child td:first-child {
            border-bottom-left-radius: 10px;
        }

        .table tbody tr:last-child td:last-child {
            border-bottom-right-radius: 10px;
        }

        /* Badge Styles */
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .bg-warning {
            background: #fef3c7 !important;
            color: #92400e !important;
        }

        .bg-success {
            background: #dcfce7 !important;
            color: #166534 !important;
        }

        .bg-danger {
            background: #fee2e2 !important;
            color: #991b1b !important;
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

        .text-muted {
            color: #6b7280;
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

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .container {
                margin-top: 4rem;
                padding: 0 1rem;
            }

            h2 {
                font-size: 1.75rem;
            }

            .table td, .table th {
                padding: 0.75rem;
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
            .container {
                margin-top: 3.5rem;
                padding: 0.5rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
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
</head>
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
                <a href="videos.php" class="nav-link">videos</a>
                <a href="withdrawals.php" class="nav-link">withdraws</a>
                <a href="../logout.php" class="nav-link">Logout</a>
            </div>
        </nav>
    </header>
<body class="container mt-5">

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
        max-width: 1200px;
        margin: 5rem auto 2rem;
        padding: 0 1.5rem;
        animation: fadeIn 0.5s ease;
    }

    h2 {
        font-size: 2rem;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 2rem;
    }

    /* Table Styles */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin-bottom: 2rem;
        border-radius: 15px;
        box-shadow: var(--shadow-soft);
    }

    .table {
        width: 100%;
        background: white;
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

    .table tbody tr:last-child td:first-child {
        border-bottom-left-radius: 10px;
    }

    .table tbody tr:last-child td:last-child {
        border-bottom-right-radius: 10px;
    }

    /* Badge Styles */
    .badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.875rem;
    }

    .bg-warning {
        background: #fef3c7 !important;
        color: #92400e !important;
    }

    .bg-success {
        background: #dcfce7 !important;
        color: #166534 !important;
    }

    .bg-danger {
        background: #fee2e2 !important;
        color: #991b1b !important;
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

    .text-muted {
        color: #6b7280;
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

    /* Responsive Design */
    @media screen and (max-width: 768px) {
        .container {
            margin-top: 4rem;
            padding: 0 1rem;
        }

        h2 {
            font-size: 1.75rem;
        }

        .table td, .table th {
            padding: 0.75rem;
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
        .container {
            margin-top: 3.5rem;
            padding: 0.5rem;
        }

        h2 {
            font-size: 1.5rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
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
<h2>All Deposits</h2>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>User Email</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Screenshot</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Processed At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while($deposit = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $deposit['id'] ?></td>
            <td><?= $deposit['email'] ?></td>
            <td><?= $deposit['amount'] ?></td>
            <td><?= $deposit['method'] ?></td>
            <td>
                <?php if (!empty($deposit['screenshot'])): ?>
                    <img src="../uploads/deposits/<?= htmlspecialchars($deposit['screenshot']) ?>" 
                     class="screenshot-preview"
                     onclick="showScreenshot(this.src)"
                     alt="Payment Screenshot">
                <?php else: ?>
                    <span class="text-muted">No Screenshot</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($deposit['status'] == 'pending'): ?>
                    <span class="badge bg-warning text-dark">Pending</span>
                <?php elseif ($deposit['status'] == 'approved'): ?>
                    <span class="badge bg-success">Approved</span>
                <?php else: ?>
                    <span class="badge bg-danger">Rejected</span>
                <?php endif; ?>
            </td>
            <td><?= $deposit['created_at'] ?></td>
            <td><?= $deposit['processed_at'] ?? 'N/A' ?></td>
            <td>
                <?php if ($deposit['status'] == 'pending'): ?>
                    <a href="process_deposit.php?id=<?= $deposit['id'] ?>&action=approve" class="btn btn-success btn-sm">Approve</a>
                    <a href="process_deposit.php?id=<?= $deposit['id'] ?>&action=reject" class="btn btn-danger btn-sm">Reject</a>
                <?php else: ?>
                    <span class="text-muted">No Action</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Screenshot Modal -->
<div id="screenshotModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <img id="modalImage" class="modal-image" src="" alt="Payment Screenshot">
    </div>
</div>

<script>
    function showScreenshot(src) {
        const modal = document.getElementById('screenshotModal');
        const modalImg = document.getElementById('modalImage');
        modal.classList.add('show');
        modalImg.src = src;
    }

    function closeModal() {
        const modal = document.getElementById('screenshotModal');
        modal.classList.remove('show');
    }

    // Close modal when clicking outside the image
    document.getElementById('screenshotModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>
</body>
</html>
