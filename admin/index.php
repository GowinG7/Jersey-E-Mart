<?php
include("../dbconnect.php");


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $uname = $_POST['uname'];
    $pass = $_POST['pass'];

    $query = "Select * from admin_creden where admin_username = '$uname'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        if ($pass === $row['admin_pass']) {

            header("Location: dashboard.php");
            exit();
        } else {
            echo "Incorrect password";
        }
    } else {
        echo "Username not found";
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
            min-height: 100vh;
            /*side ma scroll bar aathiyo so tyo hatauna min-height and margin 0 */
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
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
    </style>
</head>

<body>
    <form method="POST" action="">
        <div class="heading">Admin Login Panel</div>
        <div class="input">
            <input type="text" name="uname" placeholder="Username" required>
            <input type="password" name="pass" placeholder="Password" required>
        </div>
        <div class="button">
            <button type="submit" name="login">Login</button>
        </div>

    </form>
</body>

</html>