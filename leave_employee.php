<?php
/**
 * Employee Leave Requests page.
 * File a new request via LeaveRequest::fileLeave(), and view own
 * history via getLeaveRequests(). Employees cannot approve/reject —
 * that's admin/hr only, on leave_admin.php.
 */
require_once __DIR__ . '/includes/db_connect.php';
$requiredRole = 'employee';
require_once __DIR__ . '/includes/auth_guard.php';

$employeeId = (int)$_SESSION['employee_id'];
$errors = [];
$data = [
    'leave_type' => '',
    'start_date' => '',
    'end_date'   => '',
    'reason'     => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['leave_type'] = trim($_POST['leave_type'] ?? '');
    $data['start_date'] = trim($_POST['start_date'] ?? '');
    $data['end_date']   = trim($_POST['end_date'] ?? '');
    $data['reason']     = trim($_POST['reason'] ?? '');

    if ($data['leave_type'] === '') $errors['leave_type'] = 'Please select a leave type.';
    if ($data['start_date'] === '') $errors['start_date'] = 'Start date is required.';
    if ($data['end_date'] === '') {
        $errors['end_date'] = 'End date is required.';
    } elseif ($data['start_date'] !== '' && $data['end_date'] < $data['start_date']) {
        $errors['end_date'] = 'End date cannot be before the start date.';
    }
    if ($data['reason'] === '') $errors['reason'] = 'Please provide a reason.';

    if (empty($errors)) {
        $ok = $leaveRequest->fileLeave(
            $employeeId,
            $data['reason'],
            $data['leave_type'],
            $data['start_date'],
            $data['end_date']
        );
        if ($ok) {
            header('Location: leave_employee.php?flash=filed');
            exit;
        }
        $errors['general'] = 'Something went wrong filing your request. Please try again.';
    }
}

$flash = $_GET['flash'] ?? null;
$history = $leaveRequest->getLeaveRequests($employeeId, null);

$pageTitle = 'My Leave Requests';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>My Leave Requests</h1>
        <p>File a new request or check the status of past ones</p>
    </div>
</div>

<?php if ($flash === 'filed'): ?>
    <div class="alert alert-success">Leave request submitted. You'll see its status below once HR reviews it.</div>
<?php endif; ?>

<?php if (!empty($errors['general'])): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($errors['general']); ?></div>
<?php endif; ?>

<h2>File a new request</h2>
<form method="post" class="form-grid" novalidate>
    <div class="form-field <?php echo isset($errors['leave_type']) ? 'has-error' : ''; ?>">
        <label for="leave_type">Leave type</label>
        <select id="leave_type" name="leave_type">
            <option value="">Select type</option>
            <option value="SICK_LEAVE" <?php echo $data['leave_type'] === 'SICK_LEAVE' ? 'selected' : ''; ?>>Sick Leave</option>
            <option value="VACATION_LEAVE" <?php echo $data['leave_type'] === 'VACATION_LEAVE' ? 'selected' : ''; ?>>Vacation Leave</option>
            <option value="EMERGENCY_LEAVE" <?php echo $data['leave_type'] === 'EMERGENCY_LEAVE' ? 'selected' : ''; ?>>Emergency Leave</option>
        </select>
        <?php if (isset($errors['leave_type'])): ?><span class="error"><?php echo $errors['leave_type']; ?></span><?php endif; ?>
    </div>

    <div class="form-field"></div>

    <div class="form-field <?php echo isset($errors['start_date']) ? 'has-error' : ''; ?>">
        <label for="start_date">Start date</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($data['start_date']); ?>">
        <?php if (isset($errors['start_date'])): ?><span class="error"><?php echo $errors['start_date']; ?></span><?php endif; ?>
    </div>

    <div class="form-field <?php echo isset($errors['end_date']) ? 'has-error' : ''; ?>">
        <label for="end_date">End date</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($data['end_date']); ?>">
        <?php if (isset($errors['end_date'])): ?><span class="error"><?php echo $errors['end_date']; ?></span><?php endif; ?>
    </div>

    <div class="form-field full <?php echo isset($errors['reason']) ? 'has-error' : ''; ?>">
        <label for="reason">Reason</label>
        <textarea id="reason" name="reason" rows="3"><?php echo htmlspecialchars($data['reason']); ?></textarea>
        <?php if (isset($errors['reason'])): ?><span class="error"><?php echo $errors['reason']; ?></span><?php endif; ?>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Submit request</button>
    </div>
</form>

<h2>History</h2>
<?php if (empty($history)): ?>
    <div class="empty-state">You haven't filed any leave requests yet.</div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Start</th>
                <th>End</th>
                <th>Filed</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $lr): ?>
                <tr>
                    <td><?php echo htmlspecialchars($lr['leave_type']); ?></td>
                    <td><?php echo htmlspecialchars($lr['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($lr['end_date']); ?></td>
                    <td><?php echo htmlspecialchars($lr['date_filed']); ?></td>
                    <td><?php echo htmlspecialchars($lr['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>