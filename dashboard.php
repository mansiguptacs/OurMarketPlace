<?php
$pageTitle = "My Dashboard - OurMarketplace";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

requireLogin();

$conn = getDBConnection();
$user_id = getCurrentUserId();

// User info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Total visits
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM user_visits WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_visits = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Total reviews
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM reviews WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_reviews = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Companies visited (unique)
$stmt = $conn->prepare("SELECT COUNT(DISTINCT company_id) AS total FROM user_visits WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$companies_visited = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Average rating given
$stmt = $conn->prepare("SELECT COALESCE(AVG(rating), 0) AS avg FROM reviews WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$avg_rating_given = round($stmt->get_result()->fetch_assoc()['avg'], 1);
$stmt->close();

// My reviews (with product & company info)
$stmt = $conn->prepare("
    SELECT r.*, p.name AS product_name, p.id AS product_id, c.name AS company_name
    FROM reviews r
    JOIN products p ON p.id = r.product_id
    JOIN companies c ON c.id = p.company_id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
    LIMIT 10
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$my_reviews = $stmt->get_result();
$stmt->close();

// Visit breakdown by company
$stmt = $conn->prepare("
    SELECT c.id, c.name, COUNT(v.id) AS visit_count
    FROM user_visits v
    JOIN companies c ON c.id = v.company_id
    WHERE v.user_id = ?
    GROUP BY c.id
    ORDER BY visit_count DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$visit_breakdown = $stmt->get_result();
$stmt->close();

require_once __DIR__ . '/includes/header.php';
?>

<h2 class="mb-4"><i class="fas fa-tachometer-alt"></i> My Dashboard</h2>

<!-- User Info -->
<div class="card mb-4">
    <div class="card-body d-flex align-items-center">
        <div class="me-4">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:64px;height:64px;font-size:1.5rem;">
                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
            </div>
        </div>
        <div>
            <h4 class="mb-0"><?php echo htmlspecialchars($user['full_name']); ?></h4>
            <p class="text-muted mb-0">@<?php echo htmlspecialchars($user['username']); ?> &middot; <?php echo htmlspecialchars($user['email']); ?></p>
            <small class="text-muted">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></small>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <p class="display-6 fw-bold text-primary mb-0"><?php echo $total_visits; ?></p>
                <small class="text-muted">Total Visits</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <p class="display-6 fw-bold text-success mb-0"><?php echo $total_reviews; ?></p>
                <small class="text-muted">Reviews Written</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <p class="display-6 fw-bold text-warning mb-0"><?php echo $companies_visited; ?>/4</p>
                <small class="text-muted">Companies Visited</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <p class="display-6 fw-bold text-info mb-0"><?php echo $avg_rating_given; ?></p>
                <small class="text-muted">Avg Rating Given</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Visit Breakdown -->
    <div class="col-md-5 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Visits by Company</h5>
            </div>
            <div class="card-body">
                <?php if ($visit_breakdown->num_rows > 0): ?>
                    <?php while ($vb = $visit_breakdown->fetch_assoc()): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <a href="<?php echo baseUrl('/companies/view.php?id=' . $vb['id']); ?>" class="text-decoration-none small">
                                <?php echo htmlspecialchars($vb['name']); ?>
                            </a>
                            <span class="small text-muted"><?php echo $vb['visit_count']; ?> visits</span>
                        </div>
                        <?php
                        $percentage = $total_visits > 0 ? ($vb['visit_count'] / $total_visits) * 100 : 0;
                        ?>
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar" style="width:<?php echo $percentage; ?>%"></div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">No visits yet.</p>
                <?php endif; ?>
                <a href="<?php echo baseUrl('/tracking/history.php'); ?>" class="btn btn-outline-primary btn-sm mt-2">
                    View Full History
                </a>
            </div>
        </div>
    </div>

    <!-- My Reviews -->
    <div class="col-md-7 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-star"></i> My Reviews</h5>
            </div>
            <div class="card-body">
                <?php if ($my_reviews->num_rows > 0): ?>
                    <?php while ($review = $my_reviews->fetch_assoc()): ?>
                    <div class="border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo baseUrl('/products/view.php?id=' . $review['product_id']); ?>" class="text-decoration-none fw-semibold small">
                                <?php echo htmlspecialchars($review['product_name']); ?>
                            </a>
                            <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                        </div>
                        <small class="text-muted"><?php echo htmlspecialchars($review['company_name']); ?></small>
                        <div class="stars small">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $review['rating']): ?>
                                    <i class="fas fa-star"></i>
                                <?php else: ?>
                                    <i class="far fa-star empty"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <?php if (!empty($review['review_text'])): ?>
                            <p class="small text-muted mb-0">"<?php echo htmlspecialchars(substr($review['review_text'], 0, 100)); ?>"</p>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">You haven't written any reviews yet.</p>
                    <a href="<?php echo baseUrl('/products/index.php'); ?>" class="btn btn-outline-primary btn-sm">
                        Browse Products
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once __DIR__ . '/includes/footer.php';
?>
