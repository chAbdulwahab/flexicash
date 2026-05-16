<?php
session_start();
require '../includes/config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['plan_id'])) {
    die("Unauthorized or missing plan.");
}

$user_id = intval($_SESSION['user_id']);
$plan_id = intval($_POST['plan_id']);

// Step 1: Get plan amount
$plan_sql = "SELECT investment FROM plans WHERE id = ?";
$plan_stmt = mysqli_prepare($conn, $plan_sql);
mysqli_stmt_bind_param($plan_stmt, 'i', $plan_id);
mysqli_stmt_execute($plan_stmt);
$plan_result = mysqli_stmt_get_result($plan_stmt);

if (!$plan_row = mysqli_fetch_assoc($plan_result)) {
    $_SESSION['msg'] = "Invalid plan selected.";
    header("Location: plans.php");
    exit;
}

$plan_amount = floatval($plan_row['investment']);

// Step 2: Get user total_deposits
$user_sql = "SELECT total_deposits FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($conn, $user_sql);
mysqli_stmt_bind_param($user_stmt, 'i', $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user_row = mysqli_fetch_assoc($user_result);

if (!$user_row || floatval($user_row['total_deposits']) < $plan_amount) {
    $_SESSION['msg'] = "Insufficient deposit balance.";
    header("Location: plans.php");
    exit;
}

// Step 3: Deduct amount from user's total_deposits
$deduct_sql = "UPDATE users SET total_deposits = total_deposits - ? WHERE id = ?";
$deduct_stmt = mysqli_prepare($conn, $deduct_sql);
mysqli_stmt_bind_param($deduct_stmt, 'di', $plan_amount, $user_id);
mysqli_stmt_execute($deduct_stmt);

// Step 4: Insert investment
$start_date = date('Y-m-d');
$end_date = date('Y-m-d', strtotime('+30 days'));
$last_earning_date = null;
$is_active = 1;
$next_checkpoint = date('Y-m-d', strtotime('+9 days'));

$inv_sql = "INSERT INTO investments (user_id, plan_id, start_date, end_date, last_earning_date, is_active, next_checkpoint)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
$inv_stmt = mysqli_prepare($conn, $inv_sql);
mysqli_stmt_bind_param($inv_stmt, 'iisssis', $user_id, $plan_id, $start_date, $end_date, $last_earning_date, $is_active, $next_checkpoint);
mysqli_stmt_execute($inv_stmt);

$_SESSION['msg'] = "Investment started successfully!";
header("Location: dashboard.php");
exit;
