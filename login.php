<?php
session_start();

try {
    $pdo = new PDO("mysql:host=54.225.154.64;dbname=PcRepair", "Sawyer", "/Royals2026");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT ID, Username, Email, Password FROM Admins WHERE Username = ?");
    $stmt->execute([$username]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['Password'])) {
        $_SESSION['admin_id'] = $admin['ID'];
        $_SESSION['admin_username'] = $admin['Username'];

        header('Location: admin.php');
        exit;
    } else {
        echo "Invalid login.";
    }

} catch (PDOException $e) {
    echo "Server error.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
    <main>
        <section class="hero">
            <div class="container">
                <h1>Admin Login</h1>
                <form action="login.php" method="post" class="card form">
                    <label for="username">Username</label>
                    <input id="username" name="username" required />

                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required />

                    <button class="button" type="submit">Login</button>
                    <p id="login-status" aria-live="polite"></p>
                </form>
            </div>
        </section>
    </main>

</body>
</html>