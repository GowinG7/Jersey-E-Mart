<?php
session_start();
include_once("../dbconnect.php");

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
<style>
* { font-family: Arial, "Arial Black"; }

body {
    background-color: azure;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: -100px;
    height: 100vh;
}

form {
    background-color: whitesmoke;
    width: 300px;
    border: 1px solid black;
    border-radius: 7px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    padding-bottom: 15px;
}

.top {
    background-color: rgba(0,0,0,0.72);
    color: white;
    padding: 8px;
    margin: 0 0 28px 0;
    border-radius: 4px 4px 0 0;
    text-align: center;
}

div { margin: 5px 0 12px 20px; }

button {
    width: 60%;
    border: none;
    margin: 15px 0 0 57px;
    padding: 9px 7px;
    border-radius: 5px;
    background-color: rgba(7,83,100,0.79);
    font-size: medium;
    color: white;
    font-weight: 600;
}

button:hover { background-color: rgba(10,62,73,1); cursor: pointer; }

input[type="password"] {
    margin-left: 3px;
   
}

.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    padding: 10px;
    margin: 0 20px 10px 20px;
    border-radius: 5px;
    text-align: center;
}

.errormes {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    padding: 10px;
    margin: 0 20px 10px 20px;
    border-radius: 5px;
    text-align: center;
}
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<form action="" method="POST">
    <div class="top"><h3>Admin Signup</h3></div>

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
</form>

<!-- this is code of jQuery which automatically hides the success and error messages after some time -->
<script>
    // html document purai load nahunjel samma wait grney
$(document).ready(function () {
    //successMessage id ko element cha ki chaina bhanera check grney
    if ($("#successMessage").length) {
        //yedi xa baney time set grney 3 seconds=3000 millisecondsnhide grna
        //time set grnu taaki user le padna sakunn k raixa k vayo
        setTimeout(() => $("#successMessage").fadeOut("slow"), 3000);
    }
    if ($("#errorMessage").length) {
        setTimeout(() => $("#errorMessage").fadeOut("slow"), 5000);
    }
});
</script>
</body>
</html>
