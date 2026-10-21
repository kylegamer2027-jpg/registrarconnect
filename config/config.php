<?php
// Local XAMPP configuration. Change credentials if your MySQL setup differs.
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'registrarconnect');
define('DB_USER', 'root');
define('DB_PASS', '');
define('APP_NAME', 'RegistrarConnect');
if (session_status() === PHP_SESSION_NONE) session_start();
