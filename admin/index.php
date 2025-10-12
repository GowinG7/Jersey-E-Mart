<?php
session_start();
include_once("../dbconnect.php");

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
            $_SESSION['successMessage'] = "Login Successful! Redirecting...";
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
    <style>
        * {
            font-family: Arial, "Arial Black";
        }

        body {
            background-color: #E7F2EF;
            font-weight: bold;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: -100px;
            height: 100vh;
        }

        form {
            border-radius: 7px;
            padding: 20px;
            width: 300px;
            background-color: whitesmoke;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .heading {
            background-color: black;
            color: white;
            padding: 17px;
            margin: -20px -20px 20px -20px;
            border-radius: 7px 7px 0 0;
            text-align: center;
            font-size: 24px;
        }

        .input {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }

        .input input {
            font-size: 15px;
            padding: 10px;
            border: 1px solid #0000002c;
            border-radius: 5px;
            outline: none;
        }

        .input input:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        input::placeholder {
            text-align: center;
        }

        button {
            width: 50%;
            padding: 9px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
        }

        .button {
            display: flex;
            justify-content: center;
        }

        button:hover {
            background-color: #12cc3bff;
        }

        /* message styles */
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <form method="POST" action="">
        <div class="heading">Admin Login Panel</div>

        <!-- Show messages -->
        <?php
        if (isset($_SESSION['successMessage'])) {
            echo "<div class='success' id='successMessage'>" . $_SESSION['successMessage'] . "</div>";
            unset($_SESSION['successMessage']);
        }
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