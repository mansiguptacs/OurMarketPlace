<?php
$pageTitle = "Register - OurMarketplace";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: " . baseUrl('/index.php'));
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    }

    if (empty($full_name)) {
        $errors[] = "Full name is required.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    // If no validation errors, try to insert
    if (empty($errors)) {
        $conn = getDBConnection();

        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "Username or email already exists.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $password_hash, $full_name);

            if ($stmt->execute()) {
                $success = true;
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        }

        $stmt->close();
        $conn->close();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card p-4">
            <h2 class="text-center mb-4"><i class="fas fa-user-plus"></i> Register</h2>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Registration successful!
                    <a href="<?php echo baseUrl('/auth/login.php'); ?>" class="alert-link">Click here to login</a>.
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name"
                           value="<?php echo htmlspecialchars($full_name ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username"
                           value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="form-text">At least 6 characters.</div>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
            <?php endif; ?>

            <p class="text-center mt-3 mb-0">
                Already have an account? <a href="<?php echo baseUrl('/auth/login.php'); ?>">Login here</a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
