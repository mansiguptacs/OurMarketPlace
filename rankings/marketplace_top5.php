<?php
$pageTitle = "Top 5 Products - OurMarketplace";
require_once __DIR__ . '/../includes/header.php';

$conn = getDBConnection();

// Ranking method (default: best_rated)
$method = $_GET['method'] ?? 'best_rated';
$valid_methods = ['best_rated', 'most_visited', 'most_reviewed'];
if (!in_array($method, $valid_methods)) {
    $method = 'best_rated';
}

// Build query based on method
switch ($method) {
    case 'most_visited':
        $sql = "
            SELECT p.*, c.name AS company_name, c.id AS company_id,
                   COUNT(DISTINCT v.id) AS visit_count,
                   COALESCE(AVG(r.rating), 0) AS avg_rating,
                   COUNT(DISTINCT r.id) AS review_count
            FROM products p
            JOIN companies c ON c.id = p.company_id
            LEFT JOIN user_visits v ON v.product_id = p.id
            LEFT JOIN reviews r ON r.product_id = p.id
            GROUP BY p.id
            ORDER BY visit_count DESC, avg_rating DESC
            LIMIT 5
        ";
        $method_label = "Most Visited";
        break;

    case 'most_reviewed':
        $sql = "
            SELECT p.*, c.name AS company_name, c.id AS company_id,
                   COUNT(DISTINCT v.id) AS visit_count,
                   COALESCE(AVG(r.rating), 0) AS avg_rating,
                   COUNT(DISTINCT r.id) AS review_count
            FROM products p
            JOIN companies c ON c.id = p.company_id
            LEFT JOIN user_visits v ON v.product_id = p.id
            LEFT JOIN reviews r ON r.product_id = p.id
            GROUP BY p.id
            ORDER BY review_count DESC, avg_rating DESC
            LIMIT 5
        ";
        $method_label = "Most Reviewed";
        break;

    default: // best_rated
        $sql = "
            SELECT p.*, c.name AS company_name, c.id AS company_id,
                   COUNT(DISTINCT v.id) AS visit_count,
                   COALESCE(AVG(r.rating), 0) AS avg_rating,
                   COUNT(DISTINCT r.id) AS review_count
            FROM products p
            JOIN companies c ON c.id = p.company_id
            LEFT JOIN user_visits v ON v.product_id = p.id
            LEFT JOIN reviews r ON r.product_id = p.id
            GROUP BY p.id
            ORDER BY avg_rating DESC, review_count DESC
            LIMIT 5
        ";
        $method_label = "Best Rated";
        break;
}

$results = $conn->query($sql);
?>

<h2 class="mb-2">Top 5 Products - Marketplace</h2>
<p class="text-muted mb-4">The best products across all companies in our marketplace.</p>

<!-- Per-Company Rankings Link -->
<div class="mt-4 text-center">
    <a href="<?php echo baseUrl('/rankings/company_top5.php'); ?>" class="btn btn-outline-primary">
        <i class="fas fa-building"></i>Click to view Top 5 Products/Services Per Company
    </a>
</div>

<!-- Ranking Method Tabs -->
<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link <?php echo $method === 'best_rated' ? 'active' : ''; ?>"
           href="<?php echo baseUrl('/rankings/marketplace_top5.php?method=best_rated'); ?>">
            <i class="fas fa-star"></i> Best Rated
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $method === 'most_visited' ? 'active' : ''; ?>"
           href="<?php echo baseUrl('/rankings/marketplace_top5.php?method=most_visited'); ?>">
            <i class="fas fa-eye"></i> Most Visited
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $method === 'most_reviewed' ? 'active' : ''; ?>"
           href="<?php echo baseUrl('/rankings/marketplace_top5.php?method=most_reviewed'); ?>">
            <i class="fas fa-comments"></i> Most Reviewed
        </a>
    </li>
</ul>

<!-- Results -->
<?php if ($results && $results->num_rows > 0): ?>
<div class="row g-3">
    <?php $rank = 1; while ($product = $results->fetch_assoc()): ?>
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex align-items-center">
                <div class="rank-badge me-3"><?php echo $rank; ?></div>
                <div class="flex-grow-1">
                    <h5 class="mb-1">
                        <a href="<?php echo baseUrl('/products/view.php?id=' . $product['id']); ?>" class="text-decoration-none">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </h5>
                    <p class="mb-0 small">
                        <a href="<?php echo baseUrl('/companies/view.php?id=' . $product['company_id']); ?>" class="text-muted text-decoration-none">
                            <i class="fas fa-store"></i> <?php echo htmlspecialchars($product['company_name']); ?>
                        </a>
                        <span class="badge-category ms-2"><?php echo htmlspecialchars($product['category']); ?></span>
                    </p>
                </div>
                <div class="text-end">
                    <div class="stars">
                        <?php
                        $avg = round($product['avg_rating'], 1);
                        for ($i = 1; $i <= 5; $i++):
                            if ($i <= $avg): ?>
                                <i class="fas fa-star"></i>
                            <?php elseif ($i - 0.5 <= $avg): ?>
                                <i class="fas fa-star-half-alt"></i>
                            <?php else: ?>
                                <i class="far fa-star empty"></i>
                            <?php endif; endfor; ?>
                    </div>
                    <small class="text-muted">
                        <?php echo $avg; ?>/5 &middot; <?php echo $product['review_count']; ?> reviews &middot; <?php echo $product['visit_count']; ?> visits
                    </small>
                    <div class="mt-2">
                        <a href="<?php echo baseUrl('/compare/add.php?product_id=' . $product['id']); ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-code-compare"></i> Compare
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $rank++; endwhile; ?>
</div>
<?php else: ?>
<div class="alert alert-info">
    No ranking data yet. Products need reviews or visits to appear here.
</div>
<?php endif; ?>



<?php
$conn->close();
require_once __DIR__ . '/../includes/footer.php';
?>
