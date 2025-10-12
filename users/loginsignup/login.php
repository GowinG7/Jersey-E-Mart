<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="../css/login.css"> <!-- Link to your CSS file -->
     <!-- jquery v3.7.1 cdn -->
    <script src="shared/jquery-3.7.1.min.js"></script>
</head>

<body>
    <div class="login-form">
        <form action="login.php" method="POST">
            <h4>Login</h4>

            <!-- Username or Email input -->
            <input type="text" name="username" class="form-control" placeholder="Username or Email" id="username"
                required>

            <!-- Password input -->
            <input name="password" type="password" class="form-control" id="password" placeholder="Password" required>

            <!-- Login button -->
            <button name="submit" type="submit" class="btn">Login</button>

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

    <script src="js/login.js"></script>

</body>

</html>