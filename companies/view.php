<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tracking.php';
require_once __DIR__ . '/../includes/remote_catalog.php';
require_once __DIR__ . '/../includes/sso_token.php';

// Get company ID from URL
$company_id = intval($_GET['id'] ?? 0);
if ($company_id <= 0) {
    header("Location: " . baseUrl('/companies/index.php'));
    exit;
}

$conn = getDBConnection();

// Fetch company details
$stmt = $conn->prepare("SELECT * FROM companies WHERE id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$company) {
    header("Location: " . baseUrl('/companies/index.php'));
    exit;
}

$pageTitle = $company['name'] . " - OurMarketplace";

// Track this visit
trackVisit($company_id);

// Prefer partner JSON when available; otherwise use the marketplace DB for real ids + reviews.
$products = [];
$productsSource = 'db';

$partnerApiUrl = null;
$partnerIdPrefix = 'api';

// Megha Artisans (company_id 2): live catalog on teammate site.
if ($company_id === 2 || ($company['slug'] ?? '') === 'megha-artisans') {
    $partnerApiUrl = 'https://mgcodes.com/get_products.php';
    $partnerIdPrefix = 'megha';
}

if ($partnerApiUrl !== null) {
    $decoded = fetch_partner_products_json($partnerApiUrl);
    if (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
        $products = normalize_partner_product_rows($decoded, $partnerIdPrefix);
        $productsSource = 'api';
    }
}

if ($productsSource !== 'api') {
    $stmt = $conn->prepare("
        SELECT p.*,
               COALESCE(AVG(r.rating), 0) AS avg_rating,
               COUNT(r.id) AS review_count
        FROM products p
        LEFT JOIN reviews r ON r.product_id = p.id
        WHERE p.company_id = ?
        GROUP BY p.id
        ORDER BY p.name
    ");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['is_api_product'] = false;
        $products[] = $row;
    }
    $stmt->close();
}

require_once __DIR__ . '/../includes/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo baseUrl('/index.php'); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo baseUrl('/companies/index.php'); ?>">Companies</a></li>
        <li class="breadcrumb-item active"><?php echo htmlspecialchars($company['name']); ?></li>
    </ol>
</nav>

<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1"><?php echo htmlspecialchars($company['name']); ?></h2>
                <span class="badge-category"><?php echo htmlspecialchars($company['category']); ?></span>
                <p class="mt-2 mb-1"><?php echo htmlspecialchars($company['description']); ?></p>
                <p class="text-muted small">
                    <i class="fas fa-user"></i> Owner: <?php echo htmlspecialchars($company['owner_name']); ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <?php if (!empty($company['website_url'])): ?>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end flex-md-column flex-lg-row align-items-md-end">
                <?php if (isLoggedIn() && (($company['slug'] ?? '') === 'cookie-business' || marketplace_sso_launch_ready())): ?>
                <a href="<?php echo htmlspecialchars(baseUrl('/sso/launch_to_company.php?company_id=' . (int) $company_id . '&return=index.php')); ?>" class="btn btn-success order-md-first" title="Starts single sign-on with the partner site">
                    <i class="fas fa-key"></i> Open company site (signed in)
                </a>
                <?php elseif (isLoggedIn()): ?>
                <p class="small text-muted mb-0 text-md-end" style="max-width:14rem;">SSO is off: add <code>config/sso_config.php</code> on the marketplace and <code>includes/sso_config.php</code> on the partner site with the same <code>MARKETPLACE_SSO_SECRET</code>.</p>
                <?php endif; ?>
                <a href="<?php echo htmlspecialchars($company['website_url']); ?>" target="_blank" rel="noopener" class="btn btn-outline-primary" title="New tab — does not pass marketplace login">
                    <i class="fas fa-external-link-alt"></i> Official website (no login transfer)
                </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<h3 class="mb-3">Products & Services (<?php echo count($products); ?>)</h3>

<div class="row g-4">
    <?php if (count($products) > 0): ?>
        <?php foreach ($products as $product): ?>
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
                    <p class="card-text text-muted small">
                        <?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...
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
                    <?php if (!empty($product['is_api_product'])): ?>
                        <button class="btn btn-outline-secondary btn-sm w-100" type="button" disabled>API product (view on company site)</button>
                    <?php else: ?>
                        <div class="d-flex gap-2">
                            <a href="<?php echo baseUrl('/products/view.php?id=' . (int) $product['id']); ?>" class="btn btn-outline-primary btn-sm flex-grow-1">
                                View Details
                            </a>
                            <a href="<?php echo baseUrl('/compare/add.php?product_id=' . (int) $product['id']); ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-code-compare"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">No products found for this company.</div>
        </div>
    <?php endif; ?>
</div>

<?php
$conn->close();
require_once __DIR__ . '/../includes/footer.php';
?>
