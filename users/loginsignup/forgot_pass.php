<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../css/forgotpass.css">
    <!-- jquery v3.7.1 cdn -->
    <script src="shared/jquery-3.7.1.min.js"></script>
</head>

<body>
    <div class="form-container">
        <h1>Reset Password</h1>
        <form method="post">
            <div class="form-group">
                <label for="uname">Username or Email</label>
                <input type="text" id="uname" name="uname" placeholder="Enter your username or email" required>
            </div>
            <div class="form-group">
                <label for="question">Choose Security Question:</label>
                <select id="question" name="question" required style="color:grey">
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
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password"
                    required>
            </div>
            <button type="submit" class="btn-submit">Change Password</button>
        </form>
        <div class="footer">
            <hr>
            <p>Now you can <a href="login.php">Log in</a></p>
        </div>
    </div>


    <!-- jquery v3.7.1 cdn -->
    <script src="../../shared/jquery-3.7.1.min.js"></script>

    <script src="../js/forgotpass.js"></script>

</body>

</html>