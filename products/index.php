<?php
$pageTitle = "All Products - OurMarketplace";
require_once __DIR__ . '/../includes/header.php';

$conn = getDBConnection();

// Optional filter by company
$filter_company = intval($_GET['company'] ?? 0);

$sql = "
    SELECT p.*, c.name AS company_name, c.slug AS company_slug,
           COALESCE(AVG(r.rating), 0) AS avg_rating,
           COUNT(r.id) AS review_count
    FROM products p
    JOIN companies c ON c.id = p.company_id
    LEFT JOIN reviews r ON r.product_id = p.id
";
if ($filter_company > 0) {
    $sql .= " WHERE p.company_id = " . $filter_company;
}
$sql .= " GROUP BY p.id ORDER BY p.name";

$products = $conn->query($sql);

// Get companies for filter dropdown
$companies = $conn->query("SELECT id, name FROM companies ORDER BY name");
?>

<h2 class="mb-3">All Products & Services</h2>

<div class="row mb-4">
    <div class="col-md-6">
        <form method="GET" class="d-flex gap-2">
            <select name="company" class="form-select" onchange="this.form.submit()">
                <option value="0">All Companies</option>
                <?php while ($c = $companies->fetch_assoc()): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo ($filter_company == $c['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>
    </div>
</div>

<div class="row g-4">
    <?php if ($products && $products->num_rows > 0): ?>
        <?php while ($product = $products->fetch_assoc()): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <?php
                [$showProductImg, $productImgSrc] = productImageForDisplay($product['image_url'] ?? '');
                ?>
                <?php if ($showProductImg): ?>
                    <img src="<?php echo htmlspecialchars($productImgSrc); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height:180px;object-fit:cover;">
                <?php else: ?>
                    <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height:180px;">
                        <i class="fas fa-image fa-3x text-muted"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <p class="mb-1">
                        <a href="<?php echo baseUrl('/companies/view.php?id=' . $product['company_id']); ?>" class="text-decoration-none small">
                            <i class="fas fa-store"></i> <?php echo htmlspecialchars($product['company_name']); ?>
                        </a>
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
                                if ($i <= $avg):
                            ?>
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
                    <a href="<?php echo baseUrl('/products/view.php?id=' . $product['id']); ?>" class="btn btn-outline-primary btn-sm w-100">
                        View Details
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">No products found.</div>
        </div>
    <?php endif; ?>
</div>

<?php
$conn->close();
require_once __DIR__ . '/../includes/footer.php';
?>
