<?php
session_start();
require_once("../shared/dbconnect.php");
include_once("../shared/commonlinks.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $uname = $_POST['uname'];
    $pass = $_POST['pass'];

    $query = "SELECT * FROM admin_creden WHERE username = '$uname'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // verify hashed password
        if (password_verify($pass, $row['password'])) {
            // login successful
            // $_SESSION['successMessage'] = "Login Successful! Redirecting...";
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['errorMessage'] = "Incorrect password!";
        }
    } else {
        $_SESSION['errorMessage'] = "Username not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login Panel</title>
    <!-- jquery v3.7.1 cdn -->
    <script src="shared/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="css/index.css">
</head>

<body>
    <form method="POST" action="">
        <div class="heading">Admin Login Panel</div>

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

        <div class="input">
            <input type="text" name="uname" placeholder="Username" required>
            <input type="password" name="pass" placeholder="Password" required>
        </div>
        <div class="button">
            <button type="submit" name="login">Login</button>
        </div>
    </form>
    <!-- offline jquery v3.7.1 script file -->
    <script src="../shared/jquery-3.7.1.min.js"></script>
    <script src="js/hidemessage.js"></script>
</body>

</html>