<?php
/**
 * Admin/HR Leave Requests page.
 * Lists all leave requests (optionally filtered by status) with the
 * employee's name attached via a page-level lookup — the LeaveRequest
 * class's getAllLeaveRequests() doesn't join to employees, so rather
 * than changing that method's query, we build a small name lookup here.
 */
require_once __DIR__ . '/includes/db_connect.php';
$requiredRole = 'admin_hr';
require_once __DIR__ . '/includes/auth_guard.php';

$actionMessage = null;
$actionError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leaveId = isset($_POST['leave_id']) ? (int)$_POST['leave_id'] : 0;
    $newStatus = $_POST['new_status'] ?? '';

    if ($leaveId > 0 && in_array($newStatus, ['ACCEPTED', 'REJECTED'], true)) {
        $ok = $leaveRequest->updateLeaveStatus($leaveId, $newStatus);
        $actionMessage = $ok ? 'Leave request updated.' : null;
        $actionError = $ok ? null : 'Could not update that request.';
    } else {
        $actionError = 'Invalid request.';
    }
}

$statusFilter = $_GET['status'] ?? '';
$requests = $leaveRequest->getAllLeaveRequests($statusFilter ?: null);

// Name lookup so we can show who filed each request
$namesStmt = $conn->query("SELECT employee_id, first_name, last_name FROM employees");
$namesById = [];
foreach ($namesStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $namesById[$row['employee_id']] = $row['first_name'] . ' ' . $row['last_name'];
}

$pageTitle = 'Leave Requests';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Leave Requests</h1>
        <p>Review and act on employee leave requests</p>
    </div>
</div>

<?php if ($actionMessage): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($actionMessage); ?></div>
<?php elseif ($actionError): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($actionError); ?></div>
<?php endif; ?>

<form method="get" class="toolbar">
    <div class="toolbar-filters">
        <select name="status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="PENDING" <?php echo $statusFilter === 'PENDING' ? 'selected' : ''; ?>>Pending</option>
            <option value="ACCEPTED" <?php echo $statusFilter === 'ACCEPTED' ? 'selected' : ''; ?>>Accepted</option>
            <option value="REJECTED" <?php echo $statusFilter === 'REJECTED' ? 'selected' : ''; ?>>Rejected</option>
        </select>
    </div>
</form>

<?php if (empty($requests)): ?>
    <div class="empty-state">No leave requests<?php echo $statusFilter ? ' with that status' : ''; ?>.</div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Type</th>
                <th>Start</th>
                <th>End</th>
                <th>Reason</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $lr): ?>
                <tr>
                    <td><?php echo htmlspecialchars($namesById[$lr['employee_id']] ?? 'Unknown'); ?></td>
                    <td><?php echo htmlspecialchars($lr['leave_type']); ?></td>
                    <td><?php echo htmlspecialchars($lr['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($lr['end_date']); ?></td>
                    <td><?php echo htmlspecialchars($lr['reason']); ?></td>
                    <td><?php echo htmlspecialchars($lr['status']); ?></td>
                    <td class="actions-cell">
                        <?php if ($lr['status'] === 'PENDING'): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="leave_id" value="<?php echo (int)$lr['leave_id']; ?>">
                                <input type="hidden" name="new_status" value="ACCEPTED">
                                <button type="submit" class="btn btn-primary btn-sm">Accept</button>
                            </form>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="leave_id" value="<?php echo (int)$lr['leave_id']; ?>">
                                <input type="hidden" name="new_status" value="REJECTED">
                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>