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

// Validate and process deposit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $method = $_POST['method'];
    $transaction_id = $_POST['transaction_id'];
    $user_id = $_SESSION['user_id'];
    
    // Check for pending deposits
    $stmt = $conn->prepare("SELECT id FROM deposits WHERE user_id = ? AND status = 'pending' LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "You already have a pending deposit. Please wait for it to be processed.";
        header('Location: deposit.php');
        exit();
    }
    
    // Validate amount
    if ($amount < 200) {
        $_SESSION['error'] = "Minimum deposit amount is ₨200";
        header('Location: deposit.php');
        exit();
    }
    
    // Handle file upload
    $target_dir = "../uploads/deposits/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = basename($_FILES["screenshot"]["name"]);
    $target_file = $target_dir . $user_id . '_' . time() . '_' . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is actual image
    $check = getimagesize($_FILES["screenshot"]["tmp_name"]);
    if ($check === false) {
        $_SESSION['error'] = "File is not an image";
        header('Location: deposit.php');
        exit();
    }
    
    // Check file size (max 2MB)
    if ($_FILES["screenshot"]["size"] > 2000000) {
        $_SESSION['error'] = "Image size must be less than 2MB";
        header('Location: deposit.php');
        exit();
    }
    
    // Allow certain file formats
    if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
        $_SESSION['error'] = "Only JPG, JPEG, PNG & GIF files are allowed";
        header('Location: deposit.php');
        exit();
    }
    
    // Try to upload file
    if (!move_uploaded_file($_FILES["screenshot"]["tmp_name"], $target_file)) {
        $_SESSION['error'] = "Error uploading file";
        header('Location: deposit.php');
        exit();
    }
    
    // Insert deposit record
    try {
        $stmt = $conn->prepare("INSERT INTO deposits 
            (user_id, amount, method, transaction_id, screenshot, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("idsss", $user_id, $amount, $method, $transaction_id, $target_file);
        $stmt->execute();
        
        $_SESSION['success'] = "Deposit request submitted successfully! It will be processed shortly.";
        header('Location: deposit.php');
        exit();
    } catch(Exception $e) {
        $_SESSION['error'] = "Error processing deposit: " . $e->getMessage();
        header('Location: deposit.php');
        exit();
    }
} else {
    header('Location: deposit.php');
    exit();
}
?>