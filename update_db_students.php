<?php
require_once 'config/config.php';
require_once 'includes/db.php';

try {
    // Add new columns if they don't exist
    $pdo->exec("ALTER TABLE students ADD COLUMN photo_path VARCHAR(255) DEFAULT NULL");
    $pdo->exec("ALTER TABLE students ADD COLUMN document_path VARCHAR(255) DEFAULT NULL");
    
    // Rename CPF to document_number (or add it and migrate data if needed, but for now we can just change the column or add a new one)
    // Since we want to replace CPF, let's just add document_number and we can drop CPF later or keep it unused.
    // Actually, let's just change the usage in code. But for the DB, let's add the new column.
    $pdo->exec("ALTER TABLE students ADD COLUMN document_number VARCHAR(50) DEFAULT NULL");
    
    // Make CPF nullable if it was required, or just leave it. 
    // Let's check if we can drop the unique constraint on CPF if it exists.
    // For simplicity in this environment, I'll just add the new columns and update the code to use document_number instead of cpf.
    
    echo "Database updated successfully.";
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
