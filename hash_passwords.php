<?php
// Script to hash existing plain text passwords in Admins table
// Run this once, then delete the file

require_once __DIR__ . '/db.php';

try {
    // Get all admins
    $stmt = $pdo->query("SELECT ID, Password FROM Admins");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($admins as $admin) {
        // Assuming current passwords are plain text, hash them
        $hashed = password_hash($admin['Password'], PASSWORD_DEFAULT);
        
        // Update the password
        $updateStmt = $pdo->prepare("UPDATE Admins SET Password = ? WHERE ID = ?");
        $updateStmt->execute([$hashed, $admin['ID']]);
        
        echo "Hashed password for ID {$admin['ID']}<br>";
    }

    echo "All passwords hashed successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>