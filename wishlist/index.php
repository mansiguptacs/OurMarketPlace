<?php
$pageTitle = "My Wishlist - OurMarketplace";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

requireLogin();

$conn = getDBConnection();
$user_id = getCurrentUserId();

$stmt = $conn->prepare("
    SELECT w.added_at, p.*, c.name AS company_name, c.id AS company_id,
           COALESCE(pr.avg_rating, 0) AS avg_rating,
           COALESCE(pr.review_count, 0) AS review_count
    FROM wishlist w
    JOIN products p ON p.id = w.product_id
    JOIN companies c ON c.id = p.company_id
    LEFT JOIN (
        SELECT product_id,
               AVG(rating) AS avg_rating,
               COUNT(*) AS review_count
        FROM reviews
        GROUP BY product_id
    ) pr ON pr.product_id = p.id
    WHERE w.user_id = ?
    ORDER BY w.added_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$items = $stmt->get_result();
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="mb-4"><i class="fas fa-heart"></i> My Wishlist</h2>

<?php if ($items->num_rows > 0): ?>
<p class="text-muted mb-3"><?php echo $items->num_rows; ?> item<?php echo $items->num_rows !== 1 ? 's' : ''; ?> saved</p>

<div class="row g-4">
    <?php while ($product = $items->fetch_assoc()): ?>
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
                </p>
                <div class="d-flex justify-content-between align-items-center mt-2">
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
                    </div>
                </div>
                <small class="text-muted">Added <?php echo date('M d, Y', strtotime($product['added_at'])); ?></small>
            </div>
            <div class="card-footer bg-white border-0 d-flex gap-2">
                <a href="<?php echo baseUrl('/products/view.php?id=' . $product['id']); ?>" class="btn btn-outline-primary btn-sm flex-grow-1">
                    View
                </a>
                <a href="<?php echo baseUrl('/wishlist/remove.php?product_id=' . $product['id']); ?>" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php else: ?>
<div class="text-center mt-5">
    <i class="far fa-heart fa-4x text-muted mb-3"></i>
    <h4 class="text-muted">Your wishlist is empty</h4>
    <p class="text-muted">Browse products and click the heart icon to save items here.</p>
    <a href="<?php echo baseUrl('/products/index.php'); ?>" class="btn btn-primary">Browse Products</a>
</div>
<?php endif; ?>

<?php
$conn->close();
require_once __DIR__ . '/../includes/footer.php';
?>
