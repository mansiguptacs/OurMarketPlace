<?php
$pageTitle = "OurMarketplace - Home";
require_once __DIR__ . '/includes/header.php';
?>

<div class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold">Welcome to OurMarketplace</h1>
        <p class="lead mt-3">Your one-stop destination for Makeup, Jewellery, Cookies & IT Services</p>
        <a href="/companies/index.php" class="btn btn-light btn-lg mt-3">Explore Companies</a>
    </div>
</div>

<h2 class="text-center mb-4">Our Companies</h2>
<div class="row g-4">
    <?php
    require_once __DIR__ . '/config/database.php';
    $conn = getDBConnection();
    $result = $conn->query("SELECT * FROM companies ORDER BY id");

    if ($result && $result->num_rows > 0):
        while ($company = $result->fetch_assoc()):
    ?>
    <div class="col-md-6 col-lg-3">
        <div class="card company-card h-100">
            <div class="card-body text-center">
                <i class="fas fa-building fa-2x text-primary mb-3"></i>
                <h5 class="card-title"><?php echo htmlspecialchars($company['name']); ?></h5>
                <span class="badge-category"><?php echo htmlspecialchars($company['category']); ?></span>
                <p class="card-text mt-2 text-muted small">
                    <?php echo htmlspecialchars(substr($company['description'], 0, 80)); ?>...
                </p>
                <a href="/companies/view.php?id=<?php echo $company['id']; ?>" class="btn btn-outline-primary btn-sm mt-2">
                    Visit Store
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
    <?php
    endif;
    $conn->close();
    ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
