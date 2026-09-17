<?php
/**
 * Login page.
 * Two modes, matching the two auth paths that already exist in the backend:
 *   - Employee:  Employee::employeeLogin(email, employee_id)  — no password
 *   - Admin/HR:  User::login(username, password)               — username
 *     doubles as the account's email for this form (see the sample
 *     account in schema.sql), so the User class itself needed no changes.
 */
require_once __DIR__ . '/includes/db_connect.php';

// If already logged in, skip straight to the right place
if (!empty($_SESSION['user_type'])) {
    header('Location: ' . ($_SESSION['user_type'] === 'admin_hr' ? 'index.php' : 'employee_dashboard.php'));
    exit;
}

$errors = [];
$activeTab = $_GET['type'] ?? 'employee';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginType = $_POST['login_type'] ?? 'employee';
    $activeTab = $loginType;

    if ($loginType === 'employee') {
        $email = trim($_POST['email'] ?? '');
        $employeeIdInput = trim($_POST['employee_id'] ?? '');

        if ($email === '' || $employeeIdInput === '' || !ctype_digit($employeeIdInput)) {
            $errors[] = 'Please enter a valid email and employee ID.';
        } else {
            $ok = $employee->employeeLogin($email, (int)$employeeIdInput);
            if ($ok) {
                $empData = $employee->getEmployeeData();
                $_SESSION['user_type'] = 'employee';
                $_SESSION['employee_id'] = $empData['employee_id'];
                $_SESSION['employee_name'] = $empData['first_name'] . ' ' . $empData['last_name'];
                header('Location: employee_dashboard.php');
                exit;
            }
            $errors[] = 'Email or Employee ID is incorrect.';
        }
    } else {
        $usernameOrEmail = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($usernameOrEmail === '' || $password === '') {
            $errors[] = 'Please enter both email and password.';
        } else {
            $ok = $user->login($usernameOrEmail, $password);
            if ($ok) {
                $userData = $user->getUserData();
                $_SESSION['user_type'] = 'admin_hr';
                $_SESSION['user_id'] = $userData['user_id'];
                $_SESSION['employee_id'] = $userData['employee_id'];
                $_SESSION['username'] = $userData['username'];
                $_SESSION['role'] = $userData['role'];
                header('Location: index.php');
                exit;
            }
            $errors[] = 'Incorrect email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — HRMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="login-brand">
            <span class="mark">HRMS</span>
            <span class="sub">Employee Records</span>
        </div>

        <div class="login-tabs">
            <a href="login.php?type=employee" class="<?php echo $activeTab === 'employee' ? 'active' : ''; ?>">Employee</a>
            <a href="login.php?type=admin" class="<?php echo $activeTab === 'admin' ? 'active' : ''; ?>">Admin / HR</a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errors[0]); ?></div>
        <?php endif; ?>

        <?php if ($activeTab === 'employee'): ?>
            <form method="post" class="login-form">
                <input type="hidden" name="login_type" value="employee">
                <div class="form-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-field">
                    <label for="employee_id">Employee ID</label>
                    <input type="number" id="employee_id" name="employee_id" required>
                </div>
                <button type="submit" class="btn btn-primary">Log in</button>
            </form>
        <?php else: ?>
            <form method="post" class="login-form">
                <input type="hidden" name="login_type" value="admin">
                <div class="form-field">
                    <label for="username">Email</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Log in</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>