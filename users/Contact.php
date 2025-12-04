<?php
session_start();
require_once "../shared/commonlinks.php";
require_once "../shared/dbconnect.php";

include("header.php");



if (isset($_POST['send'])) {

    //message send garna login garnu parne so checked grya either login gareko xa ki nai
    //yo block bitra check grda page herna ni painxa bina login
    if (!isset($_SESSION["user_id"])) {
        $_SESSION["alert"] = "You must login to send a message.";
        $_SESSION["alert_type"] = "warning";
        header("Location: contact.php");
        exit();
    }

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (
        preg_match("/^[a-zA-Z\s]+$/", $name) &&
        preg_match("/^[a-z0-9\.]+@(gmail\.com|yahoo\.com|outlook\.com)$/", $email) &&
        preg_match("/^[0-9]{10}$/", $phone) &&
        preg_match("/^[a-zA-Z\s]+$/", $subject) &&
        preg_match("/^[a-zA-Z0-9\s]+$/", $message)
    ) {

        $stmt = $conn->prepare("INSERT INTO user_queries(name,email,phone,subject,message) VALUES(?,?,?,?,?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);

        if ($stmt->execute()) {
            $_SESSION["alert"] = "Message sent successfully!";
            $_SESSION["alert_type"] = "success";
        } else {
            $_SESSION["alert"] = "Failed to send message.";
            $_SESSION["alert_type"] = "danger";
        }

        $stmt->close();
    } else {
        $_SESSION["alert"] = "Invalid input. Please check again.";
        $_SESSION["alert_type"] = "danger";
    }

    header("Location: contact.php");
    exit();
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | Jersey E-Mart</title>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .h-line {
            width: 150px;
            height: 2px;
            background-color: #000;
            margin: 10px auto;
        }

        .alert-box {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
        }
    </style>
</head>

<body>

    <?php
    if (isset($_SESSION["alert"])) {
        echo "<div class='alert alert-" . $_SESSION['alert_type'] . " alert-box'>" . $_SESSION['alert'] . "</div>";
        unset($_SESSION["alert"]);
        unset($_SESSION["alert_type"]);
    }
    ?>

    <div class="my-5 px-4">
        <h2 class="fw-bold text-center">CONTACT US</h2>
        <div class="h-line"></div>
        <p class="text-center mt-3 fs-5">
            If you have any queries regarding jerseys, orders, delivery or payment,
            feel free to contact us. We will get back within 24 hours.
        </p>
    </div>

    <div class="container">
        <div class="row">

            <!-- Left Static Info -->
            <div class="col-lg-6 col-md-6 col-12 mb-4 px-4">
                <div class="bg-white rounded shadow p-4">

                    <iframe class="w-100 rounded mb-3" height="320"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d8407.168480256954!2d84.38693763278744!3d27.631362988094352!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3994fa7bee03649d%3A0x6eb3396ddd7fc183!2sindrapuri%20mandir%2C%20Bharatpur%2044200!5e0!3m2!1sen!2snp!4v1764871807942!5m2!1sen!2snp">
                    </iframe>

                    <h5>Address</h5>
                    <p><i class="bi bi-geo-alt-fill"></i> Bharatpur, Nepal</p>

                    <h5 class="mt-4">Call Us</h5>
                    <p><i class="bi bi-telephone-fill"></i> +977-98XXXXXXXX</p>
                    <p><i class="bi bi-telephone-fill"></i> +977-97XXXXXXXX</p>

                    <h5 class="mt-4">Email</h5>
                    <p><i class="bi bi-envelope-fill"></i> support@jerseyemart.com</p>

                    <h5 class="mt-4">Follow Us</h5>
                    <a href="https://www.facebook.com/" class="text-dark fs-4 me-3"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.instagram.com" class="text-dark fs-4"><i class="bi bi-instagram"></i></a>

                </div>
            </div>

            <!-- Right Contact Form -->
            <div class="col-lg-6 col-md-6 col-12 mb-4 px-4">
                <div class="bg-white rounded shadow p-4">

                    <form method="POST" onsubmit="return validateForm();">
                        <h5>Send Us a Message</h5>

                        <div class="mt-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="name" class="form-control shadow-none" required>
                            <small id="nameError" class="text-danger"></small>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control shadow-none" required>
                            <small id="emailError" class="text-danger"></small>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" id="phone" class="form-control shadow-none" required>
                            <small id="phoneError" class="text-danger"></small>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" id="subject" class="form-control shadow-none" required>
                            <small id="subjectError" class="text-danger"></small>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" id="message" rows="5" class="form-control shadow-none"
                                required></textarea>
                            <small id="messageError" class="text-danger"></small>
                        </div>

                        <button type="submit" name="send" class="btn btn-dark mt-3">Send</button>
                    </form>

                </div>
            </div>

        </div>
    </div>


    <script>
        const namePattern = /^[a-zA-Z\s]+$/;
        const emailPattern = /^[a-z0-9\.]+@(gmail\.com|yahoo\.com|outlook\.com)$/;
        const phonePattern = /^[0-9]{10}$/;
        const subjectPattern = /^[a-zA-Z\s]+$/;
        const messagePattern = /^[a-zA-Z0-9.,\-'":\s]+$/;

        function validateName() {
            let v = document.getElementById("name").value.trim();
            document.getElementById("nameError").textContent =
                namePattern.test(v) ? "" : "Only alphabets allowed.";
        }

        function validateEmail() {
            let v = document.getElementById("email").value.trim();
            document.getElementById("emailError").textContent =
                emailPattern.test(v) ? "" : "Invalid email address.";
        }

        function validatePhone() {
            let v = document.getElementById("phone").value.trim();
            document.getElementById("phoneError").textContent =
                phonePattern.test(v) ? "" : "Enter a valid 10-digit number.";
        }

        function validateSubject() {
            let v = document.getElementById("subject").value.trim();
            document.getElementById("subjectError").textContent =
                subjectPattern.test(v) ? "" : "Only alphabets allowed.";
        }

        function validateMessage() {
            let v = document.getElementById("message").value.trim();
            document.getElementById("messageError").textContent =
                messagePattern.test(v) ? "" : "Invalid characters.";
        }

        function validateForm() {
            validateName();
            validateEmail();
            validatePhone();
            validateSubject();
            validateMessage();

            let errors = document.querySelectorAll(".text-danger");
            for (let e of errors) {
                if (e.textContent !== "") return false;
            }
            return true;
        }


        // Auto-hide alerts after 3 seconds
        setTimeout(function () {
            const alertBox = document.querySelector(".alert-box");
            if (alertBox) {
                alertBox.style.transition = "opacity 0.5s";
                alertBox.style.opacity = "0";
                setTimeout(() => alertBox.remove(), 500);
            }
        }, 3000);
    </script>

</body>

</html>