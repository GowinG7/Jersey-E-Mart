<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="login.css"> <!-- Link to your CSS file -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            // Real-time validation for username or email
            $("#username").on("input", function () {
                var input = $(this).val();
                var usernamePattern = /^[a-zA-Z0-9_@]+$/;
                var emailPattern = /^[a-zA-Z0-9.]+@(gmail|yahoo|outlook)\.com$/;

                if (usernamePattern.test(input) || emailPattern.test(input)) {
                    $("#username_error").text(""); // Clear error message
                } else {
                    $("#username_error").text("Username can only contain letters, numbers, underscores, and the @ symbol, or email only contains a-z,0-9 and (.) and must end with @gmail.com, @yahoo.com, or @outlook.com.");
                }
            });

            // Real-time validation for password
            $("#password").on("input", function () {
                var password = $(this).val();
                if (password.length >= 8) {
                    $("#password_error").text(""); // Clear error message
                } else {
                    $("#password_error").text("Password must be at least 8 characters long.");
                }
            });
        });
    </script>
</head>

<body>
    <div class="login-form">
        <form action="login.php" method="POST">
            <h4>Login</h4>

            <!-- Username or Email input -->
            <input type="text" name="username" class="form-control" placeholder="Username or Email" id="username" required>
              <div class="error-message" id="username_error"></div>

          

            <!-- Password input -->
            <input name="password" type="password" class="form-control" id="password" placeholder="Password" required>
            <div class="error-message" id="password_error"></div>

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
</body>

</html>
