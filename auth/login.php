<?php
$pageTitle = "Login - OurMarketplace";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

$return_to = $_GET['return_to'] ?? '';

// Redirect if already logged in
if (isLoggedIn()) {
    if (!empty($return_to) && strpos($return_to, baseUrl('/sso/')) === 0) {
        header("Location: " . $return_to);
    } else {
        header("Location: " . baseUrl('/index.php'));
    }
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errors[] = "Username and password are required.";
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, username, password_hash, full_name FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];

                $return_to = $_GET['return_to'] ?? $_POST['return_to'] ?? '';
                if (!empty($return_to) && strpos($return_to, baseUrl('/sso/')) === 0) {
                    header("Location: " . $return_to);
                } else {
                    header("Location: " . baseUrl('/index.php'));
                }
                exit;
            } else {
                $errors[] = "Invalid username or password.";
            }
        } else {
            $errors[] = "Invalid username or password.";
        }

        $stmt->close();
        $conn->close();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card p-4">
            <h2 class="text-center mb-4"><i class="fas fa-sign-in-alt"></i> Login</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?php if (!empty($_GET['return_to'])): ?>
                    <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($_GET['return_to']); ?>">
                <?php endif; ?>
                <div class="mb-3">
                    <label for="username" class="form-label">Username or Email</label>
                    <input type="text" class="form-control" id="username" name="username"
                           value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <p class="text-center mt-3 mb-0">
                Don't have an account? <a href="<?php echo baseUrl('/auth/register.php'); ?>">Register here</a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
