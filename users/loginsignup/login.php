<?php
session_start();
require_once("../../shared/dbconnect.php");
include_once("../../shared/commonlinks.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    // Get username/email and password
    $unameOrEmail = trim($_POST["unameOrEmail"]);
    $pass = trim($_POST["password"]);

    if (!empty($unameOrEmail) && !empty($pass)) {
        // Prepare query to check for username OR email
        $stmt = $conn->prepare("SELECT id, username, password FROM user_creden WHERE username = ? OR email = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $unameOrEmail, $unameOrEmail);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // Verify hashed password
                if (password_verify($pass, $row["password"])) {
                    $_SESSION["user_id"] = $row["id"];
                    $_SESSION["user_name"] = $row["username"];

                    header("Location: homepage.php");
                    exit();
                } else {
                    $_SESSION["errorMessage"] = "Incorrect password!";
                }
            } else {
                $_SESSION["errorMessage"] = "Username or email not found!";
            }

            $stmt->close();
        } else {
            $_SESSION["errorMessage"] = "Something went wrong with the query!";
        }
    } else {
        $_SESSION["errorMessage"] = "All fields are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="../css/login.css"> <!-- Link to your CSS file -->

</head>

<body>
    <div class="login-form">
        <form action="login.php" method="POST">
            <h4>Login</h4>

            <!-- Show messages -->
            <?php
            // if (isset($_SESSION['successMessage'])) {
            //     echo "<div class='success' id='successMessage'>" . $_SESSION['successMessage'] . "</div>";
            //     unset($_SESSION['successMessage']);
            // }
            if (isset($_SESSION['errorMessage'])) {
                echo "<div class='error' id='errorMessage'>" . $_SESSION['errorMessage'] . "</div>";
                unset($_SESSION['errorMessage']);
            }
            ?>

            <!-- Username or Email input -->
            <input type="text" name="unameOrEmail" id="unameOrEmail" class="form-control"
                placeholder="Username or Email" required>

            <!-- Password input -->
            <input name="password" type="password" class="form-control" id="password" placeholder="Password" required>

            <!-- Login button -->
            <button type="submit" name="submit" class="btn">Login</button>

            <!-- Footer links -->
            <div class="footer">
                <a href="forgot_pass.php">Forgot your password?</a>
                <hr>
                <div>
                    Don't have an account?
                    <a href="signup.php">Sign up</a>
                </div>
            </div>
        </form>
    </div>


    <!-- offline jquery v3.7.1 script file -->
    <script src="../../shared/jquery-3.7.1.min.js"></script>
    <script src="../js/hidemessage.js"></script>
    <script src="../js/login.js"></script>

</body>

</html>