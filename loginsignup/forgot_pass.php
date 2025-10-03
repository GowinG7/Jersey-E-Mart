<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: Arial, "Arial Black";
            min-height: 100vh;
            margin: 0;
            background-color: #f4f4f9;

        }

        .form-container {
            width: 100%;
            max-width: 400px;
            margin: 24px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .message {
            text-align: center;
            font-size: 16px;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        h1 {
            text-align: center;
            font-weight: 600;
            font-size: 24px;
            color: grey;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-size: 16px;
            color: #0e0707;
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .form-group input:focus {
            border-color: #34e43a;
            outline: none;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: rgb(16, 139, 98);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-submit:hover {
            background-color: #056b0a;
        }

        .footer {
            margin-top: 15px;
            font-size: 17px;
            text-align: center;
            color: #6f6b79e5;
        }

        .footer a {
            color: #5537a8f1;
            text-decoration: none;
            font-weight: bold;
        }

        .footer a:hover {
            color: purple;
        }

        .footer hr {
            border: none;
            height: 1px;
            background: #ddd;
            margin: 15px 0;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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


    <script>
        $(document).ready(function () {
            // Real-time validation for uname
            $("#uname").on("blur keyup", function () {
                $(".uname-error").remove();
                let uname = $(this).val().trim();
                let usernameRegex = /^[a-zA-Z0-9_@]+$/;
                let emailRegex = /^[a-z0-9.]+@(gmail\.com|yahoo\.com|outlook\.com)$/;

                if (uname !== "" && (!usernameRegex.test(uname) && !emailRegex.test(uname))) {
                    $(this).after('<span class="uname-error" style="color:red;">Enter a valid username or email.</span>');
                }
            });

            // Real-time validation for New Password (updated)
            $("#new_password").on("blur keyup", function () {
                $(".password-error").remove();
                let newPassword = $(this).val().trim();

                // Only check for length (at least 8 characters)
                if (newPassword.length < 8) {
                    $(this).after('<span class="password-error" style="color:red;">Password must be at least 8 characters long.</span>');
                }
            });

            // Real-time validation for Confirm Password
            $("#confirm_password").on("blur keyup", function () {
                $(".confirm-password-error").remove();
                let newPassword = $("#new_password").val().trim();
                let confirmPassword = $(this).val().trim();

                if (confirmPassword !== "" && newPassword !== confirmPassword) {
                    $(this).after('<span class="confirm-password-error" style="color:red;">Passwords do not match.</span>');
                }
            });

            // Auto-hide message after 3 seconds
            setTimeout(function () {
                $(".message").fadeOut("slow");
            }, 3000);
        });
    </script>

</body>

</html>