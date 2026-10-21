
<?php
define('DB_HOST', 'sql313.infinityfree.com');
define('DB_NAME', 'if0_43232742_registrarconnect_db');
define('DB_USER', 'if0_43232742');
define('DB_PASS', 'YOUR_INFINITYFREE_MYSQL_PASSWORD');

define('APP_NAME', 'RegistrarConnect');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
