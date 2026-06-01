<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
    <?php
    session_start();

    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }

    // Database connection
    $host = '54.225.154.64';
    $db = 'PcRepair';
    $user = 'Sawyer';
    $pass = '/Royals2026';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die('Database connection failed.');
    }

    // Helper: allowed file extensions for page management
    $allowed_ext = ['html', 'htm', 'php'];

    // Helper: validate filename (no paths, simple chars)
    function valid_filename($name) {
        return preg_match('/^[A-Za-z0-9_\-]+\.(html|htm|php)$/', $name);
    }

    // Handle create page
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'create_page') {
            $fname = trim($_POST['filename'] ?? '');
            $content = $_POST['content'] ?? '';
            if (!valid_filename($fname)) {
                $msg = 'Invalid filename.';
            } else {
                $path = __DIR__ . DIRECTORY_SEPARATOR . $fname;
                if (file_exists($path)) {
                    $msg = 'File already exists.';
                } else {
                    if (file_put_contents($path, $content) !== false) {
                        $msg = 'Page created.';
                    } else {
                        $msg = 'Failed to create page.';
                    }
                }
            }
        }

        // Handle delete pages (checkbox list)
        if ($action === 'delete_pages' && !empty($_POST['delete_files'])) {
            $deleted = 0;
            foreach ($_POST['delete_files'] as $f) {
                if (!valid_filename($f)) continue;
                $path = __DIR__ . DIRECTORY_SEPARATOR . $f;
                if (is_file($path)) {
                    if (unlink($path)) $deleted++;
                }
            }
            $msg = "Deleted $deleted file(s).";
        }

        // Handle add admin
        if ($action === 'add_admin') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            if ($username === '' || $email === '' || $password === '') {
                $msg = 'All admin fields required.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                try {
                    $st = $pdo->prepare('INSERT INTO Admins (Username, Email, Password) VALUES (?, ?, ?)');
                    $st->execute([$username, $email, $hash]);
                    $msg = 'Admin added.';
                } catch (PDOException $e) {
                    $msg = 'Failed to add admin.';
                }
            }
        }

        // Handle delete admin
        if ($action === 'delete_admin' && !empty($_POST['admin_id'])) {
            $aid = (int)$_POST['admin_id'];
            // prevent deleting self
            if ($aid === (int)$_SESSION['admin_id']) {
                $msg = 'Cannot delete currently logged-in admin.';
            } else {
                try {
                    $st = $pdo->prepare('DELETE FROM Admins WHERE ID = ?');
                    $st->execute([$aid]);
                    $msg = 'Admin removed.';
                } catch (PDOException $e) {
                    $msg = 'Failed to remove admin.';
                }
            }
        }
    }
    ?>
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
                <?php if (!empty($msg)) echo '<p class="card">' . htmlspecialchars($msg) . '</p>'; ?>

                <h3>Admins</h3>
                <table class="card">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT ID, Username, Email FROM Admins");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $id = (int)$row['ID'];
                                $usern = htmlspecialchars($row['Username']);
                                $email = htmlspecialchars($row['Email']);
                                echo "<tr><td>$id</td><td>$usern</td><td>$email</td>";
                                echo "<td>";
                                if ($id !== (int)$_SESSION['admin_id']) {
                                    echo "<form method=\"post\" style=\"display:inline\">";
                                    echo "<input type=\"hidden\" name=\"action\" value=\"delete_admin\">";
                                    echo "<input type=\"hidden\" name=\"admin_id\" value=\"$id\">";
                                    echo "<button type=\"submit\">Remove</button>";
                                    echo "</form>";
                                } else {
                                    echo "(you)";
                                }
                                echo "</td></tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='4'>Error loading admins.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <h4>Add Admin</h4>
                <form method="post" class="card">
                    <input type="hidden" name="action" value="add_admin">
                    <label>Username: <input name="username" required></label><br>
                    <label>Email: <input name="email" type="email" required></label><br>
                    <label>Password: <input name="password" type="password" required></label><br>
                    <button type="submit">Add Admin</button>
                </form>

                <h3>Pages</h3>
                <?php
                // list files in current dir with allowed extensions
                $files = [];
                foreach (scandir(__DIR__) as $f) {
                    if (is_file(__DIR__ . DIRECTORY_SEPARATOR . $f)) {
                        $ext = pathinfo($f, PATHINFO_EXTENSION);
                        if (in_array(strtolower($ext), $allowed_ext) && $f !== basename(__FILE__)) {
                            $files[] = $f;
                        }
                    }
                }
                ?>

                <form method="post" class="card">
                    <input type="hidden" name="action" value="delete_pages">
                    <table>
                        <thead><tr><th>Delete</th><th>Filename</th></tr></thead>
                        <tbody>
                        <?php foreach ($files as $f): ?>
                            <tr>
                                <td><input type="checkbox" name="delete_files[]" value="<?php echo htmlspecialchars($f); ?>"></td>
                                <td><a href="<?php echo htmlspecialchars($f); ?>" target="_blank"><?php echo htmlspecialchars($f); ?></a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit">Delete Selected Pages</button>
                </form>

                <h4>Create Page</h4>
                <form method="post" class="card">
                    <input type="hidden" name="action" value="create_page">
                    <label>Filename (eg page.html): <input name="filename" required></label><br>
                    <label>Content:</label><br>
                    <textarea name="content" rows="10" cols="80"></textarea><br>
                    <button type="submit">Create Page</button>
                </form>
            </div>
        </section>
    </main>

    <?php
    // Admin content here
    echo '<p>Logged in as: ' . htmlspecialchars($_SESSION['admin_username']) . '</p>';
    ?>
</body>
</html>