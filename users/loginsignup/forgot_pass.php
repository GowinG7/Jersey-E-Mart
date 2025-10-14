<?php
session_start();
require_once("../../shared/dbconnect.php");
include_once("../../shared/commonlinks.php");

$errors = []; // initialize errors array

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    // get form values
    $unameOrEmail = trim($_POST["unameOrEmail"]);
    $question = trim($_POST["question"]);
    $answer = trim($_POST["answer"]);
    $newpass = trim($_POST["new_password"]);
    $cnewpass = trim($_POST["confirm_password"]);

    // validation
    if (empty($unameOrEmail) || empty($question) || empty($answer) || empty($newpass) || empty($cnewpass)) {
        $errors[] = "All fields are required!";
    }
    if ($newpass !== $cnewpass) {
        $errors[] = "Passwords do not match!";
    }
    if (strlen($newpass) < 8) {
        $errors[] = "Password must be at least 8 characters long!";
    }

    // if no validation errors
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, security_question, security_answer FROM user_creden WHERE username = ? OR email = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $unameOrEmail, $unameOrEmail);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // check security Question and answer
                if ($row["security_question"] === $question && $row["security_answer"] === $answer) {
                    $hashedPassword = password_hash($newpass, PASSWORD_DEFAULT);

                    $update = $conn->prepare("UPDATE user_creden SET password = ? WHERE id = ?");
                    $update->bind_param("si", $hashedPassword, $row["id"]);
                    if ($update->execute()) {
                        $_SESSION["successMessage"] = "Password updated successfully! Please login.";
                        header("Location: forgot_pass.php"); // reload to clear POST
                        exit();
                    } else {
                        $errors[] = "Error while updating password!";
                    }
                    $update->close();
                } else {
                    $errors[] = "Security question and answer do not match!";
                }
            } else {
                $errors[] = "No user found with that username/email!";
            }
            $stmt->close();
        } else {
            $errors[] = "Query failed!";
        }
    }

    // if we have errors, store in session
    if (!empty($errors)) {
        $_SESSION["errorMessages"] = $errors;
        header("Location: forgot_pass.php"); // reload to clear POST
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../css/forgotpass.css">
</head>

<body>
    <div class="form-container">
        <h1>Reset Password</h1>

        <!-- Display success or error messages -->
        <?php
        if (isset($_SESSION['successMessage'])) {
            echo '<div class="success" id="successMessage">' . $_SESSION["successMessage"] . '</div>';
            unset($_SESSION['successMessage']);
        }
        if (isset($_SESSION['errorMessages'])) {
            echo '<div class="errormes" id="errorMessage">';
            foreach ($_SESSION['errorMessages'] as $error) {
                echo '<p>' . htmlspecialchars($error) . '</p>';
            }
            echo '</div>';
            unset($_SESSION['errorMessages']);
        }
        ?>

        <form method="post">
            <div class="form-group">
                <label for="unameOrEmail">Username or Email</label>
                <input type="text" id="unameOrEmail" name="unameOrEmail" placeholder="Enter your username or email"
                    required>

            </div>
            <div class="form-group">
                <label for="question">Choose Security Question:</label>
                <select id="question" name="question" required style="color:grey">
                    <option value="color">Favourite Color</option>
                    <option value="food">Favourite Food</option>
                    <option value="fruit">Favourite Fruit</option>
                    <option value="pet">Favourite Pet</option>
                    <option value="subject">Favourite Subject</option>
                    <option value="place">Favourite Place</option>
                    <option value="laptop">Favourite Laptop</option>
                </select>
            </div>
            <div class="form-group">
                <label for="answer">Enter Answer:</label>
                <input type="text" id="answer" name="answer" placeholder="Enter your answer" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
                <div class="error-message" id="pass_error"></div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password"
                    required>
                <div class="error-message" id="cpass_error"></div>
            </div>
            <button type="submit" name="submit" class="btn-submit">Change Password</button>
        </form>
        <div class="footer">
            <hr>
            <p>Now you can <a href="login.php">Log in</a></p>
        </div>
    </div>

    <!-- jquery v3.7.1 cdn -->
    <script src="../../shared/jquery-3.7.1.min.js"></script>
    <script src="../js/hidemessage.js"></script>
    <script src="../js/forgotpass.js"></script>
</body>

</html>