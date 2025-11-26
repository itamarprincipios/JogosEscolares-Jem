<?php
require_once 'config/config.php';
require_once 'includes/db.php';

    $pdo = getConnection();
    $stmt = $pdo->query("DESCRIBE students");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns in students table: " . implode(", ", $columns) . "\n";
    
    // Check uploads directory
    $uploadDir = __DIR__ . '/uploads';
    if (!file_exists($uploadDir)) {
        echo "Uploads directory does not exist.\n";
    } else {
        echo "Uploads directory exists.\n";
        echo "Writable: " . (is_writable($uploadDir) ? "Yes" : "No") . "\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
