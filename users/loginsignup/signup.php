<?php
session_start();
require_once("../../shared/dbconnect.php");
include_once("../../shared/commonlinks.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {

    // Get form values in local variables
    $name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $question = trim($_POST['question']);
    $answer = trim($_POST['answer']);
    $password = trim($_POST['pass']);
    $cpassword = trim($_POST['cpass']);

    $errors = array();

    //validation checks
    if (!preg_match("/^[A-Za-z]+([ -][A-Za-z]+)*$/", $name)) {
        array_push($errors, "Name must contain only letters, with optional single spaces or hyphens between words. No digits, special characters, or leading/trailing spaces allowed.");
    }

    if (!preg_match("/^[a-zA-Z0-9_@]+$/", $username)) {
        array_push($errors, "Username can only contain letters, numbers, underscores and the @");
    }

    if (!preg_match("/^[a-z0-9.]+@(gmail|yahoo|outlook)\.com$/", $email)) {
        array_push($errors, "Email must contains a-z,0-9,.(dot/period) and end with @gmail.com,@yahoo.com or @outlook.com");
    }

    if (strlen($phone) < 10) {
        array_push($errors, "Phone shouldnot be less than 10");
    }

    if (empty($answer)) {
        array_push($errors, "Security answer can't be empty");
    }
    if (empty($question)) {
        array_push($errors, "Please select a security question");
    }

    if (strlen($password) < 8) {
        array_push($errors, "Password must be at least 8 character long");
    }

    if ($password !== $cpassword) {
        array_push($errors, "Passwords donot match");
    }

    //check for existing username
    $sql = "select * from user_creden where username = '$username'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        array_push($errors, "Username already exists. Please choose a different username");
    }

    //check for exisiting email
    $sql = "select * from user_creden where email = '$email'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        array_push($errors, "Email already exists. Please select the other email");
    }

    //If errors exits -> store in session and redirect (button submit garisake paxi dekhauney message ko lagi store grya session ma 
    if (!empty($errors)) {
        $_SESSION['errorMessages'] = $errors;
        header("Location: signup.php");
        exit();
    }

    //hash the password and insert 
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "Insert into user_creden (name, username, email, phone, password, security_question, security_answer) VALUES (?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sssssss", $name, $username, $email, $phone, $hashedPassword, $question, $answer);

        if ($stmt->execute()) {
            $_SESSION['successMessage'] = "Signup Successful! You can now log in";
            $stmt->close();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['errorMessages'] = ["Error occured during signup. Please try again "];
            $stmt->close();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Form</title>
    <link rel="stylesheet" href="../css/signup.css">
    
</head>

<body>

    <div class="form-container">
        <h1>Create Account</h1>
        <!-- Display success or error messagesafter form submition -->
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

        <form method="POST" action="signup.php" id="signup-form">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" placeholder="Your full name (First, Middle, Last)"
                    required>
                <div class="error-message" id="full_name_error"></div>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="example:hello@123_" required>
                <div class="error-message" id="username_error"></div>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="example:name@gmail.com" required>
                <div class="error-message" id="email_error"></div>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number (Optional)</label>
                <input type="tel" id="phone" name="phone" placeholder="Provide phone number for easy contact">
                <div class="error-message" id="phone_error"></div>
            </div>
            <div class="form-group">
                <label for="question">Choose Security Question:</label>
                <select id="question" name="question" style="color:blue" required>
                    <option value="">Please select:</option>
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
                <div class="error-message" id="answer_error"></div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="pass">Password</label>
                    <input type="password" id="pass" name="pass" placeholder="Enter password" required>
                    <div class="error-message" id="pass_error"></div>
                </div>
                <div class="form-group">
                    <label for="cpass">Confirm Password</label>
                    <input type="password" id="cpass" name="cpass" placeholder="Confirm password" required>
                    <div class="error-message" id="cpass_error"></div>
                </div>
            </div>
            <button type="submit" class="btn-submit" name="submit">Sign Up</button>
        </form>
        <div class="form-footer">
            Already have an account?
            <a href="login.php">Log In</a>
        </div>
    </div>

    <!-- offline jquery v3.7.1 script file -->
        <script src="../../shared/jquery-3.7.1.min.js"></script>
        <script src="../js/hidemessage.js"></script>
        <script src="../js/signup.js"></script>

</body>

</html>

</html>