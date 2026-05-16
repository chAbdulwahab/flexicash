<?php
session_start();
require_once '../includes/config.php';

// Initialize MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Validate form submission
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method";
    header('Location: withdraw.php');
    exit();
}

// Get and validate form data
$amount = floatval($_POST['amount'] ?? 0);
$method = trim($_POST['method'] ?? '');
$account_number = trim($_POST['account_number'] ?? '');
$account_name = trim($_POST['account_name'] ?? '');

// Validate minimum amount (50 PKR)
if ($amount < 50) {
    $_SESSION['error'] = "Minimum withdrawal amount is ₨50";
    header('Location: withdraw.php');
    exit();
}

// Validate payment method
if (!in_array($method, ['jazzcash', 'easypaisa'])) {
    $_SESSION['error'] = "Invalid payment method selected";
    header('Location: withdraw.php');
    exit();
}

// Validate account details
if (empty($account_number) || empty($account_name)) {
    $_SESSION['error'] = "Please provide complete account details";
    header('Location: withdraw.php');
    exit();
}

// Check user balance with row locking
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
if (!$stmt) {
    $_SESSION['error'] = "Database error: " . $conn->error;
    header('Location: withdraw.php');
    exit();
}

$stmt->bind_param("i", $_SESSION['user_id']);
if (!$stmt->execute()) {
    $_SESSION['error'] = "Error checking balance: " . $stmt->error;
    header('Location: withdraw.php');
    exit();
}

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['balance'] < $amount) {
    $_SESSION['error'] = "Insufficient balance for withdrawal";
    header('Location: withdraw.php');
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Deduct from user balance
    $update_balance = $conn->prepare("UPDATE users SET balance = balance - ?, total_withdrawals = total_withdrawals + ? WHERE id = ?");
    if (!$update_balance) {
        throw new Exception("Failed to prepare balance update: " . $conn->error);
    }
    $update_balance->bind_param("ddi", $amount, $amount, $_SESSION['user_id']);
    if (!$update_balance->execute()) {
        throw new Exception("Failed to update balance: " . $update_balance->error);
    }

    // Record withdrawal request - removed account_name field
    $insert_withdrawal = $conn->prepare("INSERT INTO withdrawals (user_id, amount, method, account_number, status, created_at) 
                           VALUES (?, ?, ?, ?, 'pending', NOW())");
    if (!$insert_withdrawal) {
        throw new Exception("Failed to prepare withdrawal insert: " . $conn->error);
    }
    $insert_withdrawal->bind_param("idss", $_SESSION['user_id'], $amount, $method, $account_number);
    if (!$insert_withdrawal->execute()) {
        throw new Exception("Failed to insert withdrawal: " . $insert_withdrawal->error);
    }

    $conn->commit();
    $_SESSION['success'] = "Withdrawal request of ₨" . number_format($amount, 2) . " submitted successfully!";
} catch (Exception $e) {
    $conn->rollback();
    error_log("Withdrawal error for user " . $_SESSION['user_id'] . ": " . $e->getMessage());
    $_SESSION['error'] = "Error processing withdrawal: " . $e->getMessage();
} finally {
    if (isset($update_balance)) $update_balance->close();
    if (isset($insert_withdrawal)) $insert_withdrawal->close();
    $conn->close();
}

header('Location: withdraw.php');
exit();
?>