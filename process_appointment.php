<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/');
}


if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    redirect('/?error=' . urlencode('Invalid request'));
}


$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
$address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS);
$phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
$car_license = filter_input(INPUT_POST, 'car_license', FILTER_SANITIZE_SPECIAL_CHARS);
$car_engine = filter_input(INPUT_POST, 'car_engine', FILTER_SANITIZE_SPECIAL_CHARS);
$appointment_date = filter_input(INPUT_POST, 'appointment_date', FILTER_SANITIZE_SPECIAL_CHARS);
$mechanic_id = filter_input(INPUT_POST, 'mechanic_id', FILTER_VALIDATE_INT);


if (empty($name) || empty($address) || empty($phone) || empty($car_license) || 
    empty($car_engine) || empty($appointment_date) || empty($mechanic_id)) {
    redirect('/?error=' . urlencode('All fields are required'));
}

if (!preg_match('/^[A-Z0-9-]+$/', $car_license)) {
    redirect('/?error=' . urlencode('Invalid car license format (use letters, numbers, or hyphens)'));
}

if (!preg_match('/^[A-Z0-9-]+$/', $car_engine)) {
    redirect('/?error=' . urlencode('Invalid car engine format (use letters, numbers, or hyphens)'));
}


if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $appointment_date)) {
    redirect('/?error=' . urlencode('Invalid date format (use YYYY-MM-DD)'));
}


try {
    
    $stmt = $pdo->prepare("SELECT name, max_appointments FROM mechanics WHERE mechanic_id = ?");
    $stmt->execute([$mechanic_id]);
    $mechanic = $stmt->fetch();
    
    if (!$mechanic) {
        redirect('/?error=' . urlencode('Invalid mechanic selected'));
    }

   
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM appointments 
        WHERE mechanic_id = ? 
        AND appointment_date = ? 
        AND status IN ('pending', 'confirmed')
    ");
    $stmt->execute([$mechanic_id, $appointment_date]);
    $booked_slots = $stmt->fetchColumn();
    
    $available_slots = $mechanic['max_appointments'] - $booked_slots;

    if ($available_slots <= 0) {
        redirect('/?error=' . urlencode($mechanic['name'] . ' is fully booked for this date'));
    }

    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM appointments a
        JOIN clients c ON a.client_id = c.client_id
        WHERE (c.phone = ? OR c.car_license = ?) 
        AND a.appointment_date = ?
    ");
    $stmt->execute([$phone, $car_license, $appointment_date]);
    
    if ($stmt->fetchColumn() > 0) {
        redirect('/?error=' . urlencode('You already have an appointment on this date'));
    }

    
    $clientData = [
        'name' => $name,
        'address' => $address,
        'phone' => $phone,
        'car_license' => $car_license,
        'car_engine' => $car_engine
    ];

    if (createAppointment($pdo, $clientData, $mechanic_id, $appointment_date)) {
        $_SESSION['appointment_success'] = true;
        redirect('/success.php');
    } else {
        redirect('/?error=' . urlencode('Failed to create appointment. Please try again'));
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    redirect('/?error=' . urlencode('System error. Please try again later'));
}
?>