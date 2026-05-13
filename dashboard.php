<?php
$pageTitle = "Dashboard - OurMarketplace";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

invalidateStaleSessionUser();
requireLogin();

$conn = getDBConnection();
$user_id = (int) getCurrentUserId();

function om_fetch_count(mysqli $conn, string $sql, int $userId): int {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['count_value'] ?? 0);
}

$totalVisits = om_fetch_count($conn, "SELECT COUNT(*) AS count_value FROM user_visits WHERE user_id = ?", $user_id);
$visitedCompanies = om_fetch_count($conn, "SELECT COUNT(DISTINCT company_id) AS count_value FROM user_visits WHERE user_id = ?", $user_id);
$wishlistCount = om_fetch_count($conn, "SELECT COUNT(*) AS count_value FROM wishlist WHERE user_id = ?", $user_id);
$reviewCount = om_fetch_count($conn, "SELECT COUNT(*) AS count_value FROM reviews WHERE user_id = ?", $user_id);

$stmt = $conn->prepare("
    SELECT MAX(visited_at) AS last_visit
    FROM user_visits
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$lastVisitRow = $stmt->get_result()->fetch_assoc();
$stmt->close();
$lastVisit = $lastVisitRow['last_visit'] ?? null;

$stmt = $conn->prepare("
    SELECT c.id, c.name, COUNT(v.id) AS visit_count
    FROM user_visits v
    JOIN companies c ON c.id = v.company_id
    WHERE v.user_id = ?
    GROUP BY c.id
    ORDER BY visit_count DESC, c.name
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$favoriteCompany = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("
    SELECT v.visited_at, v.product_id, v.company_id, c.name AS company_name, p.name AS product_name
    FROM user_visits v
    JOIN companies c ON c.id = v.company_id
    LEFT JOIN products p ON p.id = v.product_id
    WHERE v.user_id = ?
    ORDER BY v.visited_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recentVisits = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("
    SELECT r.created_at, r.rating, r.review_text, p.id AS product_id, p.name AS product_name,
           c.id AS company_id, c.name AS company_name
    FROM reviews r
    JOIN products p ON p.id = r.product_id
    JOIN companies c ON c.id = p.company_id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recentReviews = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("
    SELECT w.added_at, p.id, p.name, p.price, p.image_url, c.id AS company_id, c.name AS company_name
    FROM wishlist w
    JOIN products p ON p.id = w.product_id
    JOIN companies c ON c.id = p.company_id
    WHERE w.user_id = ?
    ORDER BY w.added_at DESC
    LIMIT 4
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wishlistPreview = $stmt->get_result();
$stmt->close();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h2 class="mb-1"><i class="fas fa-gauge-high"></i> My Dashboard</h2>
        <p class="text-muted mb-0">A quick view of your activity across all marketplace storefronts.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?php echo baseUrl('/companies/index.php'); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-store"></i> Browse Companies
        </a>
        <a href="<?php echo baseUrl('/compare/index.php'); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-code-compare"></i> Compare (<?php echo compareCount(); ?>)
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Total product/company visits</p>
                <h3 class="mb-0 text-primary"><?php echo $totalVisits; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Companies explored</p>
                <h3 class="mb-0 text-primary"><?php echo $visitedCompanies; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Wishlist items</p>
                <h3 class="mb-0 text-primary"><?php echo $wishlistCount; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Reviews written</p>
                <h3 class="mb-0 text-primary"><?php echo $reviewCount; ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Highlights</h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <strong>Favorite storefront:</strong>
                        <?php if ($favoriteCompany): ?>
                            <a href="<?php echo baseUrl('/companies/view.php?id=' . (int) $favoriteCompany['id']); ?>">
                                <?php echo htmlspecialchars($favoriteCompany['name']); ?>
                            </a>
                            <span class="text-muted">(<?php echo (int) $favoriteCompany['visit_count']; ?> visits)</span>
                        <?php else: ?>
                            <span class="text-muted">No visits recorded yet.</span>
                        <?php endif; ?>
                    </li>
                    <li class="mb-2">
                        <strong>Last recorded activity:</strong>
                        <?php if ($lastVisit): ?>
                            <span class="text-muted"><?php echo date('M d, Y h:i A', strtotime($lastVisit)); ?></span>
                        <?php else: ?>
                            <span class="text-muted">No visits yet.</span>
                        <?php endif; ?>
                    </li>
                    <li class="mb-2">
                        <strong>Compare tray:</strong>
                        <span class="text-muted"><?php echo compareCount(); ?> product<?php echo compareCount() === 1 ? '' : 's'; ?> selected.</span>
                    </li>
                    <li class="mb-0">
                        <strong>Quick demo path:</strong>
                        <span class="text-muted">Wishlist + visits + reviews + rankings in one place.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Quick Actions</h5>
                <div class="d-grid gap-2">
                    <a href="<?php echo baseUrl('/tracking/history.php'); ?>" class="btn btn-outline-primary">
                        <i class="fas fa-history"></i> View visit history
                    </a>
                    <a href="<?php echo baseUrl('/wishlist/index.php'); ?>" class="btn btn-outline-danger">
                        <i class="fas fa-heart"></i> Open wishlist
                    </a>
                    <a href="<?php echo baseUrl('/rankings/marketplace_top5.php'); ?>" class="btn btn-outline-success">
                        <i class="fas fa-ranking-star"></i> Open top products
                    </a>
                    <a href="<?php echo baseUrl('/products/search.php'); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-search"></i> Search marketplace
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Recent Visits</h5>
                <?php if ($recentVisits->num_rows > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php while ($visit = $recentVisits->fetch_assoc()): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between gap-3">
                                    <div>
                                        <?php if (!empty($visit['product_id']) && !empty($visit['product_name'])): ?>
                                            <a href="<?php echo baseUrl('/products/view.php?id=' . (int) $visit['product_id']); ?>">
                                                <?php echo htmlspecialchars($visit['product_name']); ?>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo baseUrl('/companies/view.php?id=' . (int) $visit['company_id']); ?>">
                                                Company storefront
                                            </a>
                                        <?php endif; ?>
                                        <div class="small text-muted">
                                            <?php echo htmlspecialchars($visit['company_name']); ?>
                                        </div>
                                    </div>
                                    <small class="text-muted text-nowrap"><?php echo date('M d, h:i A', strtotime($visit['visited_at'])); ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No visit activity yet. Explore a storefront to start building history.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Recent Reviews</h5>
                <?php if ($recentReviews->num_rows > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php while ($review = $recentReviews->fetch_assoc()): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between gap-3">
                                    <div>
                                        <a href="<?php echo baseUrl('/products/view.php?id=' . (int) $review['product_id']); ?>">
                                            <?php echo htmlspecialchars($review['product_name']); ?>
                                        </a>
                                        <div class="small text-muted">
                                            <?php echo htmlspecialchars($review['company_name']); ?> · <?php echo (int) $review['rating']; ?>/5
                                        </div>
                                        <?php if (!empty($review['review_text'])): ?>
                                            <div class="small mt-1"><?php echo htmlspecialchars(substr($review['review_text'], 0, 90)); ?><?php echo strlen($review['review_text']) > 90 ? '...' : ''; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted text-nowrap"><?php echo date('M d', strtotime($review['created_at'])); ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">You have not reviewed any products yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">Wishlist Snapshot</h5>
        <?php if ($wishlistPreview->num_rows > 0): ?>
            <div class="row g-3">
                <?php while ($item = $wishlistPreview->fetch_assoc()): ?>
                    <div class="col-md-6 col-xl-3">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-semibold">
                                <a href="<?php echo baseUrl('/products/view.php?id=' . (int) $item['id']); ?>">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </a>
                            </div>
                            <div class="small text-muted mb-2"><?php echo htmlspecialchars($item['company_name']); ?></div>
                            <div class="text-primary fw-bold">$<?php echo number_format((float) $item['price'], 2); ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-muted mb-0">Your wishlist is empty. Save products to compare and revisit them later.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
require_once __DIR__ . '/includes/footer.php';
?>
