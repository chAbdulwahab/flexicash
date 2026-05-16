<?php
session_start();
require '../includes/config.php';

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    die("Missing parameters.");
}

$id = intval($_GET['id']);
$action = $_GET['action'];

if (!in_array($action, ['approve', 'reject'])) {
    die("Invalid action.");
}

$new_status = ($action === 'approve') ? 'approved' : 'rejected';

// Step 1: Get deposit info
$sql = "SELECT * FROM deposits WHERE id = ? AND status = 'pending'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $user_id = $row['user_id'];
    $amount = floatval($row['amount']);

    // Step 2: Update deposit status
    $update_sql = "UPDATE deposits SET status = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, 'si', $new_status, $id);
    mysqli_stmt_execute($update_stmt);

    if ($new_status === 'approved') {
        // Step 3: Update total_deposits in users table
        $update_user_sql = "UPDATE users SET total_deposits = total_deposits + ? WHERE id = ?";
        $user_stmt = mysqli_prepare($conn, $update_user_sql);
        mysqli_stmt_bind_param($user_stmt, 'di', $amount, $user_id);
        mysqli_stmt_execute($user_stmt);

        // Step 4: Check referral table for this user
        $ref_sql = "SELECT id, referrer_id FROM referrals WHERE referred_id = ?";
        $ref_stmt = mysqli_prepare($conn, $ref_sql);
        mysqli_stmt_bind_param($ref_stmt, 'i', $user_id);
        mysqli_stmt_execute($ref_stmt);
        $ref_result = mysqli_stmt_get_result($ref_stmt);

        if ($ref_row = mysqli_fetch_assoc($ref_result)) {
            $referral_id = $ref_row['id'];
            $referrer_id = $ref_row['referrer_id'];
            $commission = $amount * 0.10;

            // Step 5: Update referrer's total_balance
            $bonus_sql = "UPDATE users SET balance = balance + ? WHERE id = ?";
            $bonus_stmt = mysqli_prepare($conn, $bonus_sql);
            mysqli_stmt_bind_param($bonus_stmt, 'di', $commission, $referrer_id);
            mysqli_stmt_execute($bonus_stmt);

            // Step 6: Update referral table with commission and mark has_deposited
            $ref_update_sql = "UPDATE referrals SET commission = ?, has_deposited = 1 WHERE id = ?";
            $ref_update_stmt = mysqli_prepare($conn, $ref_update_sql);
            mysqli_stmt_bind_param($ref_update_stmt, 'di', $commission, $referral_id);
            mysqli_stmt_execute($ref_update_stmt);
        }
    }

    $_SESSION['msg'] = "Deposit ID $id has been $new_status.";
} else {
    $_SESSION['msg'] = "Deposit not found or already processed.";
}

header("Location: deposits.php");
exit;
