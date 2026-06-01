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
    ?>
    <?php
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
                    <div id="db-view" style="margin-top:12px;overflow:auto;max-height:400px;border:1px solid #ddd;padding:8px;background:#fff"></div>
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
                        if (data.error) { view.innerHTML = '<p style="color:red">' + data.error + '</p>'; return; }
                        const cols = data.columns || [];
                        const rows = data.rows || [];
                        let html = '<table style="border-collapse:collapse;width:100%"><thead><tr>' + cols.map(c=>'<th style="border:1px solid #ccc;padding:6px;background:#f6f6f6">'+escapeHtml(c)+'</th>').join('') + '</tr></thead><tbody>';
                        for (const r of rows) {
                            html += '<tr>' + cols.map(c=>' <td style="border:1px solid #eee;padding:6px">'+escapeHtml(String(r[c] ?? ''))+'</td>').join('') + '</tr>';
                        }
                        html += '</tbody></table>';
                        view.innerHTML = html;
                    }

                    function escapeHtml(s){ return s.replace(/[&<>\"]/g, function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}); }

                    async function fetchTable() {
                        const table = select.value;
                        if (!table) { view.innerHTML = '<p>Select a table to view.</p>'; return; }
                        view.innerHTML = '<p>Loading...</p>';
                        try {
                            const res = await fetch('?action=fetch_table&table=' + encodeURIComponent(table));
                            const text = await res.text();
                            let data;
                            try {
                                data = JSON.parse(text);
                            } catch (err) {
                                // show raw server response for debugging
                                view.innerHTML = '<pre style="color:red">Server response:\n' + escapeHtml(text) + '</pre>';
                                return;
                            }
                            if (data.error) {
                                view.innerHTML = '<p style="color:red">' + escapeHtml(data.error) + '</p>';
                                return;
                            }
                            renderData(data);
                        } catch (e) {
                            view.innerHTML = '<p style="color:red">Fetch error: ' + escapeHtml(e.message || String(e)) + '</p>';
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