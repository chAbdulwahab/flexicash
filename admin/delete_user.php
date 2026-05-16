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

// Validate user ID
if ($user_id <= 0) {
    $_SESSION['error'] = "Invalid user ID";
    header('Location: users.php');
    exit();
}

// Prevent self-deletion
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete your own account";
    header('Location: users.php');
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Delete user from database
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Optionally: Delete related records (deposits, etc.)
    // $stmt = $conn->prepare("DELETE FROM deposits WHERE user_id = ?");
    // $stmt->bind_param("i", $user_id);
    // $stmt->execute();

    $conn->commit();
    $_SESSION['success'] = "User deleted successfully";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
}

header('Location: users.php');
exit();
?>