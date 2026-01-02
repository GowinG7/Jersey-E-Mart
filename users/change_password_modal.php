<?php
session_start();
require_once "../shared/dbconnect.php";

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access.";
    exit;
}

$user_id = $_SESSION['user_id'];

$question = trim($_POST['question'] ?? '');
$answer = trim($_POST['answer'] ?? '');
$newpass = trim($_POST['new_password'] ?? '');
$cnewpass = trim($_POST['confirm_password'] ?? '');

$errors = [];

// Validation
if (!$question || !$answer || !$newpass || !$cnewpass) {
    $errors[] = "All fields are required.";
}

if ($newpass !== $cnewpass) {
    $errors[] = "Passwords do not match.";
}

if (strlen($newpass) < 8) {
    $errors[] = "Password must be at least 8 characters.";
}

if (!empty($errors)) {
    echo implode("<br>", $errors);
    exit;
}

// Fetch security question & answer
$stmt = $conn->prepare("
    SELECT security_question, security_answer
    FROM user_creden
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Verify security
if (
    $user['security_question'] !== $question ||
    $user['security_answer'] !== $answer
) {
    echo "Security question or answer is incorrect.";
    exit;
}

// Update password
$hashedPassword = password_hash($newpass, PASSWORD_DEFAULT);

$update = $conn->prepare("
    UPDATE user_creden
    SET password = ?
    WHERE id = ?
");
$update->bind_param("si", $hashedPassword, $user_id);

if ($update->execute()) {
    echo "success";
} else {
    echo "Failed to update password.";
}

$update->close();
