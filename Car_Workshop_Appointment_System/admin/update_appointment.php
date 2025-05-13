<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

// Verify admin session
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $date = $_POST['appointment_date'];
    $mechanic_id = (int)$_POST['mechanic_id'];
    $status = $_POST['status'];
    
    // Validation
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        die("Invalid date format");
    }
    
    // Update database
    $stmt = $pdo->prepare("
        UPDATE appointments 
        SET appointment_date = ?, 
            mechanic_id = ?, 
            status = ?,
            updated_at = NOW()
        WHERE appointment_id = ?
    ");
    
    $success = $stmt->execute([$date, $mechanic_id, $status, $appointment_id]);
    
    // Redirect back with success/error
    header("Location: admin.php?" . ($success ? "success=1" : "error=1"));
    exit();
}