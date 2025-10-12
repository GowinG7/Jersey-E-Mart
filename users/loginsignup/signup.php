<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Form</title>
    <link rel="stylesheet" href="../css/signup.css">
    <!-- jquery v3.7.1 cdn -->
    <script src="shared/jquery-3.7.1.min.js"></script>
</head>

<body>
    <div class="form-container">
        <h1>Create Account</h1>
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
                <input type="tel" id="phone" name="phone" pattern="[0-9]{10}"
                    placeholder="Provide phone number for easy contact">
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

    <!-- offline jquery v3.7.1 script file -->
    <script src="../../shared/jquery-3.7.1.min.js"></script>
    <script src="../js/signup.js"></script>

</body>

</html>

</html>