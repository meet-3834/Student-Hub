<?php
require_once __DIR__ . '/auth.php';
$currentUser = currentUser();
$errors = $_SESSION['form_errors'] ?? [];
$message = $_SESSION['form_message'] ?? '';
$success = $_SESSION['form_success'] ?? false;
unset($_SESSION['form_errors'], $_SESSION['form_message'], $_SESSION['form_success']);

$recordsFile = __DIR__ . '/data/records.json';
$records = [];
if (file_exists($recordsFile)) {
  $rawData = file_get_contents($recordsFile);
  $decoded = json_decode($rawData, true);
  if (is_array($decoded)) {
    $records = $decoded;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Contact — CHARUSAT</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <header class="site-header">
    <div class="container">
      <h1 class="brand">CHARUSAT</h1>
      <nav>
        <a href="index.html">Home</a>
        <a href="about.html">About</a>
        <a href="events.html">Events</a>
        <a href="students.html">Students</a>
        <a href="dashboard.html">Dashboard</a>
        <a href="contact.php" class="active">Contact</a>
        <a href="faq.html">FAQ</a>
        <a href="sitemap.html">Site Map</a>
      </nav>
    </div>
  </header>

  <main class="container">
    <div class="page-title-section">
      <h2>Contact Us</h2>
      <p class="subtitle">
        <strong>Practical 7:</strong> PHP Form Processing with Server-Side Validation, CSV/JSON File Storage &amp; CSRF Token Validation.
      </p>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error" style="margin-bottom: 20px; background:#fdecec; color:#b42318; padding:12px 16px; border-radius:8px; border:1px solid #efb4b4;">
        <?php foreach ($errors as $error): ?>
          <div><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
      </div>
    <?php elseif ($success): ?>
      <div class="alert alert-success" style="margin-bottom: 20px; background:#eafaf1; color:#167a43; padding:12px 16px; border-radius:8px; border:1px solid #bde8c7;">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <div class="split-layout">
      <div class="stack">
        <section class="card">
          <h3>Contact Information</h3>
          <p><strong>Address:</strong> CHARUSAT Campus, Changa – 388421, Gujarat, India</p>
          <p><strong>Phone:</strong> +91 2692 XXXXX</p>
          <p><strong>Email:</strong> <a href="mailto:info@charusat.edu.in">info@charusat.edu.in</a></p>
        </section>

        <section class="card">
          <h3>Office Hours</h3>
          <p><strong>Administration:</strong> Mon–Fri 09:10–4:20</p>
          <p><strong>Admissions:</strong> Mon–Sat 09:30–16:30</p>
        </section>

        <section class="card">
          <h3>Directions</h3>
          <p>Changa is near Anand; regular local buses and taxis connect from Anand railway station. <a href="https://maps.google.com" target="_blank">View on map</a></p>
        </section>
      </div>

      <section class="card">
        <h3>Get in touch</h3>
        <form action="process.php" method="POST" class="contact-form">
          <!-- Practical 7 Advanced Extension: CSRF Token Field -->
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
          
          <label>Name<input type="text" name="name" required placeholder="Your Name"></label>
          <label>Email<input type="email" name="email" required placeholder="Your Email Address"></label>
          <label>Phone<input type="tel" name="phone" required placeholder="Your Phone Number"></label>
          <label>Subject<input type="text" name="subject" required placeholder="Subject"></label>
          <label>Message<textarea name="message" required placeholder="Write your message here..."></textarea></label>
          <button class="btn full" type="submit">Send Message</button>
        </form>
      </section>
    </div>

    <!-- Practical 7 Intermediate Extension: Display Stored CSV/JSON Records on Webpage -->
    <section class="card" style="margin-top: 30px;">
      <div class="section-header-flex">
        <div>
          <h3 style="margin: 0;">📁 Stored Records (CSV &amp; JSON Storage)</h3>
          <p style="color: var(--muted); font-size: 0.9rem; margin-top: 4px;">Data safely written to <code>data/records.csv</code> and <code>data/records.json</code>.</p>
        </div>
        <span class="source-badge">Practical 7 Live File Storage</span>
      </div>

      <?php if (empty($records)): ?>
        <p style="color: var(--muted); margin-top: 12px;">No records submitted yet. Submit the contact form above to test file storage.</p>
      <?php else: ?>
        <div style="overflow-x:auto; margin-top: 16px;">
          <table style="width:100%; border-collapse:collapse; font-size:0.92rem;">
            <thead>
              <tr style="background: rgba(37, 99, 235, 0.08);">
                <th style="padding:10px; border:1px solid var(--border); text-align:left;">Name</th>
                <th style="padding:10px; border:1px solid var(--border); text-align:left;">Email</th>
                <th style="padding:10px; border:1px solid var(--border); text-align:left;">Phone</th>
                <th style="padding:10px; border:1px solid var(--border); text-align:left;">Subject</th>
                <th style="padding:10px; border:1px solid var(--border); text-align:left;">Message</th>
                <th style="padding:10px; border:1px solid var(--border); text-align:left;">Submitted At</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($records as $record): ?>
                <tr>
                  <td style="padding:10px; border:1px solid var(--border); vertical-align:top; font-weight:600;">
                    <?php echo htmlspecialchars($record['name'] ?? ''); ?>
                  </td>
                  <td style="padding:10px; border:1px solid var(--border); vertical-align:top;">
                    <?php echo htmlspecialchars($record['email'] ?? ''); ?>
                  </td>
                  <td style="padding:10px; border:1px solid var(--border); vertical-align:top;">
                    <?php echo htmlspecialchars($record['phone'] ?? ''); ?>
                  </td>
                  <td style="padding:10px; border:1px solid var(--border); vertical-align:top;">
                    <?php echo htmlspecialchars($record['subject'] ?? ''); ?>
                  </td>
                  <td style="padding:10px; border:1px solid var(--border); vertical-align:top;">
                    <?php echo htmlspecialchars($record['message'] ?? ''); ?>
                  </td>
                  <td style="padding:10px; border:1px solid var(--border); vertical-align:top; color:var(--muted); font-size:0.85rem;">
                    <?php echo htmlspecialchars($record['submitted_at'] ?? ''); ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container">
      <p>CHARUSAT • Changa, Gujarat</p>
    </div>
  </footer>
  <script src="script.js"></script>
</body>

</html>
