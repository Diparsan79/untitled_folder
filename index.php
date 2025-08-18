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
    // Get issues with user info and vote counts
    $query = "
        SELECT i.id, i.title, i.description, i.image_path, i.created_by, i.vote_count, i.created_at,
               u.username AS author_name,
               COUNT(DISTINCT c.id) AS comment_count,
               COALESCE(SUM(CASE WHEN v.vote_type = 'upvote' THEN 1 ELSE 0 END),0) AS upvotes,
               COALESCE(SUM(CASE WHEN v.vote_type = 'downvote' THEN 1 ELSE 0 END),0) AS downvotes
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shiksha Mitra - शिक्षा मित्र</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>
                Shiksha Mitra <span class="nepali-text">शिक्षा मित्र</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="issues/create.php">Post Issue</a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Search Bar -->
                <form class="d-flex me-3" method="GET" action="">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search issues..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                
                <!-- User Menu -->
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                               data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="auth/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold text-white mb-3">
                शिक्षा मित्र - Education Community
            </h1>
            <p class="lead text-white-50 mb-4">
                शिक्षाको समस्या साझा गर्नुहोस्, मतदान गर्नुहोस्, र सँगै समाधान खोजौं<br>
                Share educational issues, vote on priorities, and work together for better education
            </p>
            <?php if (isLoggedIn()): ?>
                <a href="issues/create.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-2"></i>Post New Issue
                </a>
            <?php else: ?>
                <a href="auth/register.php" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-user-plus me-2"></i>Join Community
                </a>
                <a href="auth/login.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </a>
            <?php endif; ?>
        </div>
    </div>

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
                <div class="row">
                    <?php foreach ($issues as $issue): ?>
                        <div class="col-12 mb-4">
                            <div class="card issue-card h-100">
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Voting Section -->
                                        <div class="col-md-1 text-center">
                                            <div class="voting-section">
                                                <button class="btn btn-sm vote-btn upvote-btn" 
                                                        data-issue-id="<?php echo $issue['id']; ?>"
                                                        data-vote-type="upvote">
                                                    <i class="fas fa-chevron-up"></i>
                                                </button>
                                                
                                                <div class="vote-count <?php echo $issue['vote_count'] > 0 ? 'text-success' : ($issue['vote_count'] < 0 ? 'text-danger' : 'text-muted'); ?>">
                                                    <?php echo $issue['vote_count']; ?>
                                                </div>
                                                
                                                <button class="btn btn-sm vote-btn downvote-btn" 
                                                        data-issue-id="<?php echo $issue['id']; ?>"
                                                        data-vote-type="downvote">
                                                    <i class="fas fa-chevron-down"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Issue Content -->
                                        <div class="col-md-11">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="card-title mb-1">
                                                    <a href="issues/view.php?id=<?php echo $issue['id']; ?>" 
                                                       class="text-decoration-none">
                                                        <?php echo htmlspecialchars($issue['title']); ?>
                                                    </a>
                                                </h5>
                                                <?php if ($issue['image_path']): ?>
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-image me-1"></i>Has Image
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <p class="card-text text-muted">
                                                <?php echo htmlspecialchars(substr($issue['description'], 0, 200)); ?>
                                                <?php if (strlen($issue['description']) > 200): ?>...<?php endif; ?>
                                            </p>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="issue-meta">
                                                    <small class="text-muted">
                                                        <i class="fas fa-user me-1"></i>
                                                        Posted by <?php echo htmlspecialchars($issue['author_name']); ?>
                                                    </small>
                                                    <small class="text-muted ms-3">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?php echo formatDate($issue['created_at']); ?>
                                                    </small>
                                                    <small class="text-muted ms-3">
                                                        <i class="fas fa-comments me-1"></i>
                                                        <?php echo $issue['comment_count']; ?> comments
                                                    </small>
                                                </div>
                                                
                                                <a href="issues/view.php?id=<?php echo $issue['id']; ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    View Details
                                                </a>
                                            </div>
                                        </div>
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
                                <h2 class="card-title h1 mb-3">स्वागत छ Shiksha Mitra मा!</h2>
                                <p class="lead text-muted">Welcome to Shiksha Mitra - Your Education Community Partner</p>
                            </div>
                            
                            <div class="row text-start">
                                <div class="col-md-6 mb-4">
                                    <div class="feature-item">
                                        <i class="fas fa-comments fa-2x text-primary mb-2"></i>
                                        <h5>Share Educational Issues</h5>
                                        <p class="text-muted">शिक्षा सम्बन्धी समस्याहरू साझा गर्नुहोस् र समुदायसँग छलफल गर्नुहोस्।</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="feature-item">
                                        <i class="fas fa-vote-yea fa-2x text-primary mb-2"></i>
                                        <h5>Vote on Priorities</h5>
                                        <p class="text-muted">महत्वपूर्ण शैक्षिक मुद्दाहरूमा मतदान गर्नुहोस् र प्राथमिकता तय गर्नुहोस्।</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="feature-item">
                                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                        <h5>Community Collaboration</h5>
                                        <p class="text-muted">शिक्षकहरू, अभिभावकहरू र विद्यार्थीहरूसँग मिलेर काम गर्नुहोस्।</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="feature-item">
                                        <i class="fas fa-lightbulb fa-2x text-primary mb-2"></i>
                                        <h5>Find Solutions</h5>
                                        <p class="text-muted">शैक्षिक चुनौतीहरूका लागि सामूहिक समाधान खोज्नुहोस्।</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-4 border-top">
                                <h4 class="text-primary mb-3">Join Our Education Community Today!</h4>
                                <p class="text-muted mb-4">
                                    शिक्षा मित्र मा सामेल भएर नेपालको शिक्षा क्षेत्रमा सकारात्मक परिवर्तन ल्याउनुहोस्।<br>
                                    <em>To access educational issues and participate in discussions, please create an account.</em>
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
                शिक्षा मित्र - Built for educational community engagement and problem-solving<br>
                <small>Empowering Nepal's education system through community collaboration</small>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/voting.js"></script>
</body>
</html>
