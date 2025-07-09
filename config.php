<?php

if (ob_get_level() === 0) ob_start();


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


set_include_path(
    __DIR__ . PATH_SEPARATOR .
    '/Applications/XAMPP/htdocs/car-workshop-appointment/includes/'
);


if (headers_sent($file, $line)) {
    die("Headers sent in $file on line $line - fix this first!");
}


define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'workshop_appointments');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');


define('APP_NAME', 'Car Workshop Appointment System');
define('BASE_URL', 'http://localhost/Car_Workshop_Appointment_System/');
define('DEFAULT_TIMEZONE', 'Asia/Dhaka');

date_default_timezone_set(DEFAULT_TIMEZONE);


define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 300);
define('SESSION_TIMEOUT', 1800);


define('MAX_APPOINTMENTS_PER_MECHANIC', 4);
define('WORKING_HOURS_START', '08:00');
define('WORKING_HOURS_END', '18:00');


define('UPLOAD_DIR', __DIR__ . '/uploads');

define('LOG_DIR', __DIR__ . '/./logs');



if (!file_exists(LOG_DIR)) {
    mkdir(LOG_DIR, 0755, true);
}


define('ENVIRONMENT', 'development');


if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path' => BASE_URL,
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}


error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', LOG_DIR . '/php_errors.log');

if (ENVIRONMENT !== 'development') {
    ini_set('display_errors', 0);
}

function sanitize_output($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    if (!headers_sent()) {
        header("Location: " . BASE_URL . $url);
        exit();
    }
  
    echo '<script>window.location.href="'.BASE_URL.$url.'";</script>';
    exit();
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
?>