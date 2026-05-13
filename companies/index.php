<?php
$pageTitle = "All Companies - OurMarketplace";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sso_token.php';

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
                        <?php if (isLoggedIn() && (($company['slug'] ?? '') === 'cookie-business' || marketplace_sso_launch_ready())): ?>
                        <a href="<?php echo htmlspecialchars(baseUrl('/sso/launch_to_company.php?company_id=' . (int) $company['id'] . '&return=index.php')); ?>" class="btn btn-success btn-sm ms-2" title="Passes your marketplace login to this partner site">
                            <i class="fas fa-key"></i> Open signed in
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo htmlspecialchars($company['website_url']); ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm ms-2" title="Opens their site in a new tab — does not send marketplace login">
                            <i class="fas fa-external-link-alt"></i> Official site (no SSO)
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
