<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    redirect('/admin/admin_login.php');
}


if (!isset($_SESSION['last_regeneration']) || time() - $_SESSION['last_regeneration'] > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

$appointments = getAllAppointments($pdo);
$mechanics = getAllMechanics($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | <?= sanitize_output(APP_NAME) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Admin Panel</h1>
            <div class="admin-info">
                <span>Welcome, <?= sanitize_output($_SESSION['admin_username'] ?? 'Admin') ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>
        <main>
            <div class="appointments-table">
                <h2>Appointment Management</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client Name</th>
                            <th>Phone</th>
                            <th>Car License</th>
                            <th>Appointment Date</th>
                            <th>Mechanic</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr data-appointment-id="<?= (int)$appointment['appointment_id'] ?>">
                                <td><?= (int)$appointment['appointment_id'] ?></td>
                                <td><?= sanitize_output($appointment['client_name'] ?? 'N/A') ?></td>
                                <td><?= sanitize_output($appointment['phone'] ?? 'N/A') ?></td>
                                <td><?= sanitize_output($appointment['car_license'] ?? 'N/A') ?></td>
                                <td>

                               
                                <table>
                                    
                                    <tbody>
                                        <?php //foreach ($appointments as $appointment): ?>
                                        <tr>
                                        <form method="POST" action="update_appointment.php">
                                            <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                                            
                                            <td><?= $appointment['client_name'] ?></td>
                                            
                                            <td>
                                            <input type="date" name="appointment_date" 
                                                    value="<?= sanitize_output($appointment['appointment_date'] ?? date('Y-m-d')) ?>">
                                            </td>
                                            
                                            <td>
                                            <select name="mechanic_id">
                                                <option value="0">-- No Mechanic --</option>
                                                <?php foreach ($mechanics as $mechanic): ?>
                                                <option value="<?= $mechanic['mechanic_id'] ?>"
                                                <?= ($mechanic['mechanic_id'] == $appointment['mechanic_id']) ? 'selected' : '' ?>>
                                                <?= sanitize_output($mechanic['name']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            </td>
                                            
                                            <td>
                                            <select name="status">
                                                <option value="pending" <?= $appointment['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="confirmed" <?= $appointment['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                <option value="completed" <?= $appointment['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                            </select>
                                            </td>
                                            
                                            <td>
                                            <button type="submit" name="save">Save</button>
                                            </td>
                                        </form>
                                        </tr>
                                        <?//php endforeach; ?>
                                    </tbody>
                                    </table>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script src="../assets/js/admin_script.js">
        const BASE_URL = '<?= BASE_URL ?>'; 
    </script>
</body>
</html>