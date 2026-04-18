<!-- User Profile management -->
<?php
session_start();
require_once "../shared/dbconnect.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: loginsignup/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

//FETCH USER DATA 
$stmt = $conn->prepare("
    SELECT name, username, email, phone, security_question, security_answer, is_verified, created_at
    FROM user_creden
    WHERE id = ? LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

?>

<!DOCTYPE html>
<html>

<head>
    <title>User Profile</title>
    <link rel="stylesheet" href="css/profile.css">
</head>

<body style="background-color: #e0f4f2;">

    <?php include_once 'header.php'; ?>

    <div class="profile-card" style="max-width:500px;margin:30px auto;padding:15px;">


        <div id="profileMsg" class="msg" style="display:none"></div>

        <form id="profileForm">

            <div class="row">
                <div class="label">Full Name</div>
                <div class="value"><input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>"></div>
            </div>

            <div class="row">
                <div class="label">Username</div>
                <div class="value"><input type="text" name="username"
                        value="<?= htmlspecialchars($user['username']) ?>"></div>
            </div>

            <div class="row">
                <div class="label">Email</div>
                <div class="value"><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                </div>
            </div>

            <div class="row">
                <div class="label">Phone</div>
                <div class="value"><input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                </div>
            </div>

            <div class="row">
                <div class="label">Security Question</div>
                <div class="value"><input type="text" name="security_question"
                        value="<?= htmlspecialchars($user['security_question']) ?>"></div>
            </div>

            <div class="row">
                <div class="label">Security Answer</div>
                <div class="value"><input type="text" name="security_answer"
                        value="<?= htmlspecialchars($user['security_answer']) ?>"></div>
            </div>

            <div class="row">
                <div class="label">Account Status</div>
                <div class="value status"><?= $user['is_verified'] ? 'Verified' : 'Not Verified' ?></div>
            </div>

            <div class="row">
                <div class="label">Joined On</div>
                <div class="value"><?= date("d M Y", strtotime($user['created_at'])) ?></div>
            </div>

            <div style="text-align:center;margin-top:20px;">
                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
            </div>

            <div style="text-align:center;margin-top:25px;">
                <button type="button" onclick="openModal()" class="btn btn-secondary">
                    Change Password
                </button>
            </div>

            <div style="text-align:center;margin-top:25px;">
                <a href="profile_order.php" class="btn btn-info">
                    View Orders
                </a>
            </div>
        </form>
    </div>

    <!-- CHANGE PASSWORD MODAL -->
    <div id="passwordModal" class="modal-overlay">
        <div class="modal-box">
            <h4>Change Password</h4>

            <div id="modalMsg" class="msg error"></div>

            <form id="changePasswordForm">
                <select name="question" required>
                    <option value="">Select Security Question</option>
                    <option value="color">Favourite Color</option>
                    <option value="food">Favourite Food</option>
                    <option value="fruit">Favourite Fruit</option>
                    <option value="pet">Favourite Pet</option>
                    <option value="subject">Favourite Subject</option>
                    <option value="place">Favourite Place</option>
                    <option value="laptop">Favourite Laptop</option>
                </select>

                <input type="text" name="answer" placeholder="Security Answer" required>
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>

                <div class="modal-actions">
                    <button type="submit" class="modal-confirm">Change Password</button>
                    <button type="button" class="modal-cancel" onclick="closeModal()">Close</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/profile.js"></script>

</body>

</html>