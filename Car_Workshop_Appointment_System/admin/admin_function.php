<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

if (!ob_get_level()) ob_start();
if (headers_sent($file, $line)) {
    die(json_encode([
        'success' => false,
        'error' => "Headers sent in $file:$line"
    ]));
}
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    die(json_encode(['success' => false, 'error' => 'Unauthorized']));
}
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR])) {
        header('Content-Type: application/json');
        die(json_encode([
            'success' => false,
            'error' => 'Server error: ' . $error['message'], 
            'code' => 'SYSTEM_FAILURE',
            'file' => $error['file'],
            'line' => $error['line']
        ]));
    }
});






error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', LOG_DIR . '/admin_errors.log');


$response = [
    'success' => false,
    'message' => 'Invalid request',
    'code' => 'invalid_request'
];
error_log("Admin function called: " . ($_POST['action'] ?? 'No action'));
try {
  
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Only POST requests allowed', 405);
    }

    
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_TIMEOUT,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }

  
    if (!isset($_SESSION['admin_logged_in'])) {
        throw new RuntimeException('Unauthorized access', 401);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $input = $_POST;
    }

    if (empty($input['action'])) {
        throw new InvalidArgumentException('Missing action parameter', 400);
    }

    
    switch ($input['action']) {
        case 'updateAppointment':
            
            $required = ['id', 'date', 'status'];
            if (array_diff($required, array_keys($input))) {
                throw new InvalidArgumentException('Missing required fields', 400);
            }

            
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['date'])) {
                throw new InvalidArgumentException('Invalid date format', 400);
            }

            if (!in_array($input['status'], ['pending', 'confirmed', 'completed'])) {
                throw new InvalidArgumentException('Invalid status value', 400);
            }

            $response = updateAppointment(
                $pdo,
                (int)$input['id'],
                $input['date'],
                (int)($input['mechanicId'] ?? 0),
                $input['status']
            );
            break;

        case 'getMechanics':
            
            $date = $input['date'] ?? date('Y-m-d');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                throw new InvalidArgumentException('Invalid date format', 400);
            }

            $response = [
                'success' => true,
                'mechanics' => getAvailableMechanics($pdo, $date)
            ];
            break;

        default:
            throw new RuntimeException('Invalid action specified', 400);
    }

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    $response = [
        'success' => false,
        'message' => $e->getMessage(),
        'code' => 'invalid_input'
    ];
} catch (RuntimeException $e) {
    http_response_code($e->getCode() ?: 500);
    $response = [
        'success' => false,
        'message' => $e->getMessage(),
        'code' => 'operation_failed'
    ];
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    http_response_code(500);
    $response = [
        'success' => false,
        'message' => 'Database operation failed',
        'code' => 'database_error'
    ];
} catch (Exception $e) {
    error_log("Unexpected Error: " . $e->getMessage());
    http_response_code(500);
    $response = [
        'success' => false,
        'message' => 'Internal server error',
        'code' => 'server_error'
    ];
} catch (Exception $e) {
    error_log("Critical Error: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(), 
        'code' => 'server_error',
        'debug' => ENVIRONMENT === 'development' ? [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace()
        ] : null
    ];
}



while (ob_get_level() > 0) ob_end_clean();
echo json_encode($response);
exit;
?>