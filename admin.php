<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
    <header class="topbar">
        <div class="container topbar__inner">
            <a class="brand" href="./index.html">
                <span class="brand__badge">PC</span>
                <span>
                    <strong>Benchside PC Repair & Custom Builds</strong>
                    <small>Admin Panel</small>
                </span>
            </a>
            <nav class="nav">
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <h1>Welcome to Admin Panel</h1>
                <p>Manage your site here.</p>
                <h2>Admins List</h2>
                <table class="card">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Database connection
                        $host = '54.225.154.64';
                        $db = 'PcRepair';
                        $user = 'Sawyer';
                        $pass = '/Royals2026';

                        try {
                            $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                            $stmt = $pdo->query("SELECT ID, Username, Email FROM Admins");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr><td>{$row['ID']}</td><td>{$row['Username']}</td><td>{$row['Email']}</td></tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='3'>Error loading admins.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <?php
    session_start();

    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }

    // Admin content here
    echo '<p>Logged in as: ' . htmlspecialchars($_SESSION['admin_username']) . '</p>';
    ?>
</body>
</html>