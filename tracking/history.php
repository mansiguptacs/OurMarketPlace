<?php
$pageTitle = "My Visit History - OurMarketplace";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

requireLogin();

$conn = getDBConnection();
$user_id = getCurrentUserId();

// Get visit summary per company
$stmt = $conn->prepare("
    SELECT c.id, c.name, c.category, COUNT(v.id) AS visit_count, MAX(v.visited_at) AS last_visit
    FROM user_visits v
    JOIN companies c ON c.id = v.company_id
    WHERE v.user_id = ?
    GROUP BY c.id
    ORDER BY visit_count DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$company_visits = $stmt->get_result();
$stmt->close();

// Get recent visit details (last 20)
$stmt = $conn->prepare("
    SELECT v.*, c.name AS company_name, p.name AS product_name
    FROM user_visits v
    JOIN companies c ON c.id = v.company_id
    LEFT JOIN products p ON p.id = v.product_id
    WHERE v.user_id = ?
    ORDER BY v.visited_at DESC
    LIMIT 20
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_visits = $stmt->get_result();
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="mb-4"><i class="fas fa-history"></i> My Visit History</h2>

<!-- Summary by Company -->
<h4 class="mb-3">Visits by Company</h4>
<?php if ($company_visits->num_rows > 0): ?>
<div class="row g-3 mb-4">
    <?php while ($cv = $company_visits->fetch_assoc()): ?>
    <div class="col-md-6 col-lg-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($cv['name']); ?></h5>
                <p class="display-6 text-primary fw-bold"><?php echo $cv['visit_count']; ?></p>
                <p class="text-muted small mb-0">visits</p>
                <p class="text-muted small">
                    Last: <?php echo date('M d, Y', strtotime($cv['last_visit'])); ?>
                </p>
                <a href="<?php echo baseUrl('/companies/view.php?id=' . $cv['id']); ?>" class="btn btn-outline-primary btn-sm">
                    Visit Again
                </a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php else: ?>
<div class="alert alert-info mb-4">
    You haven't visited any companies yet. <a href="<?php echo baseUrl('/companies/index.php'); ?>">Start exploring!</a>
</div>
<?php endif; ?>

<!-- Recent Activity -->
<h4 class="mb-3">Recent Activity</h4>
<?php if ($recent_visits->num_rows > 0): ?>
<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>Date & Time</th>
                <th>Company</th>
                <th>Product/Page</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($visit = $recent_visits->fetch_assoc()): ?>
            <tr>
                <td class="text-muted small"><?php echo date('M d, Y h:i A', strtotime($visit['visited_at'])); ?></td>
                <td>
                    <a href="<?php echo baseUrl('/companies/view.php?id=' . $visit['company_id']); ?>">
                        <?php echo htmlspecialchars($visit['company_name']); ?>
                    </a>
                </td>
                <td>
                    <?php if ($visit['product_name']): ?>
                        <a href="<?php echo baseUrl('/products/view.php?id=' . $visit['product_id']); ?>">
                            <?php echo htmlspecialchars($visit['product_name']); ?>
                        </a>
                    <?php else: ?>
                        <span class="text-muted">Company Page</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<p class="text-muted">No recent activity to show.</p>
<?php endif; ?>

<?php
$conn->close();
require_once __DIR__ . '/../includes/footer.php';
?>
