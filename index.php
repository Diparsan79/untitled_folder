<?php
require_once 'includes/functions.php';


// Get sorting parameter
$sort = $_GET['sort'] ?? 'popular';
$search = $_GET['search'] ?? '';

// Build query based on sorting
$orderBy = match($sort) {
    'recent' => 'i.created_at DESC',
    'trending' => 'i.vote_count DESC, i.created_at DESC',
    default => 'i.vote_count DESC, i.created_at DESC'
};

$pdo = getDBConnection();

// Build search query
$whereClause = '';
$params = [];
if (!empty($search)) {
    $whereClause = 'WHERE i.title LIKE ? OR i.description LIKE ?';
    $params = ["%$search%", "%$search%"];
}

// Only load issues if user is logged in
$issues = [];
if (isLoggedIn()) {
    // Get issues with user info, vote counts, and latest comment preview
    $query = "
        SELECT i.id, i.title, i.description, i.image_path, i.created_by, i.vote_count, i.created_at,
               u.username AS author_name,
               COUNT(DISTINCT c.id) AS comment_count,
               COALESCE(SUM(CASE WHEN v.vote_type = 'upvote' THEN 1 ELSE 0 END),0) AS upvotes,
               COALESCE(SUM(CASE WHEN v.vote_type = 'downvote' THEN 1 ELSE 0 END),0) AS downvotes,
               (SELECT c2.content FROM comments c2 WHERE c2.issue_id = i.id ORDER BY c2.created_at DESC LIMIT 1) AS latest_comment,
               (SELECT u2.username FROM comments c3 JOIN users u2 ON u2.id = c3.user_id WHERE c3.issue_id = i.id ORDER BY c3.created_at DESC LIMIT 1) AS latest_comment_author
        FROM issues i
        LEFT JOIN users u ON i.created_by = u.id
        LEFT JOIN comments c ON i.id = c.issue_id
        LEFT JOIN votes v ON i.id = v.issue_id
        $whereClause
        GROUP BY i.id, i.title, i.description, i.image_path, i.created_by, i.vote_count, i.created_at, u.username
        ORDER BY $orderBy
        LIMIT 50
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $issues = $stmt->fetchAll();
}
?>

<?php include __DIR__ . '/partials/header.php'; ?>

    <div class="layout">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        <main class="main">
            <section class="hero">
                <h1>Community Voice — Education Community</h1>
                <p>Share educational issues, vote on priorities, and work together for better education.</p>
                <div style="margin-top:16px;display:flex;gap:12px;flex-wrap:wrap;">
                    <?php if (isLoggedIn()): ?>
                        <a href="issues/create.php" class="btn">Post New Issue</a>
                    <?php else: ?>
                        <a href="auth/register.php" class="btn">Join Community</a>
                        <a href="auth/login.php" class="btn secondary">Sign In</a>
                    <?php endif; ?>
                </div>
            </section>
            
    <!-- Main Content -->
    <div class="container my-5">
        <!-- Messages -->
        <?php echo displayMessage(); ?>
        
        <?php if (isLoggedIn()): ?>
            <!-- Sorting and Stats -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="btn-group" role="group">
                        <a href="?sort=popular<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="btn <?php echo $sort === 'popular' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <i class="fas fa-fire me-1"></i>Most Popular
                        </a>
                        <a href="?sort=recent<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="btn <?php echo $sort === 'recent' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <i class="fas fa-clock me-1"></i>Recent
                        </a>
                        <a href="?sort=trending<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="btn <?php echo $sort === 'trending' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <i class="fas fa-trending-up me-1"></i>Trending
                        </a>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <span class="text-muted">
                        <i class="fas fa-list me-1"></i>
                        <?php echo count($issues); ?> issues found
                    </span>
                </div>
            </div>

            <!-- Issues List -->
            <?php if (empty($issues)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h3 class="text-muted">No issues found</h3>
                    <p class="text-muted">
                        <?php if (!empty($search)): ?>
                            Try adjusting your search terms
                        <?php else: ?>
                            Be the first to post a community issue!
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-4">
                    <?php foreach ($issues as $issue): ?>
                        <?php 
                            $initial = strtoupper(substr((string)$issue['author_name'], 0, 1));
                            $desc = (string)$issue['description'];
                            $tag = 'education';
                            $lower = strtolower($desc . ' ' . (string)$issue['title']);
                            if (str_contains($lower, 'road') || str_contains($lower, 'traffic')) { $tag = 'infrastructure'; }
                            if (str_contains($lower, 'trash') || str_contains($lower, 'pollution') || str_contains($lower, 'water')) { $tag = 'environment'; }
                            $latestComment = isset($issue['latest_comment']) ? trim((string)$issue['latest_comment']) : '';
                            $latestAuthor = isset($issue['latest_comment_author']) ? (string)$issue['latest_comment_author'] : '';
                        ?>
                        <div class="bg-white/80 dark:bg-[#2d3748] border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-4">
                            <div class="flex gap-4">
                                <!-- Vote rail -->
                                <div class="flex flex-col items-center justify-center w-12">
                                    <button class="vote-btn upvote-btn text-gray-600 hover:text-primary-600" data-issue-id="<?php echo $issue['id']; ?>" data-vote-type="upvote">
                                        <i class="fas fa-chevron-up"></i>
                                    </button>
                                    <div class="vote-count font-semibold <?php echo ($issue['vote_count']>0?'text-green-600':($issue['vote_count']<0?'text-red-600':'text-gray-500')); ?>" data-issue-id="<?php echo $issue['id']; ?>">
                                        <?php echo (int)$issue['vote_count']; ?>
                                    </div>
                                    <button class="vote-btn downvote-btn text-gray-600 hover:text-primary-600" data-issue-id="<?php echo $issue['id']; ?>" data-vote-type="downvote">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>

                                <!-- Main -->
                                <div class="flex-1">
                                    <div class="flex items-start justify-between">
                                        <a href="issues/view.php?id=<?php echo $issue['id']; ?>" class="text-lg font-semibold text-gray-900 dark:text-gray-100 hover:underline">
                                            <?php echo htmlspecialchars($issue['title']); ?>
                                        </a>
                                        <?php if ($issue['image_path']): ?>
                                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-image mr-1"></i> Image
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                        <?php echo htmlspecialchars(substr($issue['description'], 0, 160)); ?><?php if (strlen($issue['description'])>160) { echo '...'; } ?>
                                    </p>

                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="inline-flex items-center rounded-full bg-primary-50 text-primary-700 px-2 py-0.5 text-xs font-medium border border-primary-200">#<?php echo htmlspecialchars($tag); ?></span>
                                        <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-700 px-2 py-0.5 text-xs"><?php echo (int)$issue['comment_count']; ?> comments</span>
                                        <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-700 px-2 py-0.5 text-xs"><i class="fas fa-clock mr-1"></i><?php echo formatDate($issue['created_at']); ?></span>
                                    </div>

                                    <?php if (!empty($latestComment)): ?>
                                        <div class="mt-3 flex items-start gap-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-3">
                                            <div class="h-8 w-8 rounded-full bg-primary-600 text-white flex items-center justify-center text-xs font-bold">
                                                <?php echo htmlspecialchars(substr($latestAuthor !== '' ? $latestAuthor : 'U', 0, 1)); ?>
                                            </div>
                                            <div class="text-sm text-gray-700 dark:text-gray-200">
                                                <span class="font-semibold"><?php echo htmlspecialchars($latestAuthor !== '' ? $latestAuthor : 'User'); ?>:</span>
                                                <?php echo htmlspecialchars(substr($latestComment, 0, 120)); ?><?php if (strlen($latestComment)>120) { echo '...'; } ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-3 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="h-8 w-8 rounded-full bg-primary-600 text-white flex items-center justify-center text-xs font-bold">
                                                <?php echo htmlspecialchars($initial); ?>
                                            </div>
                                            <span class="text-sm text-gray-600 dark:text-gray-300">by <?php echo htmlspecialchars($issue['author_name']); ?></span>
                                        </div>
                                        <a href="issues/view.php?id=<?php echo $issue['id']; ?>" class="inline-flex items-center text-primary-700 hover:text-primary-800 text-sm font-medium">
                                            View Details <i class="fas fa-arrow-right ml-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- About Section for Non-logged Users -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0 mb-5">
                        <div class="card-body p-5 text-center">
                            <div class="mb-4">
                                <i class="fas fa-graduation-cap fa-4x text-primary mb-3"></i>
                                <h2 class="card-title h1 mb-3">Welcome to Shiksha Mitra</h2>
                                <p class="lead text-muted">Your Education Community Partner</p>
                            </div>
                            
                            <div class="row text-start">
                                <div class="col-md-6 mb-4">
                                    <div class="feature-item">
                                        <i class="fas fa-comments fa-2x text-primary mb-2"></i>
                                        <h5>Share Educational Issues</h5>
                                        <p class="text-muted">Post problems you see in your schools and community.</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="feature-item">
                                        <i class="fas fa-vote-yea fa-2x text-primary mb-2"></i>
                                        <h5>Vote on Priorities</h5>
                                        <p class="text-muted">Help the community prioritize what matters most.</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="feature-item">
                                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                        <h5>Community Collaboration</h5>
                                        <p class="text-muted">Work with teachers, parents, and students.</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="feature-item">
                                        <i class="fas fa-lightbulb fa-2x text-primary mb-2"></i>
                                        <h5>Find Solutions</h5>
                                        <p class="text-muted">Co-create practical solutions together.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-4 border-top">
                                <h4 class="text-primary mb-3">Join Our Education Community Today!</h4>
                                <p class="text-muted mb-4">
                                    To access educational issues and participate in discussions, please create an account.
                                </p>
                                <div class="d-grid gap-2 d-md-block">
                                    <a href="auth/register.php" class="btn btn-primary btn-lg px-4 me-2">
                                        <i class="fas fa-user-plus me-2"></i>Create Account
                                    </a>
                                    <a href="auth/login.php" class="btn btn-outline-primary btn-lg px-4">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer mt-5">
        <div class="container text-center py-4">
            <p class="text-muted mb-0">
                <i class="fas fa-heart text-danger me-1"></i>
                Built for educational community engagement and problem-solving<br>
                <small>Empowering education through community collaboration</small>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/voting.js"></script>
</body>
</html>
