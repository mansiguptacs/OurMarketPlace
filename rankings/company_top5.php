<?php
$pageTitle = "Top 5 Per Company - OurMarketplace";
require_once __DIR__ . '/../includes/header.php';

$conn = getDBConnection();

// Ranking method
$method = $_GET['method'] ?? 'best_rated';
$valid_methods = ['best_rated', 'most_visited', 'most_reviewed'];
if (!in_array($method, $valid_methods)) {
    $method = 'best_rated';
}

// Get all companies
$companies = $conn->query("SELECT * FROM companies ORDER BY id");

// Build ORDER BY clause based on method
switch ($method) {
    case 'most_visited':
        $order_by = "visit_count DESC, avg_rating DESC";
        $method_label = "Most Visited";
        break;
    case 'most_reviewed':
        $order_by = "review_count DESC, avg_rating DESC";
        $method_label = "Most Reviewed";
        break;
    default:
        $order_by = "avg_rating DESC, review_count DESC";
        $method_label = "Best Rated";
        break;
}
?>

<h2 class="mb-2">Top 5 Products Per Company</h2>
<p class="text-muted mb-4">See the best products within each company.</p>

<!-- Back to Marketplace Rankings -->
<div class="text-center">
    <a href="<?php echo baseUrl('/rankings/marketplace_top5.php'); ?>" class="btn btn-outline-primary">
        <i class="fas fa-trophy"></i>Click to view Marketplace-wide Top 5 products/services
    </a>
</div>

<!-- Ranking Method Tabs -->
<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link <?php echo $method === 'best_rated' ? 'active' : ''; ?>"
           href="<?php echo baseUrl('/rankings/company_top5.php?method=best_rated'); ?>">
            <i class="fas fa-star"></i> Best Rated
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $method === 'most_visited' ? 'active' : ''; ?>"
           href="<?php echo baseUrl('/rankings/company_top5.php?method=most_visited'); ?>">
            <i class="fas fa-eye"></i> Most Visited
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $method === 'most_reviewed' ? 'active' : ''; ?>"
           href="<?php echo baseUrl('/rankings/company_top5.php?method=most_reviewed'); ?>">
            <i class="fas fa-comments"></i> Most Reviewed
        </a>
    </li>
</ul>

<?php while ($company = $companies->fetch_assoc()): ?>
<div class="card mb-4">
    <div class="card-header bg-white">
        <h4 class="mb-0">
            <a href="<?php echo baseUrl('/companies/view.php?id=' . $company['id']); ?>" class="text-decoration-none">
                <i class="fas fa-store"></i> <?php echo htmlspecialchars($company['name']); ?>
            </a>
            <span class="badge-category ms-2"><?php echo htmlspecialchars($company['category']); ?></span>
        </h4>
    </div>
    <div class="card-body">
        <?php
        $stmt = $conn->prepare("
            SELECT p.*,
                   COALESCE(AVG(r.rating), 0) AS avg_rating,
                   COUNT(DISTINCT r.id) AS review_count,
                   COUNT(DISTINCT v.id) AS visit_count
            FROM products p
            LEFT JOIN reviews r ON r.product_id = p.id
            LEFT JOIN user_visits v ON v.product_id = p.id
            WHERE p.company_id = ?
            GROUP BY p.id
            ORDER BY $order_by
            LIMIT 5
        ");
        $stmt->bind_param("i", $company['id']);
        $stmt->execute();
        $products = $stmt->get_result();
        $stmt->close();
        ?>

        <?php if ($products->num_rows > 0): ?>
        <div class="list-group list-group-flush">
            <?php $rank = 1; while ($product = $products->fetch_assoc()): ?>
            <div class="list-group-item d-flex align-items-center px-0">
                <div class="rank-badge me-3"><?php echo $rank; ?></div>
                <div class="flex-grow-1">
                    <a href="<?php echo baseUrl('/products/view.php?id=' . $product['id']); ?>" class="text-decoration-none fw-semibold">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </a>
                    <span class="text-muted small ms-2">$<?php echo number_format($product['price'], 2); ?></span>
                </div>
                <div class="text-end">
                    <div class="stars small">
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
                </div>
            </div>
            <?php $rank++; endwhile; ?>
        </div>
        <?php else: ?>
        <p class="text-muted mb-0">No products with data yet.</p>
        <?php endif; ?>
    </div>
</div>
<?php endwhile; ?>



<?php
$conn->close();
require_once __DIR__ . '/../includes/footer.php';
?>
