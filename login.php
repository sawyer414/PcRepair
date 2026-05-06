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

    <?php
    session_start();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Database connection
        $host = '54.225.154.64'; // Adjust as needed
        $db = 'PcRepair'; // Replace with your database name
        $user = 'Sawyer'; // Replace with your DB username
        $pass = '/Royals2026'; // Replace with your DB password

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Query the Admins table
            $stmt = $pdo->prepare("SELECT ID, Username, Email, Password FROM Admins WHERE Username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['Password'])) {
                $_SESSION['admin_id'] = $admin['ID'];
                $_SESSION['admin_username'] = $admin['Username'];
                header('Location: admin.php');
                exit;
            } else {
                echo '<script>document.getElementById("login-status").textContent = "Invalid username or password.";</script>';
            }
        } catch (PDOException $e) {
            echo '<script>document.getElementById("login-status").textContent = "Database error. Please try again.";</script>';
        }
    }
    ?>
</body>
</html>