<?php
// Script to hash existing plain text passwords in Admins table
// Run this once, then delete the file

$host = '54.225.154.64';
$db = 'PcRepair';
$user = 'Sawyer';
$pass = '/Royals2026';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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