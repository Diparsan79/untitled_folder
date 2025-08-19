<?php
require_once __DIR__ . '/../includes/functions.php';
startSession();
if (empty($_SESSION['is_admin'])) { redirect('index.php','Admin access required','warning'); }
$pdo = getDBConnection();
$csrf = generateCSRFToken();

$totalUsers = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$activeUsers = (int)$pdo->query('SELECT COUNT(*) FROM users WHERE last_login IS NOT NULL')->fetchColumn();
$totalIssues = (int)$pdo->query('SELECT COUNT(*) FROM issues')->fetchColumn();
$openIssues = (int)$pdo->query('SELECT COUNT(*) FROM issues')->fetchColumn();
$totalVotes = (int)$pdo->query('SELECT COUNT(*) FROM votes')->fetchColumn();
$flagged = (int)$pdo->query('SELECT COUNT(*) FROM comments WHERE is_flagged = 1')->fetchColumn();

$top7 = $pdo->query("SELECT id, title, vote_count FROM issues WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY vote_count DESC LIMIT 10")->fetchAll();
$top30 = $pdo->query("SELECT id, title, vote_count FROM issues WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY vote_count DESC LIMIT 10")->fetchAll();
$mostActive = $pdo->query("SELECT u.username, (SELECT COUNT(*) FROM issues i WHERE i.created_by=u.id)+(SELECT COUNT(*) FROM comments c WHERE c.user_id=u.id) AS score FROM users u ORDER BY score DESC LIMIT 10")->fetchAll();
$byCommunity = $pdo->query("SELECT c.name AS community, COUNT(i.id) AS issues_count FROM users u JOIN communities c ON c.id=u.community_id LEFT JOIN issues i ON i.created_by=u.id GROUP BY c.id ORDER BY issues_count DESC")->fetchAll();

?>
<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="layout">
  <?php include __DIR__ . '/../partials/sidebar.php'; ?>
  <main class="main">
    <section class="grid grid-3">
      <div class="card"><div class="muted">Total Users</div><h3><?php echo $totalUsers; ?></h3></div>
      <div class="card"><div class="muted">Active Users</div><h3><?php echo $activeUsers; ?></h3></div>
      <div class="card"><div class="muted">Total Issues</div><h3><?php echo $totalIssues; ?></h3></div>
      <div class="card"><div class="muted">Open Issues</div><h3><?php echo $openIssues; ?></h3></div>
      <div class="card"><div class="muted">Total Votes</div><h3><?php echo $totalVotes; ?></h3></div>
      <div class="card"><div class="muted">Flagged Content</div><h3><?php echo $flagged; ?></h3></div>
    </section>

    <section class="grid grid-2" style="margin-top:16px;">
      <div class="card">
        <h3>Moderation — Issues</h3>
        <?php foreach ($pdo->query('SELECT i.id, i.title, u.username FROM issues i JOIN users u ON u.id=i.created_by ORDER BY i.created_at DESC LIMIT 12') as $row): ?>
          <form method="post" class="d-flex justify-content-between align-items-center border-bottom py-2">
            <div><strong>#<?php echo (int)$row['id']; ?></strong> <?php echo htmlspecialchars($row['title']); ?> <small class="muted">by <?php echo htmlspecialchars($row['username']); ?></small></div>
            <div>
              <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
              <input type="hidden" name="issue_id" value="<?php echo (int)$row['id']; ?>">
              <button class="btn secondary" name="action" value="delete_issue">Delete</button>
            </div>
          </form>
        <?php endforeach; ?>
      </div>
      <div class="card">
        <h3>Moderation — Comments</h3>
        <?php foreach ($pdo->query('SELECT c.id, c.comment, u.username FROM comments c JOIN users u ON u.id=c.user_id ORDER BY c.created_at DESC LIMIT 12') as $row): ?>
          <form method="post" class="d-flex justify-content-between align-items-center border-bottom py-2">
            <div class="text-truncate" style="max-width:70%;"><small class="muted"><?php echo htmlspecialchars($row['username']); ?>:</small> <?php echo htmlspecialchars(substr($row['comment'],0,120)); ?></div>
            <div>
              <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
              <input type="hidden" name="comment_id" value="<?php echo (int)$row['id']; ?>">
              <button class="btn secondary" name="action" value="delete_comment">Delete</button>
            </div>
          </form>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="grid grid-2" style="margin-top:16px;">
      <div class="card">
        <h3>Trending — Last 7 Days</h3>
        <ol>
          <?php foreach ($top7 as $r): ?><li><?php echo htmlspecialchars($r['title']); ?> <small class="muted">(<?php echo (int)$r['vote_count']; ?>)</small></li><?php endforeach; ?>
        </ol>
        <h3 style="margin-top:12px;">Trending — Last 30 Days</h3>
        <ol>
          <?php foreach ($top30 as $r): ?><li><?php echo htmlspecialchars($r['title']); ?> <small class="muted">(<?php echo (int)$r['vote_count']; ?>)</small></li><?php endforeach; ?>
        </ol>
      </div>
      <div class="card">
        <h3>Most Active Users</h3>
        <ol>
          <?php foreach ($mostActive as $u): ?><li><?php echo htmlspecialchars($u['username']); ?> <small class="muted">score <?php echo (int)$u['score']; ?></small></li><?php endforeach; ?>
        </ol>
        <h3 style="margin-top:12px;">Issues by Community</h3>
        <ul>
          <?php foreach ($byCommunity as $c): ?><li><?php echo htmlspecialchars($c['community']); ?> — <strong><?php echo (int)$c['issues_count']; ?></strong></li><?php endforeach; ?>
        </ul>
      </div>
    </section>

    <section class="grid grid-2" style="margin-top:16px;">
      <div class="card">
        <h3>Users</h3>
        <div class="muted">Search and manage users</div>
        <form method="get" class="d-flex gap-2" style="margin:8px 0;">
          <input class="nav-card" type="text" name="q" placeholder="Search username/email">
          <button class="btn secondary" type="submit">Search</button>
        </form>
        <div style="overflow:auto;">
          <table class="table">
            <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Verified</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($pdo->query('SELECT id, username, email, is_verified FROM users ORDER BY id DESC LIMIT 20') as $u): ?>
                <tr>
                  <td><?php echo (int)$u['id']; ?></td>
                  <td><?php echo htmlspecialchars($u['username']); ?></td>
                  <td><?php echo htmlspecialchars($u['email']); ?></td>
                  <td><?php echo (int)$u['is_verified'] ? 'Yes' : 'No'; ?></td>
                  <td>
                    <form method="post" class="d-inline">
                      <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                      <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                      <button class="btn secondary" name="action" value="suspend_user">Toggle Suspend</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card">
        <h3>Voting Activity</h3>
        <canvas id="votesChart" height="120"></canvas>
      </div>
    </section>
  </main>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  var ctx=document.getElementById('votesChart'); if(!ctx) return;
  var chart=new Chart(ctx,{type:'line',data:{labels:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],datasets:[{label:'Votes',data:[3,5,2,8,6,4,7],borderColor:'#7c3aed',backgroundColor:'rgba(124,58,237,.2)',tension:.3,fill:true}]},options:{plugins:{legend:{display:true}},scales:{x:{grid:{display:false}},y:{beginAtZero:true}}}});
})();
</script>


