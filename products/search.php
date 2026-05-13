<?php
$pageTitle = "Search - OurMarketplace";
require_once __DIR__ . '/../includes/header.php';

$query = trim($_GET['q'] ?? '');
$results = null;
$result_count = 0;

if (!empty($query)) {
    $conn = getDBConnection();
    $search_term = '%' . $query . '%';

    $stmt = $conn->prepare("
        SELECT p.*, c.name AS company_name, c.id AS company_id, c.category AS company_category,
               COALESCE(AVG(r.rating), 0) AS avg_rating,
               COUNT(DISTINCT r.id) AS review_count
        FROM products p
        JOIN companies c ON c.id = p.company_id
        LEFT JOIN reviews r ON r.product_id = p.id
        WHERE p.name LIKE ? 
           OR p.description LIKE ? 
           OR p.category LIKE ?
           OR c.name LIKE ?
        GROUP BY p.id
        ORDER BY 
            CASE WHEN p.name LIKE ? THEN 1 ELSE 2 END,
            avg_rating DESC
    ");
    $stmt->bind_param("sssss", $search_term, $search_term, $search_term, $search_term, $search_term);
    $stmt->execute();
    $results = $stmt->get_result();
    $result_count = $results->num_rows;
    $stmt->close();
    $conn->close();
}
?>

<h2 class="mb-4"><i class="fas fa-search"></i> Search Marketplace</h2>

<!-- Search Form -->
<div class="search-box mb-4">
    <form method="GET" action="">
        <div class="input-group input-group-lg">
            <input type="text" class="form-control" name="q" 
                   placeholder="Search products, services, or companies..." 
                   value="<?php echo htmlspecialchars($query); ?>" autofocus>
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-search"></i> Search
            </button>
        </div>
    </form>
</div>

<?php if (!empty($query)): ?>
    <!-- Results Header -->
    <p class="text-muted mb-3">
        Found <strong><?php echo $result_count; ?></strong> result<?php echo $result_count !== 1 ? 's' : ''; ?> 
        for "<strong><?php echo htmlspecialchars($query); ?></strong>"
    </p>

    <?php if ($result_count > 0): ?>
    <div class="row g-4">
        <?php while ($product = $results->fetch_assoc()): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <?php
                [$showProductImg, $productImgSrc] = productImageForDisplay($product['image_url'] ?? '');
                ?>
                <?php if ($showProductImg): ?>
                    <img src="<?php echo htmlspecialchars($productImgSrc); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height:160px;object-fit:cover;">
                <?php else: ?>
                    <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height:160px;">
                        <i class="fas fa-image fa-3x text-muted"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <p class="mb-1">
                        <a href="<?php echo baseUrl('/companies/view.php?id=' . $product['company_id']); ?>" class="text-decoration-none small">
                            <i class="fas fa-store"></i> <?php echo htmlspecialchars($product['company_name']); ?>
                        </a>
                        <span class="badge-category ms-2"><?php echo htmlspecialchars($product['category']); ?></span>
                    </p>
                    <p class="card-text text-muted small">
                        <?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-primary">$<?php echo number_format($product['price'], 2); ?></span>
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
                            <span class="text-muted ms-1">(<?php echo $product['review_count']; ?>)</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <div class="d-flex gap-2">
                        <a href="<?php echo baseUrl('/products/view.php?id=' . $product['id']); ?>" class="btn btn-outline-primary btn-sm flex-grow-1">
                            View Details
                        </a>
                        <a href="<?php echo baseUrl('/compare/add.php?product_id=' . $product['id']); ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-code-compare"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i> No products found matching your search. Try different keywords.
    </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Suggestions when no search yet -->
    <div class="text-center text-muted mt-5">
        <i class="fas fa-search fa-3x mb-3"></i>
        <p>Search across all 4 companies in our marketplace.</p>
        <p class="small">Try: "makeup", "cookies", "necklace", "web development", "bridal", "brownie"</p>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
