<?php
$pageTitle = "OurMarketplace - Our Team";
require_once __DIR__ . '/includes/header.php';

$teamMembers = [
    [
        'name' => 'Mansi Gupta',
        'role' => 'Makeup & Beauty Lead',
        'company' => 'Komal Gupta Makeup Studio',
        'company_id' => 1,
        'icon' => 'fas fa-paint-brush',
        'color' => '#e91e63',
        'website' => 'https://mansiguptacs.com/kgmakeupstudio/',
        'description' => 'Manages the KG Makeup Studio storefront offering professional makeup services and beauty products for all occasions.',
        'skills' => ['PHP', 'MySQL', 'Bootstrap', 'UI/UX Design'],
    ],
    [
        'name' => 'Megha Gangal',
        'role' => 'Jewellery & Design Lead',
        'company' => 'Artisan Jewelry by Megha',
        'company_id' => 2,
        'icon' => 'fas fa-gem',
        'color' => '#9c27b0',
        'website' => 'https://mgcodes.com/',
        'description' => 'Runs Megha Artisans, showcasing handcrafted artificial jewellery that blends tradition with modern elegance.',
        'skills' => ['Web Development', 'Database Design', 'CSS', 'JavaScript'],
    ],
    [
        'name' => 'Yukta Padgaonkar',
        'role' => 'Bakery & E-Commerce Lead',
        'company' => 'Sweet Crumb Homemade Cookies',
        'company_id' => 3,
        'icon' => 'fas fa-cookie-bite',
        'color' => '#ff9800',
        'website' => 'http://yukta-padgaonkar.com/CMPE-272-project/cookie-business/',
        'description' => 'Operates Sweet Crumb Cookies, offering freshly baked cookies and treats made with premium ingredients.',
        'skills' => ['PHP', 'Frontend Dev', 'Product Management', 'Testing'],
    ],
    [
        'name' => 'Gayathri Rukmadhavan',
        'role' => 'IT Services & Backend Lead',
        'company' => 'GeekyHub',
        'company_id' => 4,
        'icon' => 'fas fa-laptop-code',
        'color' => '#2196f3',
        'website' => 'http://geekyhub.me/',
        'description' => 'Leads GeekyHub, providing IT consulting and staffing services connecting top tech talent with businesses.',
        'skills' => ['Backend Dev', 'API Design', 'Security', 'DevOps'],
    ],
];
?>

<div class="team-hero text-center">
    <div class="container">
        <h1 class="display-4 fw-bold">Meet Our Team</h1>
        <p class="lead mt-3">The people behind OurMarketplace — CMPE 272, Enterprise Software Platforms</p>
    </div>
</div>

<div class="row g-4 mt-2">
    <?php foreach ($teamMembers as $member): ?>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 text-center team-card">
            <div class="card-body d-flex flex-column align-items-center">
                <div class="team-avatar mb-3" style="background: <?php echo $member['color']; ?>20; border: 3px solid <?php echo $member['color']; ?>">
                    <i class="<?php echo $member['icon']; ?> fa-2x" style="color: <?php echo $member['color']; ?>"></i>
                </div>
                <h5 class="card-title mb-1"><?php echo htmlspecialchars($member['name']); ?></h5>
                <span class="badge-category mb-2"><?php echo htmlspecialchars($member['role']); ?></span>
                <p class="text-muted small mt-2"><?php echo htmlspecialchars($member['description']); ?></p>
                <div class="mb-3">
                    <?php foreach ($member['skills'] as $skill): ?>
                        <span class="badge bg-light text-dark border me-1 mb-1" style="font-size: 0.7rem;">
                            <?php echo htmlspecialchars($skill); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
                <div class="mt-auto d-flex gap-2">
                    <a href="<?php echo baseUrl('/companies/view.php?id=' . $member['company_id']); ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-store"></i> Store
                    </a>
                    <a href="<?php echo htmlspecialchars($member['website']); ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-external-link-alt"></i> Website
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card mt-5">
    <div class="card-body">
        <h4 class="mb-4 text-center">About the Project</h4>
        <div class="row g-4">
            <div class="col-md-4 text-center">
                <i class="fas fa-university fa-2x text-primary mb-2"></i>
                <h6>Course</h6>
                <p class="text-muted small mb-0">CMPE 272 — Enterprise Software Platforms</p>
                <p class="text-muted small">San Jose State University</p>
            </div>
            <div class="col-md-4 text-center">
                <i class="fas fa-code fa-2x text-primary mb-2"></i>
                <h6>Tech Stack</h6>
                <p class="text-muted small mb-0">PHP + MySQL + Bootstrap 5</p>
                <p class="text-muted small">Pure PHP, no frameworks</p>
            </div>
            <div class="col-md-4 text-center">
                <i class="fas fa-store fa-2x text-primary mb-2"></i>
                <h6>Marketplace</h6>
                <p class="text-muted small mb-0">4 companies, 40+ products</p>
                <p class="text-muted small">Unified login, reviews & rankings</p>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
