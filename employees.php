<?php
/**
 * Employees list.
 * Uses Employee::getAllEmployees() exactly as written (search, department,
 * limit, offset) for the actual page of records.
 *
 * The Employee class has no "count" method, so the total-record count used
 * for pagination below is a small standalone query using the same $conn
 * from db_connect.php — this is page-level logic, not a change to the
 * Employee class itself.
 */
$pageTitle = 'Employees';
require_once __DIR__ . '/includes/db_connect.php';

$search = trim($_GET['search'] ?? '');
$department = trim($_GET['department'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 8;
$offset = ($page - 1) * $perPage;

$employees = $employee->getAllEmployees($search, $department, $perPage, $offset);

// Count total matching records for pagination
$countQuery = "SELECT COUNT(*) FROM employees WHERE 1=1";
$countParams = [];
if (!empty($search)) {
    $countQuery .= " AND (first_name LIKE :search1 OR last_name LIKE :search2 OR email LIKE :search3)";
    $countParams[':search1'] = "%$search%";
    $countParams[':search2'] = "%$search%";
    $countParams[':search3'] = "%$search%";
}
if (!empty($department)) {
    $countQuery .= " AND department = :department";
    $countParams[':department'] = $department;
}
$countStmt = $conn->prepare($countQuery);
$countStmt->execute($countParams);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRecords / $perPage));

// Distinct departments for the filter dropdown
$deptStmt = $conn->query("SELECT DISTINCT department FROM employees WHERE department IS NOT NULL AND department != '' ORDER BY department");
$departments = $deptStmt->fetchAll(PDO::FETCH_COLUMN);

$flash = $_GET['flash'] ?? null;

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Employees</h1>
        <p><?php echo $totalRecords; ?> total record<?php echo $totalRecords === 1 ? '' : 's'; ?></p>
    </div>
    <a href="employee_form.php" class="btn btn-primary">Add employee</a>
</div>

<?php if ($flash === 'created'): ?>
    <div class="alert alert-success">Employee added successfully.</div>
<?php elseif ($flash === 'updated'): ?>
    <div class="alert alert-success">Employee updated successfully.</div>
<?php elseif ($flash === 'deleted'): ?>
    <div class="alert alert-success">Employee deleted successfully.</div>
<?php endif; ?>

<form method="get" class="toolbar">
    <div class="toolbar-filters">
        <input type="text" name="search" placeholder="Search name or email" value="<?php echo htmlspecialchars($search); ?>">
        <select name="department" onchange="this.form.submit()">
            <option value="">All departments</option>
            <?php foreach ($departments as $dept): ?>
                <option value="<?php echo htmlspecialchars($dept); ?>" <?php echo $department === $dept ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($dept); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary btn-sm">Search</button>
    </div>
</form>

<?php if (empty($employees)): ?>
    <div class="empty-state">
        <?php if (!empty($search) || !empty($department)): ?>
            No employees match your filters. <a href="employees.php">Clear filters</a>
        <?php else: ?>
            No employee records yet. <a href="employee_form.php">Add your first employee</a>.
        <?php endif; ?>
    </div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Position</th>
                <th>Date hired</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $emp): ?>
                <tr>
                    <td><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($emp['email']); ?></td>
                    <td><?php echo htmlspecialchars($emp['department']); ?></td>
                    <td><?php echo htmlspecialchars($emp['position']); ?></td>
                    <td><?php echo htmlspecialchars($emp['date_hired']); ?></td>
                    <td class="actions-cell">
                        <a href="employee_form.php?id=<?php echo (int)$emp['employee_id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                        <a href="employee_delete.php?id=<?php echo (int)$emp['employee_id']; ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete <?php echo htmlspecialchars(addslashes($emp['first_name'] . ' ' . $emp['last_name'])); ?>? This cannot be undone.');">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a href="employees.php?page=<?php echo $p; ?>&search=<?php echo urlencode($search); ?>&department=<?php echo urlencode($department); ?>"
                   class="btn btn-sm <?php echo $p === $page ? 'btn-primary' : 'btn-secondary'; ?>">
                   <?php echo $p; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>