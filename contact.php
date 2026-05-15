<?php
// ── Database config ──────────────────────────────────────────────
$host   = "54.225.154.64";       // your DB host
$dbname = "PcRepair";   // your database name
$user   = "Sawyer";   // your DB username
$pass   = "/Royals2026";   // your DB password
// ────────────────────────────────────────────────────────────────
 
$success = null;
$error   = null;
 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = trim($_POST["name"]    ?? "");
    $email   = trim($_POST["email"]   ?? "");
    $message = trim($_POST["message"] ?? "");
 
    if (!$name || !$email || !$message) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        try {
            $pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
 
            $stmt = $pdo->prepare(
                "INSERT INTO contacts (name, email, message, created_at)
                 VALUES (:name, :email, :message, NOW())"
            );
            $stmt->execute([
                ":name"    => $name,
                ":email"   => $email,
                ":message" => $message,
            ]);
 
            $success = "Message sent! We'll be in touch soon.";
        } catch (PDOException $e) {
            $error = "Something went wrong. Please try again or email us directly.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta
      name="description"
      content="Contact Benchside PC Repair & Custom Builds to book a diagnostic, ask about upgrades, or get a custom build quote."
    />
    <title>Contact | Benchside PC Repair & Custom Builds</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="./styles.css" />
    <link rel="icon" type="png/image" href="./favicon.png" />
  </head>
  <body>
    <header class="topbar">
      <div class="container topbar__inner">
        <a class="brand" href="./index.html">
          <span class="brand__badge">PC</span>
          <span>
            <strong>Benchside PC Repair & Custom Builds</strong>
            <small>Repair • Upgrades • Custom Builds</small>
          </span>
        </a>
        <nav class="nav" id="primary-nav" aria-label="Primary navigation">
          <a href="./index.html">Home</a>
          <a href="./about.html">About</a>
          <a href="./builds.html">Builds</a>
          <a href="./contact.php" aria-current="page">Contact</a>
          <a href="./pricing.html">Pricing</a>
        </nav>
        <button class="nav-toggle" aria-label="Open navigation menu" aria-expanded="false" aria-controls="primary-nav">
          <span class="nav-toggle__bar"></span>
          <span class="nav-toggle__bar"></span>
          <span class="nav-toggle__bar"></span>
        </button>
      </div>
    </header>
 
    <main>
      <section class="hero">
        <div class="container grid-2">
          <div>
            <p class="eyebrow">Contact</p>
            <h1>Tell us what your PC needs.</h1>
            <p>Call, email, or send a message and I will help you plan the next step.</p>
            <p><strong>Phone:</strong> <a href="tel:+16804449304">(315) 577-3663</a></p>
            <p><strong>Email:</strong> <a href="mailto:benchsiderepair@gmail.com">benchsiderepair@gmail.com</a></p>
            <p><strong>Hours:</strong> Thur-Sat, 6:00 PM - 9:00 PM</p>
          </div>
 
          <form id="contact-form" class="card form" method="POST" action="./contact.php">
            <label for="name">Name</label>
            <input id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" />
 
            <label for="email">Email</label>
            <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
 
            <label for="message">Message</label>
            <textarea id="message" name="message" rows="5" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
 
            <button class="button" type="submit">Send</button>
 
            <?php if ($success): ?>
              <p id="form-status" aria-live="polite" style="color: green;"><?php echo $success; ?></p>
            <?php elseif ($error): ?>
              <p id="form-status" aria-live="polite" style="color: red;"><?php echo $error; ?></p>
            <?php else: ?>
              <p id="form-status" aria-live="polite"></p>
            <?php endif; ?>
          </form>
        </div>
      </section>
    </main>
 
    <footer class="footer">
      <div class="container footer__inner">
        <p><strong>Benchside PC Repair & Custom Builds</strong></p>
        <p>Benchside PC Repair & Custom Builds</p>
      </div>
    </footer>
 
    <script>
      const navToggle = document.querySelector('.nav-toggle');
      const nav = document.getElementById('primary-nav');
      navToggle.addEventListener('click', () => {
        const expanded = navToggle.getAttribute('aria-expanded') === 'true';
        navToggle.setAttribute('aria-expanded', !expanded);
        nav.classList.toggle('is-open');
      });
      nav.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
          nav.classList.remove('is-open');
          navToggle.setAttribute('aria-expanded', false);
        });
      });
    </script>
  </body>
</html>