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

    // Database connection
    $host = '54.225.154.64';
    $db = 'PcRepair';
    $user = 'Sawyer';
    $pass = '/Royals2026';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die('Database connection failed: ' . $e->getMessage());
    }

    // Allow AJAX table fetch to receive JSON error instead of HTML redirect
    $is_fetch_ajax = (isset($_GET['action']) && $_GET['action'] === 'fetch_table');
    if (!isset($_SESSION['admin_id'])) {
        if ($is_fetch_ajax) {
            header('Content-Type: application/json', true, 401);
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }
        header('Location: login.php');
        exit;
    }

    // Initialize variables
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $msg = '';
    $allowed_ext = ['html', 'php'];

    // Handle add_admin
    if ($action === 'add_admin' && !empty($_POST['username'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        try {
            $hashed_pwd = password_hash($password, PASSWORD_BCRYPT);
            $st = $pdo->prepare('INSERT INTO Admins (Username, Email, Password) VALUES (?, ?, ?)');
            $st->execute([$username, $email, $hashed_pwd]);
            $msg = 'Admin added successfully.';
        } catch (PDOException $e) {
            $msg = 'Error adding admin: ' . $e->getMessage();
        }
    }

    // Handle update_admin
    if ($action === 'update_admin' && !empty($_POST['admin_id'])) {
        $admin_id = (int)$_POST['admin_id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        try {
            if (!empty($_POST['password'])) {
                $hashed_pwd = password_hash($_POST['password'], PASSWORD_BCRYPT);
                $st = $pdo->prepare('UPDATE Admins SET Username = ?, Email = ?, Password = ? WHERE ID = ?');
                $st->execute([$username, $email, $hashed_pwd, $admin_id]);
            } else {
                $st = $pdo->prepare('UPDATE Admins SET Username = ?, Email = ? WHERE ID = ?');
                $st->execute([$username, $email, $admin_id]);
            }
            $msg = 'Admin updated successfully.';
        } catch (PDOException $e) {
            $msg = 'Error updating admin: ' . $e->getMessage();
        }
    }

    // Handle delete_admin
    if ($action === 'delete_admin' && !empty($_POST['admin_id'])) {
        $aid = (int)$_POST['admin_id'];
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

    // Handle create_page
    if ($action === 'create_page' && !empty($_POST['filename'])) {
        $filename = $_POST['filename'];
        $content = $_POST['content'] ?? '';
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), $allowed_ext)) {
            $filepath = __DIR__ . DIRECTORY_SEPARATOR . $filename;
            if (!file_exists($filepath)) {
                file_put_contents($filepath, $content);
                $msg = 'Page created: ' . htmlspecialchars($filename);
            } else {
                $msg = 'File already exists.';
            }
        } else {
            $msg = 'Invalid file extension. Allowed: ' . implode(', ', $allowed_ext);
        }
    }

    // Handle delete_pages
    if ($action === 'delete_pages' && !empty($_POST['delete_files'])) {
        $deleted = 0;
        foreach ($_POST['delete_files'] as $f) {
            $ext = pathinfo($f, PATHINFO_EXTENSION);
            if (in_array(strtolower($ext), $allowed_ext)) {
                $filepath = __DIR__ . DIRECTORY_SEPARATOR . $f;
                if (file_exists($filepath) && is_file($filepath)) {
                    unlink($filepath);
                    $deleted++;
                }
            }
        }
        $msg = "Deleted $deleted file(s).";
    }

    // Handle fetch_table (AJAX)
    if ($action === 'fetch_table') {
        header('Content-Type: application/json');
        $table = $_GET['table'] ?? '';
        if (empty($table)) {
            echo json_encode(['error' => 'No table specified']);
            exit;
        }
        try {
            $stmt = $pdo->query("DESCRIBE " . $table);
            $columns = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $columns[] = $row['Field'];
            }
            
            $stmt = $pdo->query("SELECT * FROM " . $table);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['columns' => $columns, 'rows' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
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
                                // Edit link
                                echo "<a href=\"?edit_id=$id\">Edit</a> ";
                                if ($id !== (int)$_SESSION['admin_id']) {
                                    echo "<form method=\"post\" style=\"display:inline;margin-left:6px\">";
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

                <?php
                // If editing an admin, show edit form
                if (!empty($_GET['edit_id'])):
                    $edit_id = (int)$_GET['edit_id'];
                    $st = $pdo->prepare('SELECT ID, Username, Email FROM Admins WHERE ID = ?');
                    $st->execute([$edit_id]);
                    $editAdmin = $st->fetch(PDO::FETCH_ASSOC);
                    if ($editAdmin):
                ?>
                    <h4>Edit Admin (ID <?php echo (int)$editAdmin['ID']; ?>)</h4>
                    <form method="post" class="card">
                        <input type="hidden" name="action" value="update_admin">
                        <input type="hidden" name="admin_id" value="<?php echo (int)$editAdmin['ID']; ?>">
                        <label>Username: <input name="username" value="<?php echo htmlspecialchars($editAdmin['Username']); ?>" required></label><br>
                        <label>Email: <input name="email" type="email" value="<?php echo htmlspecialchars($editAdmin['Email']); ?>" required></label><br>
                        <label>New Password (leave blank to keep current): <input name="password" type="password"></label><br>
                        <button type="submit">Update Admin</button>
                        <a href="admin.php">Cancel</a>
                    </form>
                <?php
                    endif;
                endif;
                    ?>
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

                <h3>Live DB Viewer</h3>
                <div class="card">
                    <label>Table: 
                        <select id="db-table-select">
                            <option value="">-- select --</option>
                            <?php
                            // populate tables
                            try {
                                $tbls = [];
                                $q = $pdo->query('SHOW TABLES');
                                while ($r = $q->fetch(PDO::FETCH_NUM)) {
                                    $tbls[] = $r[0];
                                }
                                foreach ($tbls as $t) {
                                    echo '<option value="' . htmlspecialchars($t) . '">' . htmlspecialchars($t) . '</option>';
                                }
                            } catch (Exception $e) {
                                echo '<option disabled>Error loading tables</option>';
                            }
                            ?>
                        </select>
                    </label>
                    <label style="margin-left:10px"><input type="checkbox" id="auto-refresh"> Auto-refresh</label>
                    <label style="margin-left:10px">Interval (s): <input id="refresh-interval" type="number" value="5" min="1" style="width:60px"></label>
                    <button id="refresh-now" type="button">Refresh</button>
                    <div id="db-view" class="db-view"></div>
                </div>

                <script>
                (function(){
                    const select = document.getElementById('db-table-select');
                    const view = document.getElementById('db-view');
                    const btn = document.getElementById('refresh-now');
                    const auto = document.getElementById('auto-refresh');
                    const intervalInput = document.getElementById('refresh-interval');
                    let timer = null;

                    function renderData(data) {
                        if (data.error) {
                            renderTextTable('Server Error', data.error);
                            return;
                        }
                        const cols = data.columns || [];
                        const rows = data.rows || [];
                        let html = '<table class="data-table"><thead><tr>' + cols.map(c => '<th>' + escapeHtml(c) + '</th>').join('') + '</tr></thead><tbody>';
                        if (rows.length === 0) {
                            html += '<tr><td colspan="' + Math.max(cols.length, 1) + '" class="empty-state">No rows found.</td></tr>';
                        } else {
                            for (const r of rows) {
                                html += '<tr>' + cols.map(c => '<td>' + escapeHtml(String(r[c] ?? '')) + '</td>').join('') + '</tr>';
                            }
                        }
                        html += '</tbody></table>';
                        view.innerHTML = html;
                    }

                    function renderTextTable(header, text) {
                        const escaped = escapeHtml(text.trim() || '(empty response)');
                        const html = '<table class="data-table"><thead><tr><th>' + escapeHtml(header) + '</th></tr></thead>' +
                            '<tbody><tr><td><pre style="margin:0;white-space:pre-wrap;word-break:break-word;color:#ffb3b3;background:transparent;border:none;">' + escaped + '</pre></td></tr></tbody></table>';
                        view.innerHTML = html;
                    }

                    function escapeHtml(s){ return s.replace(/[&<>\"]/g, function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}); }

                    async function fetchTable() {
                        const table = select.value;
                        if (!table) {
                            renderTextTable('Info', 'Select a table to view.');
                            return;
                        }
                        renderTextTable('Info', 'Loading...');
                        try {
                            const res = await fetch('?action=fetch_table&table=' + encodeURIComponent(table));
                            const text = await res.text();
                            let data;
                            try {
                                data = JSON.parse(text);
                            } catch (err) {
                                renderTextTable('Server response', text);
                                return;
                            }
                            if (data.error) {
                                renderTextTable('Server Error', data.error);
                                return;
                            }
                            renderData(data);
                        } catch (e) {
                            renderTextTable('Fetch error', e.message || String(e));
                            console.error(e);
                        }
                    }

                    btn.addEventListener('click', fetchTable);
                    select.addEventListener('change', fetchTable);

                    function startTimer(){
                        stopTimer();
                        const s = Math.max(1, parseInt(intervalInput.value,10)||5);
                        timer = setInterval(fetchTable, s*1000);
                    }
                    function stopTimer(){ if (timer) { clearInterval(timer); timer = null; } }

                    auto.addEventListener('change', function(){ if (auto.checked) startTimer(); else stopTimer(); });
                    intervalInput.addEventListener('change', function(){ if (auto.checked) startTimer(); });
                })();
                </script>
            </div>
        </section>
    </main>

    <?php
    // Admin content here
    echo '<p>Logged in as: ' . htmlspecialchars($_SESSION['admin_username']) . '</p>';
    ?>
</body>
</html>