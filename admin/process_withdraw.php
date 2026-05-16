<?php
session_start();
require '../includes/config.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Get withdrawal ID and action from URL
$withdrawal_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Validate input
if ($withdrawal_id <= 0 || !in_array($action, ['approved', 'reject'])) {
    $_SESSION['error'] = "Invalid request parameters";
    header('Location: withdrawals.php');
    exit();
}

// Initialize database connection
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Start transaction
$conn->begin_transaction();

try {
    // Get withdrawal details
    $stmt_select = $conn->prepare("SELECT w.*, u.balance FROM withdrawals w 
                           JOIN users u ON w.user_id = u.id 
                           WHERE w.id = ? AND w.status = 'pending'
                           FOR UPDATE"); // Added FOR UPDATE to lock the row
    if (!$stmt_select) {
        throw new Exception("Failed to prepare select statement: " . $conn->error);
    }
    
    $stmt_select->bind_param("i", $withdrawal_id);
    if (!$stmt_select->execute()) {
        throw new Exception("Failed to execute select: " . $stmt_select->error);
    }
    
    $result = $stmt_select->get_result();
    $withdrawal = $result->fetch_assoc();
    $stmt_select->close();

    if (!$withdrawal) {
        throw new Exception("Withdrawal request not found or already processed");
    }

    if ($action === 'approved') {
        // Update withdrawal status
        $stmt_update = $conn->prepare("UPDATE withdrawals SET status = 'approved' WHERE id = ?");
        if (!$stmt_update) {
            throw new Exception("Failed to prepare update statement: " . $conn->error);
        }
        
        $stmt_update->bind_param("i", $withdrawal_id);
        if (!$stmt_update->execute()) {
            throw new Exception("Failed to execute update: " . $stmt_update->error);
        }
        $stmt_update->close();

        $_SESSION['success'] = "Withdrawal request approved successfully";
    } else {
        // Reject withdrawal and refund the amount
        $stmt_reject = $conn->prepare("UPDATE withdrawals SET status = 'rejected' WHERE id = ?");
        if (!$stmt_reject) {
            throw new Exception("Failed to prepare reject statement: " . $conn->error);
        }
        
        $stmt_reject->bind_param("i", $withdrawal_id);
        if (!$stmt_reject->execute()) {
            throw new Exception("Failed to execute reject: " . $stmt_reject->error);
        }
        $stmt_reject->close();

        // Refund the amount to user's balance
        $stmt_refund = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        if (!$stmt_refund) {
            throw new Exception("Failed to prepare refund statement: " . $conn->error);
        }
        
        $stmt_refund->bind_param("di", $withdrawal['amount'], $withdrawal['user_id']);
        if (!$stmt_refund->execute()) {
            throw new Exception("Failed to execute refund: " . $stmt_refund->error);
        }
        $stmt_refund->close();

        $_SESSION['success'] = "Withdrawal request rejected and amount refunded to user";
    }

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    error_log("Withdrawal processing error: " . $e->getMessage());
    $_SESSION['error'] = "Error processing withdrawal: " . $e->getMessage();
} finally {
    $conn->close();
}

// Redirect back to withdrawals page
header('Location: withdrawals.php');
exit();