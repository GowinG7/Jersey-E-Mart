<?php
session_start();
require_once("../shared/dbconnect.php");
include_once("../shared/commonlinks.php");

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    $uname = $_POST['uname'];
    $pass = $_POST['pass'];

    // Array to hold messages
    $messages = [];

    // Check if username exists
    $checkQuery = "SELECT * FROM admin_creden WHERE username = '$uname'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if ($checkResult && mysqli_num_rows($checkResult) > 0) {
        $messages[] = "Username already exists. Please choose a different username.";
        $_SESSION['errorMessages'] = $messages;
    } else {
        // Hash the password securely
        $hashedPass = password_hash($pass, PASSWORD_DEFAULT);

        // Insert new admin credentials
        $insertQuery = "INSERT INTO admin_creden (username, password) VALUES ('$uname', '$hashedPass')";
        if (mysqli_query($conn, $insertQuery)) {
            $_SESSION['successMessage'] = "Signup successful. You can now log in.";
        } else {
            $messages[] = "Error: " . mysqli_error($conn);
            $_SESSION['errorMessages'] = $messages;
        }
    }

    // Redirect to same page to show messages
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
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
    <script src="shared/jquery-3.7.1.min.js"></script>
</head>

<body>

    <form action="" method="POST">
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
        </div>

        <div>
            <label for="pass">Password:</label>
            <input type="password" id="pass" name="pass" placeholder="Enter password" required>
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

    <script src="js/hidemessage.js"></script>
</body>

</html>