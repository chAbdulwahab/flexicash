<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = "Active Referral Required";
include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - FlexiCash</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .referral-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            text-align: center;
        }
        .warning-icon {
            font-size: 4rem;
            color: #f59e0b;
            margin-bottom: 1rem;
        }
        .message-box {
            background: #fff;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        .info-text {
            color: #666;
            margin: 1rem 0;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <main class="main-content">
        <div class="referral-container">
            <div class="message-box">
                <div class="warning-icon">⚠️</div>
                <h1>Active Referral Required</h1>
                
                <div class="info-text">
                    <p>To access your account and continue earning, you need to have active referrals in the last 9 days.</p>
                    <p>This is a requirement to maintain account activity and ensure platform sustainability.</p>
                </div>

                <div class="requirements">
                    <h3>Requirements:</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li>✓ At least one active referral in the past 9 days</li>
                        <li>✓ Referrals must be from your active investment plans</li>
                        <li>✓ Referrals must meet minimum activity criteria</li>
                    </ul>
                </div>

                <div class="action-buttons">
                    <a href="user/referrals.php" class="btn btn-primary">View My Referrals</a>
                    <a href="logout.php" class="btn btn-outline">Logout</a>
                </div>
            </div>

            <p class="info-text">
                Need help? Contact our support team for assistance with your referral requirements.
            </p>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>