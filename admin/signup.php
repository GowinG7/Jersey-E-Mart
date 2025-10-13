<?php
session_start();
require_once("../shared/dbconnect.php");
include_once("../shared/commonlinks.php");

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    $uname = trim($_POST["uname"]);
    $pass = trim($_POST["pass"]);
    $errors = [];

    // Username validation
    if (!preg_match("/^[a-zA-Z0-9_@]+$/", $uname)) {
        $errors[] = "Username can only contain letters, numbers, underscores and @";
    }

    // Password validation
    if (strlen($pass) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    //jata pani baye huney check grnu xa baney starting ra ending ko lagi ^ ra +$ pardaina 
    if (!preg_match("/[A-Z]/", $pass)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match("/[a-z]/", $pass)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match("/[0-9]/", $pass)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match("/[\W]/", $pass)) {
        $errors[] = "Password must contain at least one special character";
    }

    // Check for existing username
    $sql = "select * from admin_creden where username = '$uname'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $errors[] = "Username already exists. Please choose a different username.";
    }

    // If errors exist → store in session and redirect button submit garisake paxi dekhine erros ko lagi store grya session ma
    if (!empty($errors)) {
        $_SESSION['errorMessages'] = $errors;
        header("Location: signup.php");
        exit();
    }

    // Hash the password and insert new user
    $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);
    $sql = "INSERT INTO admin_creden (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $uname, $hashedPassword);

    if ($stmt->execute()) {
        $_SESSION['successMessage'] = "Signup successful! You can now log in.";
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['errorMessages'] = ["Error occurred during signup. Please try again."];
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Signup Form</title>
    <link rel="stylesheet" href="css/signup.css">
    <!-- jquery v3.7.1 cdn -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>

    <form id="signup-form" action="" method="POST">
        <div class="top">
            <h3>Admin Signup</h3>
        </div>

        <!-- Display messages -->
        <?php
        if (isset($_SESSION['successMessage'])) {
            echo '<div class="success" id="successMessage">' . $_SESSION['successMessage'] . '</div>';
            unset($_SESSION['successMessage']);
        }
        if (isset($_SESSION['errorMessages'])) {
            // yeha mathi ko success messages jasari nai dekhayeni hunthiyo kiniki error messages array ko form ma rakhey ni
            // hmle lagayeko condition anusar euta matra error aauxa so tara paxi ko lagi multiple error auula banera rakheko yo
            echo '<div class="errormes" id="errorMessage">';
            foreach ($_SESSION['errorMessages'] as $error) {
                echo '<p>' . $error . '</p>';
            }
            echo '</div>';
            unset($_SESSION['errorMessages']);
        }
        ?>

        <div>
            <label for="uname">Username:</label>
            <input type="text" id="uname" name="uname" placeholder="Username" required>
            <div class="error_message" id="uname_error"></div>
        </div>

        <div>
            <label for="pass">Password:</label>
            <input type="password" id="pass" name="pass" placeholder="Enter password" required>
            <div class="error_message" id="pass_error"></div>
        </div>

        <button type="submit" name="submit">Sign Up</button>

        <hr>

        <div id="navlink">
            Already have an account?
            <a href="index.php"> log In</a>
        </div>
    </form>

    <!-- offline jquery v3.7.1 script file -->
    <script src="../shared/jquery-3.7.1.min.js"></script>
    <script src="js/signup.js"></script>
    <script src="js/hidemessage.js"></script>
</body>

</html>