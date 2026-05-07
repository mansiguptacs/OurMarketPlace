<?php
$pageTitle = "All Companies - OurMarketplace";
require_once __DIR__ . '/../includes/header.php';

$conn = getDBConnection();
$result = $conn->query("SELECT * FROM companies ORDER BY id");
?>

<h2 class="mb-4">Our Companies</h2>
<p class="text-muted mb-4">Explore all the businesses in our marketplace.</p>

<div class="row g-4">
    <?php while ($company = $result->fetch_assoc()): ?>
    <div class="col-md-6">
        <div class="card company-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <i class="fas fa-store fa-3x text-primary"></i>
                    </div>
                    <div>
                        <h4 class="card-title mb-1"><?php echo htmlspecialchars($company['name']); ?></h4>
                        <span class="badge-category"><?php echo htmlspecialchars($company['category']); ?></span>
                        <p class="card-text mt-2"><?php echo htmlspecialchars($company['description']); ?></p>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-user"></i> Owner: <?php echo htmlspecialchars($company['owner_name']); ?>
                        </p>
                        <a href="<?php echo baseUrl('/companies/view.php?id=' . $company['id']); ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-arrow-right"></i> Visit Store
                        </a>
                        <?php if (!empty($company['website_url'])): ?>
                        <a href="<?php echo htmlspecialchars($company['website_url']); ?>" target="_blank" class="btn btn-outline-secondary btn-sm ms-2">
                            <i class="fas fa-external-link-alt"></i> Official Site
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php
$conn->close();
require_once __DIR__ . '/../includes/footer.php';
?>
