<?php
session_start();
require_once("../shared/dbconnect.php");
include_once("../shared/commonlinks.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    // Get username and password from POST and trim extra spaces
    $uname = trim($_POST['uname']);
    $pass = trim($_POST['pass']);

    // Make sure fields are not empty
    if (!empty($uname) && !empty($pass)) {

        // Step 1: Prepare the SQL query with a placeholder (?)
        $sql = "SELECT id, username, password FROM admin_creden WHERE username = ?";
        $stmt = $conn->prepare($sql);
        // Check if the prepared statement was successfully created
        if ($stmt) {
            // Step 2: Bind the actual value of $uname to the placeholder (s = string)
            $stmt->bind_param("s", $uname);

            // Step 3: Execute the prepared statement
            $stmt->execute();

            // Step 4: Get the result set from the executed statement
            $result = $stmt->get_result();

            // Step 5: Check if any row exists with that username
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // Step 6: Verify the given password against the stored hashed password
                if (password_verify($pass, $row['password'])) {
                    //  Login successful → store session values
                    $_SESSION['admin_id'] = $row['id'];
                    $_SESSION['admin_user'] = $row['username'];

                    // Redirect to dashboard
                    header("Location: dashboard.php");
                    exit();
                } else {
                    //  Password incorrect
                    $_SESSION['errorMessage'] = "Incorrect password!";
                }
            } else {
                //  Username not found in DB
                $_SESSION['errorMessage'] = "Username not found!";
            }

            // Step 7: Close the prepared statement
            $stmt->close();
        } else {
            // If preparing statement failed
            $_SESSION['errorMessage'] = "Something went wrong with the query!";
        }
    } else {
        //  If any input field is empty
        $_SESSION['errorMessage'] = "All fields are required!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login Panel</title>
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