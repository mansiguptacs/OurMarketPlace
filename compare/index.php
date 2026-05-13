<?php
$pageTitle = "Compare Products - OurMarketplace";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

$selectedIds = compareProductIds();
$flash = comparePullFlash();
$products = [];

if (!empty($selectedIds)) {
    $conn = getDBConnection();
    $safeIds = array_map('intval', $selectedIds);
    $sql = "
        SELECT p.*, c.name AS company_name, c.id AS company_id,
               COALESCE(AVG(r.rating), 0) AS avg_rating,
               COUNT(DISTINCT r.id) AS review_count,
               COUNT(DISTINCT v.id) AS visit_count
        FROM products p
        JOIN companies c ON c.id = p.company_id
        LEFT JOIN reviews r ON r.product_id = p.id
        LEFT JOIN user_visits v ON v.product_id = p.id
        WHERE p.id IN (" . implode(',', $safeIds) . ")
        GROUP BY p.id
    ";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $products[(int) $row['id']] = $row;
    }
    $conn->close();

    $ordered = [];
    foreach ($safeIds as $id) {
        if (isset($products[$id])) {
            $ordered[] = $products[$id];
        }
    }
    $products = $ordered;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h2 class="mb-1"><i class="fas fa-code-compare"></i> Compare Products</h2>
        <p class="text-muted mb-0">Add up to 3 products from any storefront and compare them side by side.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?php echo baseUrl('/products/index.php'); ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-plus"></i> Add more products
        </a>
        <?php if (!empty($products)): ?>
        <a href="<?php echo baseUrl('/compare/clear.php'); ?>" class="btn btn-outline-danger btn-sm">
            <i class="fas fa-trash"></i> Clear compare
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?php echo htmlspecialchars($flash['type'] === 'danger' ? 'danger' : ($flash['type'] === 'success' ? 'success' : 'info')); ?>">
        <?php echo htmlspecialchars((string) ($flash['message'] ?? '')); ?>
    </div>
<?php endif; ?>

<?php if (empty($products)): ?>
    <div class="alert alert-info">
        No products selected yet. Open any product and click <strong>Add to compare</strong>.
    </div>
<?php else: ?>
    <div class="row g-3 mb-4">
        <?php foreach ($products as $product): ?>
        <div class="col-md-6 col-xl-<?php echo count($products) === 1 ? '12' : (count($products) === 2 ? '6' : '4'); ?>">
            <div class="card h-100">
                <?php
                [$showProductImg, $productImgSrc] = productImageForDisplay($product['image_url'] ?? '');
                ?>
                <?php if ($showProductImg): ?>
                    <img src="<?php echo htmlspecialchars($productImgSrc); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height:200px;object-fit:cover;">
                <?php else: ?>
                    <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height:200px;">
                        <i class="fas fa-image fa-3x text-muted"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <p class="mb-1">
                        <a href="<?php echo baseUrl('/companies/view.php?id=' . (int) $product['company_id']); ?>" class="text-decoration-none small">
                            <i class="fas fa-store"></i> <?php echo htmlspecialchars($product['company_name']); ?>
                        </a>
                    </p>
                    <p class="text-muted small mb-2"><?php echo htmlspecialchars(substr($product['description'], 0, 110)); ?><?php echo strlen($product['description']) > 110 ? '...' : ''; ?></p>
                    <div class="fw-bold text-primary">$<?php echo number_format((float) $product['price'], 2); ?></div>
                </div>
                <div class="card-footer bg-white border-0 d-flex gap-2">
                    <a href="<?php echo baseUrl('/products/view.php?id=' . (int) $product['id']); ?>" class="btn btn-outline-primary btn-sm flex-grow-1">View</a>
                    <a href="<?php echo baseUrl('/compare/remove.php?product_id=' . (int) $product['id']); ?>" class="btn btn-outline-secondary btn-sm">Remove</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Side-by-Side Comparison</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <tbody>
                        <tr>
                            <th style="width: 18rem;">Company</th>
                            <?php foreach ($products as $product): ?>
                                <td><?php echo htmlspecialchars($product['company_name']); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th>Category</th>
                            <?php foreach ($products as $product): ?>
                                <td><?php echo htmlspecialchars($product['category']); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th>Price</th>
                            <?php foreach ($products as $product): ?>
                                <td class="text-primary fw-bold">$<?php echo number_format((float) $product['price'], 2); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th>Average Rating</th>
                            <?php foreach ($products as $product): ?>
                                <td><?php echo number_format((float) $product['avg_rating'], 1); ?>/5</td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th>Review Count</th>
                            <?php foreach ($products as $product): ?>
                                <td><?php echo (int) $product['review_count']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th>Visit Count</th>
                            <?php foreach ($products as $product): ?>
                                <td><?php echo (int) $product['visit_count']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <?php foreach ($products as $product): ?>
                                <td class="small"><?php echo htmlspecialchars($product['description']); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php if (count($products) < 2): ?>
                <p class="text-muted mb-0">Add at least one more product to make the side-by-side comparison more meaningful.</p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
