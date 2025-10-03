

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Form</title>
    <link rel="stylesheet" href="signup.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="form-container">
        <h1>Create Account</h1>
        <form method="POST" action="signup.php" id="signup-form">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" placeholder="Your full name (First, Middle, Last)" required>
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
                <input type="tel" id="phone" name="phone" pattern="[0-9]{10}" placeholder="Provide phone number for easy contact">
                <div class="error-message" id="phone_error"></div>
            </div>
            <div class="form-group">
                <label for="question">Choose Security Question:</label>
                <select id="question" name="question" style="color:grey" required>
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
    <script>
        $(document).ready(function () {
            $('#full_name').on('blur', function () {
                var fullName = $(this).val();
                if (!/^[A-Za-z]+( [A-Za-z]+)*$/.test(fullName)) {
                    $('#full_name_error').text('Name should only contain letters, and spaces are allowed between words but not at the start.').show();
                } else {
                    $('#full_name_error').hide();
                }
            });
            $('#username').on('blur', function () {
                var username = $(this).val();
                if (!/^[a-zA-Z0-9_@]+$/.test(username)) {
                    $('#username_error').text('Username can only contain letters, numbers, underscores, and the @ symbol.').show();
                } else {
                    $('#username_error').hide();
                }
            });
            $('#email').on('blur', function () {
                var email = $(this).val();
                if (!/^[a-z0-9.]+@(gmail|yahoo|outlook)\.com$/.test(email)) {
                    $('#email_error').text('Email must contains only letters(a-z),numbers(0-9)and periods or dot(.) and email must end with @gmail.com, @yahoo.com, or @outlook.com.').show();
                } else {
                    $('#email_error').hide();
                }
            });
            $('#pass').on('blur', function () {
                var pass = $(this).val();
                if (pass.length < 8) {
                    $('#pass_error').text('Password must be at least 8 characters long.').show();
                } else {
                    $('#pass_error').hide();
                }
            });
            $('#answer').on('blur', function () {
                var answer = $(this).val().trim();
                if (answer === '') {
                    $('#answer_error').text('Security answer cannot be empty.').show();
                } else {
                    $('#answer_error').hide();
                }
            });
            $('#cpass').on('blur', function () {
                var cpass = $(this).val();
                if (cpass !== $('#pass').val()) {
                    $('#cpass_error').text('Passwords do not match.').show();
                } else {
                    $('#cpass_error').hide();
                }
            });
        });
    </script>
</body>
</html>
</html>
