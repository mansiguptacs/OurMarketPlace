<?php
$pageTitle = "OurMarketplace - Home";
require_once __DIR__ . '/includes/header.php';
?>

<div class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold">Welcome to OurMarketplace</h1>
        <p class="lead mt-3 mb-4">Your one-stop destination for Makeup, Jewellery, Cookies & IT Services</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?php echo baseUrl('/companies/index.php'); ?>" class="btn btn-light btn-lg">
                <i class="fas fa-store"></i> Explore Companies
            </a>
            <a href="<?php echo baseUrl('/rankings/marketplace_top5.php'); ?>" class="btn btn-outline-light btn-lg">
                <i class="fas fa-trophy"></i> Top 5 Products
            </a>
        </div>
    </div>
</div>

<h2 class="text-center mb-4">Our Companies</h2>

<div class="row g-4">
    <?php
    $conn = getDBConnection();
    $result = $conn->query("SELECT * FROM companies ORDER BY id");

    $icons = [
        1 => 'fas fa-paint-brush',
        2 => 'fas fa-gem',
        3 => 'fas fa-cookie-bite',
        4 => 'fas fa-laptop-code'
    ];

    $colors = [
        1 => '#e91e63',
        2 => '#9c27b0',
        3 => '#ff9800',
        4 => '#2196f3'
    ];

    if ($result && $result->num_rows > 0):
        while ($company = $result->fetch_assoc()):
            $icon = $icons[$company['id']] ?? 'fas fa-building';
            $color = $colors[$company['id']] ?? '#4f46e5';
    ?>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 text-center">
            <div class="card-body">
                <div class="mb-3">
                    <i class="<?php echo $icon; ?> fa-3x" style="color:<?php echo $color; ?>"></i>
                </div>
                <h5 class="card-title"><?php echo htmlspecialchars($company['name']); ?></h5>
                <span class="badge-category"><?php echo htmlspecialchars($company['category']); ?></span>
                <p class="card-text mt-2 text-muted small">
                    <?php echo htmlspecialchars(substr($company['description'], 0, 80)); ?>...
                </p>
                <p class="small text-muted mb-2">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($company['owner_name']); ?>
                </p>
            </div>
            <div class="card-footer bg-white border-0 pb-3">
                <a href="<?php echo baseUrl('/companies/view.php?id=' . $company['id']); ?>" class="btn btn-outline-primary btn-sm">
                    Visit Store <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    <?php
        endwhile;
    else:
    ?>
    <div class="col-12">
        <div class="alert alert-info text-center">
            <p>No companies found. Please run <code>sql/schema.sql</code> to set up the database.</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Quick Links Section -->
<div class="row mt-5 g-3">
    <div class="col-md-4">
        <div class="card text-center p-3">
            <div class="card-body">
                <i class="fas fa-search fa-2x text-primary mb-2"></i>
                <h6>Search Products</h6>
                <p class="small text-muted mb-2">Find products across all companies</p>
                <a href="<?php echo baseUrl('/products/search.php'); ?>" class="btn btn-sm btn-outline-primary">Search Now</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center p-3">
            <div class="card-body">
                <i class="fas fa-trophy fa-2x text-warning mb-2"></i>
                <h6>Top Ranked</h6>
                <p class="small text-muted mb-2">See the best products in the marketplace</p>
                <a href="<?php echo baseUrl('/rankings/marketplace_top5.php'); ?>" class="btn btn-sm btn-outline-primary">View Rankings</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center p-3">
            <div class="card-body">
                <i class="fas fa-th-list fa-2x text-success mb-2"></i>
                <h6>All Products</h6>
                <p class="small text-muted mb-2">Browse all 20+ products and services</p>
                <a href="<?php echo baseUrl('/products/index.php'); ?>" class="btn btn-sm btn-outline-primary">Browse All</a>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once __DIR__ . '/includes/footer.php';
?>
