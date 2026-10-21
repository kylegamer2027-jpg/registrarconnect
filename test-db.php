
<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = db();
    echo "Database connection successful!";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Database connection failed.";
    error_log($e->getMessage());
}
