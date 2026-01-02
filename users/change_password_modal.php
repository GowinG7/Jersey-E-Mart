<!-- Profile management ko lagi user password change garna  -->
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

/*  SERVER-SIDE VALIDATION  */

if ($question === '' || $answer === '' || $newpass === '' || $cnewpass === '') {
    $errors[] = "All fields are required.";
}

if ($newpass !== $cnewpass) {
    $errors[] = "Passwords do not match.";
}

if (strlen($newpass) < 8) {
    $errors[] = "Password must be at least 8 characters long.";
}

if (!preg_match('/[A-Z]/', $newpass)) {
    $errors[] = "Password must contain at least one uppercase letter.";
}

if (!preg_match('/[a-z]/', $newpass)) {
    $errors[] = "Password must contain at least one lowercase letter.";
}

if (!preg_match('/[0-9]/', $newpass)) {
    $errors[] = "Password must contain at least one digit.";
}

if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $newpass)) {
    $errors[] = "Password must contain at least one special character.";
}

if (!empty($errors)) {
    // Plain text response only
    echo implode("\n", $errors);
    exit;
}

/*  VERIFY SECURITY QUESTION  */

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

if (
    !$user ||
    $user['security_question'] !== $question ||
    $user['security_answer'] !== $answer
) {
    echo "Security question or answer is incorrect.";
    exit;
}

/*  UPDATE PASSWORD  */

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
$conn->close();
