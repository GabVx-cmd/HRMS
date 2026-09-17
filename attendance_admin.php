<?php
/**
 * Admin/HR Attendance page.
 * Lets HR/admin manually clock an employee in or out (useful for anyone
 * without their own login yet, or to correct a missed entry), and shows
 * all attendance records for a chosen date via Attendance::getAllAttendance().
 */
require_once __DIR__ . '/includes/db_connect.php';
$requiredRole = 'admin_hr';
require_once __DIR__ . '/includes/auth_guard.php';

$actionMessage = null;
$actionError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $empId = isset($_POST['employee_id']) ? (int)$_POST['employee_id'] : 0;

    if ($empId > 0 && in_array($action, ['clock_in', 'clock_out'], true)) {
        if ($action === 'clock_in') {
            $ok = $attendance->clockIn($empId);
            $actionMessage = $ok ? 'Employee clocked in successfully.' : null;
            $actionError = $ok ? null : 'Could not clock in — they may have already clocked in today.';
        } else {
            $ok = $attendance->clockOut($empId);
            $actionMessage = $ok ? 'Employee clocked out successfully.' : null;
            $actionError = $ok ? null : 'Could not clock out — they may not have clocked in today.';
        }
    } else {
        $actionError = 'Please select an employee and an action.';
    }
}

// Employees list, for the manual clock-in/out dropdown
$employeeListStmt = $conn->query("SELECT employee_id, first_name, last_name FROM employees ORDER BY first_name");
$employeeList = $employeeListStmt->fetchAll(PDO::FETCH_ASSOC);

// Records for the selected date (default: today)
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$records = $attendance->getAllAttendance($selectedDate);

// Simple counts computed from the day's records
$presentCount = 0;
$lateCount = 0;
foreach ($records as $r) {
    if ($r['status'] === 'PRESENT') $presentCount++;
    if ($r['status'] === 'LATE') $lateCount++;
}

$pageTitle = 'Attendance';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Attendance</h1>
        <p>Record and review daily attendance</p>
    </div>
</div>

<?php if ($actionMessage): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($actionMessage); ?></div>
<?php elseif ($actionError): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($actionError); ?></div>
<?php endif; ?>

<div class="dashboard-columns" style="margin-bottom: 32px;">
    <section style="flex: 0 0 320px;">
        <h2>Record attendance</h2>
        <form method="post" class="form-grid" style="grid-template-columns: 1fr;">
            <div class="form-field">
                <label for="employee_id">Employee</label>
                <select id="employee_id" name="employee_id">
                    <?php foreach ($employeeList as $emp): ?>
                        <option value="<?php echo (int)$emp['employee_id']; ?>">
                            <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions" style="margin-top: 0;">
                <button type="submit" name="action" value="clock_in" class="btn btn-primary btn-sm">Clock in</button>
                <button type="submit" name="action" value="clock_out" class="btn btn-secondary btn-sm">Clock out</button>
            </div>
        </form>
    </section>

    <section>
        <h2>Today's summary</h2>
        <div class="stat-row" style="border-bottom: none; padding-top: 4px;">
            <div class="stat">
                <span class="value"><?php echo count($records); ?></span>
                <span class="label">Total records</span>
            </div>
            <div class="stat">
                <span class="value"><?php echo $presentCount; ?></span>
                <span class="label">Present</span>
            </div>
            <div class="stat">
                <span class="value"><?php echo $lateCount; ?></span>
                <span class="label">Late</span>
            </div>
        </div>
    </section>
</div>

<form method="get" class="toolbar">
    <div class="toolbar-filters">
        <label for="date" style="align-self: center; font-size: 14px; color: var(--color-muted);">Date:</label>
        <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($selectedDate); ?>" onchange="this.form.submit()">
    </div>
</form>

<?php if (empty($records)): ?>
    <div class="empty-state">No attendance records for this date.</div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                <th>Time in</th>
                <th>Time out</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($r['department']); ?></td>
                    <td><?php echo htmlspecialchars($r['time_in'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($r['time_out'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($r['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>