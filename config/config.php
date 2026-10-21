<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'registrarconnect_db');
define('DB_USER', 'root');
define('DB_PASS', '');

define('APP_NAME', 'RegistrarConnect');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}