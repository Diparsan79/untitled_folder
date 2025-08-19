<?php require_once __DIR__ . '/includes/functions.php'; ?>
<?php include __DIR__ . '/partials/header.php'; ?>
<div class="layout">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <main class="main">
    <section class="card">
      <h2>Contact</h2>
      <p class="muted">Have questions or feedback? Send us a message.</p>
      <form method="post" action="#">
        <div class="grid grid-2">
          <div>
            <label class="muted">Full Name</label>
            <input class="nav-card" name="name" placeholder="Your name" />
          </div>
          <div>
            <label class="muted">Email</label>
            <input class="nav-card" type="email" name="email" placeholder="you@example.com" />
          </div>
        </div>
        <div style="margin-top:12px;">
          <label class="muted">Message</label>
          <textarea class="nav-card" name="message" rows="5" placeholder="How can we help?"></textarea>
        </div>
        <div style="margin-top:12px;">
          <button class="btn" type="submit">Send</button>
        </div>
      </form>
    </section>
  </main>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>


