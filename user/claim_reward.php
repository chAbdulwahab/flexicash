<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_id'])) {
    $userId = $_SESSION['user_id'];
    $planId = intval($_POST['plan_id']);

    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
        exit();
    }

    // Check 24-hour cooldown
    $stmt = $conn->prepare("SELECT watched_at FROM video_watches WHERE user_id = ? AND plan_id = ? ORDER BY watched_at DESC LIMIT 1");
    $stmt->bind_param("ii", $userId, $planId);
    $stmt->execute();
    $result = $stmt->get_result();
    $lastWatched = $result->fetch_assoc();

    if ($lastWatched && (time() - strtotime($lastWatched['watched_at'])) < 86400) {
        echo json_encode(['success' => false, 'message' => 'You can only claim once every 24 hours for this plan.']);
        exit();
    }

    // Get plan investment
    $stmt = $conn->prepare("SELECT investment FROM plans WHERE id = ?");
    $stmt->bind_param("i", $planId);
    $stmt->execute();
    $result = $stmt->get_result();
    $plan = $result->fetch_assoc();

    if (!$plan) {
        echo json_encode(['success' => false, 'message' => 'Invalid plan.']);
        exit();
    }

    $rewardAmount = $plan['investment'] * 0.125;

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update user's balance
        $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->bind_param("di", $rewardAmount, $userId);
        $stmt->execute();

        // Record watch
        $stmt = $conn->prepare("INSERT INTO video_watches (user_id, plan_id, watched_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $userId, $planId);
        $stmt->execute();

        // Record earnings
        $stmt = $conn->prepare("INSERT INTO earnings (user_id, amount, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("id", $userId, $rewardAmount);
        $stmt->execute();

        $conn->commit();
        echo json_encode([
            'success' => true, 
            'message' => 'Reward of PKR ' . number_format($rewardAmount, 2) . ' claimed successfully!'
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false, 
            'message' => 'Error processing reward: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}
// Do not add a closing PHP tag or any whitespace after this line
