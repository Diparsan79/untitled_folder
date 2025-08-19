<?php
require_once __DIR__ . '/../includes/functions.php';
startSession();
$isLogged = isLoggedIn();
$isAdmin = !empty($_SESSION['is_admin']);
$path = $_SERVER['PHP_SELF'] ?? '';
function navItem($href, $label, $icon, $current) {
  $active = (strpos($current, $href) !== false) ? ' active-link' : '';
  echo '<a class="nav-card' . $active . '" href="' . htmlspecialchars($href) . '">'
      . '<span class="icon">' . $icon . '</span> ' . htmlspecialchars($label) . '</a>';
}
?>
<aside class="sidebar" aria-label="Primary Navigation">
  <nav>
    <?php navItem('/untitled_folder/index.php', 'Home', '🏠', $path); ?>
    <?php if ($isLogged): ?>
      <?php navItem('/untitled_folder/issues/create.php', 'Post Issue', '➕', $path); ?>
      <?php navItem('/untitled_folder/auth/logout.php', 'Logout', '🚪', $path); ?>
      <?php navItem('/untitled_folder/about.php', 'About Us', 'ℹ️', $path); ?>
      <?php navItem('/untitled_folder/contact.php', 'Contact', '✉️', $path); ?>
      <?php if ($isAdmin): ?>
        <?php navItem('/untitled_folder/admin/dashboard.php', 'Admin Dashboard', '🛠️', $path); ?>
      <?php endif; ?>
    <?php else: ?>
      <?php navItem('/untitled_folder/auth/register.php', 'Register', '📝', $path); ?>
      <?php navItem('/untitled_folder/auth/login.php', 'Login', '🔑', $path); ?>
      <?php navItem('/untitled_folder/contact.php', 'Contact', '✉️', $path); ?>
      <?php navItem('/untitled_folder/about.php', 'About Us', 'ℹ️', $path); ?>
    <?php endif; ?>
  </nav>
</aside>


