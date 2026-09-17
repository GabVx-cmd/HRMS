<?php
/**
 * Add / Edit Employee (one file handles both).
 * GET  ?id=X  -> loads existing record for editing
 * POST         -> validates and calls Employee::createEmployee() or
 *                 updateEmployee(), whichever applies
 *
 * Validation here is page-level (required fields, email format, duplicate
 * email check) since the Employee class itself doesn't validate input —
 * it just runs whatever it's given against the database.
 */
require_once __DIR__ . '/includes/db_connect.php';
$requiredRole = 'admin_hr';
require_once __DIR__ . '/includes/auth_guard.php';

$isEdit = false;
$employeeId = null;
$errors = [];
$data = [
    'first_name' => '',
    'last_name'  => '',
    'email'      => '',
    'phone'      => '',
    'address'    => '',
    'date_hired' => '',
    'department' => '',
    'position'   => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = !empty($_POST['employee_id']) ? (int)$_POST['employee_id'] : null;
    $isEdit = $employeeId !== null;

    $data['first_name'] = trim($_POST['first_name'] ?? '');
    $data['last_name']  = trim($_POST['last_name'] ?? '');
    $data['email']      = trim($_POST['email'] ?? '');
    $data['phone']      = trim($_POST['phone'] ?? '');
    $data['address']    = trim($_POST['address'] ?? '');
    $data['date_hired'] = trim($_POST['date_hired'] ?? '');
    $data['department'] = trim($_POST['department'] ?? '');
    $data['position']   = trim($_POST['position'] ?? '');

    if ($data['first_name'] === '') $errors['first_name'] = 'First name is required.';
    if ($data['last_name'] === '') $errors['last_name'] = 'Last name is required.';

    if ($data['email'] === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }

    if ($data['date_hired'] === '') {
        $errors['date_hired'] = 'Date hired is required.';
    }
    if ($data['department'] === '') $errors['department'] = 'Department is required.';
    if ($data['position'] === '') $errors['position'] = 'Position is required.';

    // Duplicate email check (excluding the current record when editing)
    if (empty($errors['email'])) {
        $dupQuery = "SELECT employee_id FROM employees WHERE email = :email";
        if ($isEdit) {
            $dupQuery .= " AND employee_id != :employee_id";
        }
        $dupStmt = $conn->prepare($dupQuery);
        $dupStmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
        if ($isEdit) {
            $dupStmt->bindValue(':employee_id', $employeeId, PDO::PARAM_INT);
        }
        $dupStmt->execute();
        if ($dupStmt->fetch()) {
            $errors['email'] = 'This email is already used by another employee.';
        }
    }

    if (empty($errors)) {
        if ($isEdit) {
            $employee->updateEmployee($employeeId, $data);
            header('Location: employees.php?flash=updated');
            exit;
        } else {
            $employee->createEmployee($data);
            header('Location: employees.php?flash=created');
            exit;
        }
    }
} elseif (isset($_GET['id'])) {
    $employeeId = (int)$_GET['id'];
    $existing = $employee->getEmployeeById($employeeId);
    if ($existing) {
        $data = $existing;
        $isEdit = true;
    } else {
        header('Location: employees.php');
        exit;
    }
}

$pageTitle = $isEdit ? 'Edit Employee' : 'Add Employee';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><?php echo $isEdit ? 'Edit Employee' : 'Add Employee'; ?></h1>
        <p><?php echo $isEdit ? "Update this employee's information" : "Enter the new employee's details"; ?></p>
    </div>
</div>

<form method="post" class="form-grid" novalidate>
    <?php if ($isEdit): ?>
        <input type="hidden" name="employee_id" value="<?php echo (int)$employeeId; ?>">
    <?php endif; ?>

    <div class="form-field <?php echo isset($errors['first_name']) ? 'has-error' : ''; ?>">
        <label for="first_name">First name</label>
        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($data['first_name']); ?>">
        <?php if (isset($errors['first_name'])): ?><span class="error"><?php echo $errors['first_name']; ?></span><?php endif; ?>
    </div>

    <div class="form-field <?php echo isset($errors['last_name']) ? 'has-error' : ''; ?>">
        <label for="last_name">Last name</label>
        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($data['last_name']); ?>">
        <?php if (isset($errors['last_name'])): ?><span class="error"><?php echo $errors['last_name']; ?></span><?php endif; ?>
    </div>

    <div class="form-field <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($data['email']); ?>">
        <?php if (isset($errors['email'])): ?><span class="error"><?php echo $errors['email']; ?></span><?php endif; ?>
    </div>

    <div class="form-field">
        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($data['phone']); ?>">
    </div>

    <div class="form-field full">
        <label for="address">Address</label>
        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($data['address']); ?>">
    </div>

    <div class="form-field <?php echo isset($errors['date_hired']) ? 'has-error' : ''; ?>">
        <label for="date_hired">Date hired</label>
        <input type="date" id="date_hired" name="date_hired" value="<?php echo htmlspecialchars($data['date_hired']); ?>">
        <?php if (isset($errors['date_hired'])): ?><span class="error"><?php echo $errors['date_hired']; ?></span><?php endif; ?>
    </div>

    <div class="form-field <?php echo isset($errors['department']) ? 'has-error' : ''; ?>">
        <label for="department">Department</label>
        <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($data['department']); ?>">
        <?php if (isset($errors['department'])): ?><span class="error"><?php echo $errors['department']; ?></span><?php endif; ?>
    </div>

    <div class="form-field <?php echo isset($errors['position']) ? 'has-error' : ''; ?>">
        <label for="position">Position</label>
        <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($data['position']); ?>">
        <?php if (isset($errors['position'])): ?><span class="error"><?php echo $errors['position']; ?></span><?php endif; ?>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?php echo $isEdit ? 'Save changes' : 'Add employee'; ?></button>
        <a href="employees.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>