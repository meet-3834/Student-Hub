<?php
session_start();
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
    <h2>Contact Us</h2>

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
          <label>Name<input type="text" name="name" required placeholder="Your Name"></label>
          <label>Email<input type="email" name="email" required placeholder="Your Email Address"></label>
          <label>Phone<input type="tel" name="phone" required placeholder="Your Phone Number"></label>
          <label>Subject<input type="text" name="subject" required placeholder="Subject"></label>
          <label>Message<textarea name="message" required placeholder="Write your message here..."></textarea></label>
          <button class="btn full" type="submit">Send Message</button>
        </form>
      </section>
    </div>

    <section class="card" style="margin-top: 30px;">
      <h3>Saved Records</h3>
      <?php if (empty($records)): ?>
        <p>No records saved yet.</p>
      <?php else: ?>
        <div style="overflow-x:auto;">
          <table style="width:100%; border-collapse:collapse; margin-top:10px; font-size:0.96rem;">
            <thead>
              <tr style="background:#f3f4f6;">
                <th style="padding:10px; border:1px solid #dfe3e8; text-align:left;">Name</th>
                <th style="padding:10px; border:1px solid #dfe3e8; text-align:left;">Email</th>
                <th style="padding:10px; border:1px solid #dfe3e8; text-align:left;">Phone</th>
                <th style="padding:10px; border:1px solid #dfe3e8; text-align:left;">Subject</th>
                <th style="padding:10px; border:1px solid #dfe3e8; text-align:left;">Message</th>
                <th style="padding:10px; border:1px solid #dfe3e8; text-align:left;">Submitted At</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($records as $record): ?>
                <tr>
                  <td style="padding:10px; border:1px solid #dfe3e8; vertical-align:top;">
                    <?php echo htmlspecialchars($record['name'] ?? ''); ?>
                  </td>
                  <td style="padding:10px; border:1px solid #dfe3e8; vertical-align:top;">
                    <?php echo htmlspecialchars($record['email'] ?? ''); ?>
                  </td>
                  <td style="padding:10px; border:1px solid #dfe3e8; vertical-align:top;">
                    <?php echo htmlspecialchars($record['phone'] ?? ''); ?>
                  </td>
                  <td style="padding:10px; border:1px solid #dfe3e8; vertical-align:top;">
                    <?php echo htmlspecialchars($record['subject'] ?? ''); ?>
                  </td>
                  <td style="padding:10px; border:1px solid #dfe3e8; vertical-align:top;">
                    <?php echo htmlspecialchars($record['message'] ?? ''); ?>
                  </td>
                  <td style="padding:10px; border:1px solid #dfe3e8; vertical-align:top;">
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
