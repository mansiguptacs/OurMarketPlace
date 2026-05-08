<?php
$pageTitle = "Compare Products - OurMarketplace";
require_once __DIR__ . '/../includes/header.php';

$conn = getDBConnection();

// Get product IDs from URL (up to 3)
$ids = $_GET['ids'] ?? '';
$product_ids = array_filter(array_map('intval', explode(',', $ids)));
$product_ids = array_slice($product_ids, 0, 3);

// Fetch all products for selection dropdowns
$all_products = $conn->query("
    SELECT p.id, p.name, c.name AS company_name 
    FROM products p 
    JOIN companies c ON c.id = p.company_id 
    ORDER BY c.name, p.name
");

$compare_data = [];
if (!empty($product_ids)) {
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $types = str_repeat('i', count($product_ids));

    $sql = "
        SELECT p.*, c.name AS company_name, c.id AS company_id,
               COALESCE(AVG(r.rating), 0) AS avg_rating,
               COUNT(DISTINCT r.id) AS review_count,
               COUNT(DISTINCT v.id) AS visit_count
        FROM products p
        JOIN companies c ON c.id = p.company_id
        LEFT JOIN reviews r ON r.product_id = p.id
        LEFT JOIN user_visits v ON v.product_id = p.id
        WHERE p.id IN ($placeholders)
        GROUP BY p.id
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $compare_data[] = $row;
    }
    $stmt->close();
}
?>

<h2 class="mb-4"><i class="fas fa-columns"></i> Compare Products</h2>

<!-- Product Selection Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="">
            <div class="row g-3 align-items-end">
                <?php for ($slot = 0; $slot < 3; $slot++): ?>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Product <?php echo $slot + 1; ?></label>
                    <select name="p<?php echo $slot; ?>" class="form-select">
                        <option value="">-- Select --</option>
                        <?php
                        $all_products->data_seek(0);
                        while ($p = $all_products->fetch_assoc()):
                        ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo (isset($product_ids[$slot]) && $product_ids[$slot] == $p['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['company_name'] . ' - ' . $p['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php endfor; ?>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100" onclick="buildCompareUrl(event)">
                        <i class="fas fa-columns"></i> Compare
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Comparison Table -->
<?php if (!empty($compare_data)): ?>
<div class="table-responsive">
    <table class="table table-bordered text-center">
        <thead class="table-light">
            <tr>
                <th class="text-start" style="width:150px;">Attribute</th>
                <?php foreach ($compare_data as $product): ?>
                <th><?php echo htmlspecialchars($product['name']); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-start fw-semibold">Company</td>
                <?php foreach ($compare_data as $product): ?>
                <td>
                    <a href="<?php echo baseUrl('/companies/view.php?id=' . $product['company_id']); ?>" class="text-decoration-none">
                        <?php echo htmlspecialchars($product['company_name']); ?>
                    </a>
                </td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td class="text-start fw-semibold">Category</td>
                <?php foreach ($compare_data as $product): ?>
                <td><span class="badge-category"><?php echo htmlspecialchars($product['category']); ?></span></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td class="text-start fw-semibold">Price</td>
                <?php foreach ($compare_data as $product): ?>
                <td class="fw-bold text-primary">$<?php echo number_format($product['price'], 2); ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td class="text-start fw-semibold">Rating</td>
                <?php foreach ($compare_data as $product): ?>
                <td>
                    <div class="stars">
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
                    <small class="text-muted"><?php echo $avg; ?>/5</small>
                </td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td class="text-start fw-semibold">Reviews</td>
                <?php foreach ($compare_data as $product): ?>
                <td><?php echo $product['review_count']; ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td class="text-start fw-semibold">Visits</td>
                <?php foreach ($compare_data as $product): ?>
                <td><?php echo $product['visit_count']; ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td class="text-start fw-semibold">Description</td>
                <?php foreach ($compare_data as $product): ?>
                <td class="small text-start"><?php echo htmlspecialchars($product['description']); ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td class="text-start fw-semibold">Actions</td>
                <?php foreach ($compare_data as $product): ?>
                <td>
                    <a href="<?php echo baseUrl('/products/view.php?id=' . $product['id']); ?>" class="btn btn-sm btn-outline-primary">
                        View Details
                    </a>
                </td>
                <?php endforeach; ?>
            </tr>
        </tbody>
    </table>
</div>
<?php elseif (!empty($ids)): ?>
<div class="alert alert-warning">Please select at least one product to compare.</div>
<?php else: ?>
<div class="text-center text-muted mt-4">
    <i class="fas fa-columns fa-3x mb-3"></i>
    <p>Select 2 or 3 products above to compare them side by side.</p>
</div>
<?php endif; ?>

<script>
function buildCompareUrl(event) {
    event.preventDefault();
    const selects = document.querySelectorAll('select[name^="p"]');
    const ids = [];
    selects.forEach(function(s) {
        if (s.value) ids.push(s.value);
    });
    if (ids.length < 2) {
        alert('Please select at least 2 products to compare.');
        return;
    }
    window.location.href = '<?php echo baseUrl('/compare/index.php'); ?>?ids=' + ids.join(',');
}
</script>

<?php
$conn->close();
require_once __DIR__ . '/../includes/footer.php';
?>
