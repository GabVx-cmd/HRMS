<?php
/**
 * Create Admin/HR Account.
 * Links a new login (username + password + role) to an existing employee
 * record. Restricted to admin_hr sessions via auth_guard.php.
 *
 * setRole() bridges the session's known role into the User object so
 * createUser()'s internal isAdmin() check passes — see user.php for why
 * this is needed (the object has no memory of login() across page loads).
 */
require_once __DIR__ . '/includes/db_connect.php';
$requiredRole = 'admin_hr';
require_once __DIR__ . '/includes/auth_guard.php';

$user->setRole($_SESSION['role']);

$errors = [];
$data = [
    'employee_id' => '',
    'username'    => '',
    'password'    => '',
    'role'        => '',
];

// Employees who don't already have a login account
$availableStmt = $conn->query(
    "SELECT e.employee_id, e.first_name, e.last_name, e.email
     FROM employees e
     LEFT JOIN users u ON e.employee_id = u.employee_id
     WHERE u.user_id IS NULL
     ORDER BY e.first_name"
);
$availableEmployees = $availableStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['employee_id'] = trim($_POST['employee_id'] ?? '');
    $data['username']    = trim($_POST['username'] ?? '');
    $data['password']    = $_POST['password'] ?? '';
    $data['role']        = trim($_POST['role'] ?? '');

    if ($data['employee_id'] === '' || !ctype_digit($data['employee_id'])) {
        $errors['employee_id'] = 'Please select an employee.';
    }
    if ($data['username'] === '') {
        $errors['username'] = 'Username is required.';
    }
    if ($data['password'] === '' || strlen($data['password']) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }
    if (!in_array($data['role'], ['admin', 'hr'], true)) {
        $errors['role'] = 'Please select a role.';
    }

    // Duplicate username check
    if (empty($errors['username'])) {
        $dupStmt = $conn->prepare("SELECT user_id FROM users WHERE username = :username");
        $dupStmt->bindValue(':username', $data['username'], PDO::PARAM_STR);
        $dupStmt->execute();
        if ($dupStmt->fetch()) {
            $errors['username'] = 'This username is already taken.';
        }
    }

    if (empty($errors)) {
        $created = $user->createUser(
            (int)$data['employee_id'],
            $data['username'],
            $data['password'],
            $data['role']
        );
        if ($created) {
            header('Location: create_account.php?flash=created');
            exit;
        } else {
            $errors['general'] = 'Something went wrong while creating the account. Check the PHP error log for details.';
        }
    }
}

$flash = $_GET['flash'] ?? null;
$pageTitle = 'Create Account';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Create Admin/HR Account</h1>
        <p>Grant an existing employee login access to the system</p>
    </div>
</div>

<?php if ($flash === 'created'): ?>
    <div class="alert alert-success">Account created successfully.</div>
<?php endif; ?>

<?php if (!empty($errors['general'])): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($errors['general']); ?></div>
<?php endif; ?>

<?php if (empty($availableEmployees) && empty($errors)): ?>
    <div class="empty-state">
        Every employee already has a login account. <a href="employee_form.php">Add a new employee</a> first if you need to create another account.
    </div>
<?php else: ?>
    <form method="post" class="form-grid" novalidate>
        <div class="form-field full <?php echo isset($errors['employee_id']) ? 'has-error' : ''; ?>">
            <label for="employee_id">Employee</label>
            <select id="employee_id" name="employee_id">
                <option value="">Select an employee</option>
                <?php foreach ($availableEmployees as $emp): ?>
                    <option value="<?php echo (int)$emp['employee_id']; ?>" <?php echo $data['employee_id'] == $emp['employee_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' — ' . $emp['email']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['employee_id'])): ?><span class="error"><?php echo $errors['employee_id']; ?></span><?php endif; ?>
        </div>

        <div class="form-field full <?php echo isset($errors['username']) ? 'has-error' : ''; ?>">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($data['username']); ?>" placeholder="Tip: use the employee's email so it matches the Admin/HR login form">
            <?php if (isset($errors['username'])): ?><span class="error"><?php echo $errors['username']; ?></span><?php endif; ?>
        </div>

        <div class="form-field <?php echo isset($errors['password']) ? 'has-error' : ''; ?>">
            <label for="password">Password</label>
            <input type="password" id="password" name="password">
            <?php if (isset($errors['password'])): ?><span class="error"><?php echo $errors['password']; ?></span><?php endif; ?>
        </div>

        <div class="form-field <?php echo isset($errors['role']) ? 'has-error' : ''; ?>">
            <label for="role">Role</label>
            <select id="role" name="role">
                <option value="">Select a role</option>
                <option value="admin" <?php echo $data['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="hr" <?php echo $data['role'] === 'hr' ? 'selected' : ''; ?>>HR</option>
            </select>
            <?php if (isset($errors['role'])): ?><span class="error"><?php echo $errors['role']; ?></span><?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create account</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>