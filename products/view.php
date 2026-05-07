<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

$conn = getDBConnection();

// Get product ID
$product_id = intval($_GET['id'] ?? 0);
if ($product_id <= 0) {
    header("Location: " . baseUrl('/products/index.php'));
    exit;
}

// Fetch product with company info
$stmt = $conn->prepare("
    SELECT p.*, c.name AS company_name, c.id AS company_id, c.website_url,
           COALESCE(AVG(r.rating), 0) AS avg_rating,
           COUNT(r.id) AS review_count
    FROM products p
    JOIN companies c ON c.id = p.company_id
    LEFT JOIN reviews r ON r.product_id = p.id
    WHERE p.id = ?
    GROUP BY p.id
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: " . baseUrl('/products/index.php'));
    exit;
}

$pageTitle = $product['name'] . " - OurMarketplace";

// Fetch reviews for this product
$stmt = $conn->prepare("
    SELECT r.*, u.full_name, u.username
    FROM reviews r
    JOIN users u ON u.id = r.user_id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$reviews = $stmt->get_result();
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo baseUrl('/index.php'); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo baseUrl('/companies/view.php?id=' . $product['company_id']); ?>"><?php echo htmlspecialchars($product['company_name']); ?></a></li>
        <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
    </ol>
</nav>

<div class="row">
    <!-- Product Info -->
    <div class="col-md-5">
        <div class="card">
            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height:300px;">
                <i class="fas fa-image fa-5x text-muted"></i>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        <p>
            <a href="<?php echo baseUrl('/companies/view.php?id=' . $product['company_id']); ?>" class="text-decoration-none">
                <i class="fas fa-store"></i> <?php echo htmlspecialchars($product['company_name']); ?>
            </a>
            <span class="badge-category ms-2"><?php echo htmlspecialchars($product['category']); ?></span>
        </p>

        <div class="stars mb-2">
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
            <span class="ms-2 text-muted"><?php echo $avg; ?>/5 (<?php echo $product['review_count']; ?> reviews)</span>
        </div>

        <h3 class="text-primary mb-3">$<?php echo number_format($product['price'], 2); ?></h3>

        <p><?php echo htmlspecialchars($product['description']); ?></p>

        <?php if (!empty($product['website_url'])): ?>
        <a href="<?php echo htmlspecialchars($product['website_url']); ?>" target="_blank" class="btn btn-primary">
            <i class="fas fa-external-link-alt"></i> Visit Official Store
        </a>
        <?php endif; ?>
    </div>
</div>

<hr class="my-4">

<!-- Reviews Section -->
<div class="row">
    <div class="col-md-8">
        <h4 class="mb-3">Reviews (<?php echo $reviews->num_rows; ?>)</h4>

        <?php if ($reviews->num_rows > 0): ?>
            <?php while ($review = $reviews->fetch_assoc()): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong><?php echo htmlspecialchars($review['full_name']); ?></strong>
                            <span class="text-muted small ms-2">@<?php echo htmlspecialchars($review['username']); ?></span>
                        </div>
                        <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                    </div>
                    <div class="stars my-1">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= $review['rating']): ?>
                                <i class="fas fa-star"></i>
                            <?php else: ?>
                                <i class="far fa-star empty"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <?php if (!empty($review['review_text'])): ?>
                        <p class="mb-0 mt-2"><?php echo htmlspecialchars($review['review_text']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-muted">No reviews yet. Be the first to review!</p>
        <?php endif; ?>
    </div>

    <!-- Add Review Form -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5>Write a Review</h5>
                <?php if (isLoggedIn()): ?>
                <form method="POST" action="<?php echo baseUrl('/reviews/add.php'); ?>">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="rating-input">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                                <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="review_text" class="form-label">Your Review</label>
                        <textarea class="form-control" name="review_text" id="review_text" rows="3" placeholder="Share your experience..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Submit Review</button>
                </form>
                <?php else: ?>
                <p class="text-muted">
                    <a href="<?php echo baseUrl('/auth/login.php'); ?>">Login</a> to write a review.
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once __DIR__ . '/../includes/footer.php';
?>
