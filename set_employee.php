<?php
session_start();

$role = $_SESSION['user_role'] ?? '';
$is_admin = in_array(strtolower($role), ['admin'], true);

if (! $is_admin) {
    header('Location: employees.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_employee'], $_POST['employee_id'])) {
    $employeeId = (int) $_POST['employee_id'];

    require_once __DIR__ . '/config.php';
    $conn = getDBConnection();

    $stmt = $conn->prepare('DELETE FROM users WHERE id = ? AND role = ?');
    if ($stmt) {
        $roleValue = 'employee';
        $stmt->bind_param('is', $employeeId, $roleValue);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();
    header('Location: employees.php');
    exit;
}

header('Location: employees.php');
exit;
