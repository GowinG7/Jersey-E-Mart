<?php
session_start();
require_once "../shared/dbconnect.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: loginsignup/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = $error = "";

//UPDATE PROFILE 
if (isset($_POST['update_profile'])) {

    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $security_question = trim($_POST['security_question']);
    $security_answer = trim($_POST['security_answer']);

    if ($name && $username && $email && $phone && $security_question && $security_answer) {

        $stmt = $conn->prepare("
            UPDATE user_creden 
            SET name=?, username=?, email=?, phone=?, security_question=?, security_answer=?
            WHERE id=?
        ");
        $stmt->bind_param(
            "ssssssi",
            $name,
            $username,
            $email,
            $phone,
            $security_question,
            $security_answer,
            $user_id
        );

        if ($stmt->execute()) {
            // Use session messages and redirect to avoid form resubmission on refresh (PRG pattern)
            $_SESSION['profile_success'] = "Profile updated successfully.";
            $_SESSION['username'] = $username;
            $stmt->close();
            header("Location: profile.php");
            exit();
        } else {
            $_SESSION['profile_error'] = "Failed to update profile.";
            $stmt->close();
            header("Location: profile.php");
            exit();
        }
    } else {
        $_SESSION['profile_error'] = "All fields are required.";
        header("Location: profile.php");
        exit();
    }
}

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

// Consume any flash messages set during POST (Post-Redirect-Get)
if (isset($_SESSION['profile_success'])) {
    $success = $_SESSION['profile_success'];
    unset($_SESSION['profile_success']);
}
if (isset($_SESSION['profile_error'])) {
    $error = $_SESSION['profile_error'];
    unset($_SESSION['profile_error']);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>User Profile</title>
    <style>
        body {
            background: #e0f4f2;
            font-family: 'Segoe UI', sans-serif;
            color: #2d5d58;
        }

        .profile-card {
            max-width: 650px;
            margin: 50px auto;
            background: linear-gradient(145deg, #ffffff, #cdeeea);
            padding: 30px;
            border-radius: 16px;
        }

        h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #1c6059;
        }

        .row {
            display: flex;
            margin-bottom: 14px;
            align-items: center;
        }

        .label {
            width: 40%;
            font-weight: 600;
        }

        .value {
            width: 60%;
        }

        input,
        select {
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #a9d6d1;
        }

        .status {
            font-weight: 600;
            color:
                <?= $user['is_verified'] ? '#1a7f37' : '#b42318' ?>
            ;
        }

        .msg {
            text-align: center;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .success {
            color: #1a7f37;
        }

        .error {
            color: #b42318;
        }

        /* Normalize button appearance and provide polished styles */
        button {
            -webkit-appearance: none;
            appearance: none;
            border: none;
            background: #1c6059;
            color: white;
            padding: 10px 25px;
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.15s ease, color 0.15s ease, transform 0.06s ease;
        }

        button:hover {
            background: #317770ff;
            color: white;
        }

        /* Specific style for the Change Password button */
        .change-password {
            background: #c0392b;
            border-radius: 20px; /* larger radius for emphasis */
            padding: 10px 22px;
            color: #fff;
        }

        .change-password:hover { filter: brightness(0.95); color: #fff; }

        /* Specific style for the Update Profile button */
        .update-profile {
            background: #1c6059; /* site primary */
            border-radius: 20px;
            padding: 10px 26px;
            color: #fff;
            font-weight: 600;
        }

        .update-profile:hover { filter: brightness(0.95); color: #fff; }

        /*MODAL*/
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .55);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal-box {
            background: #fff;
            padding: 25px;
            border-radius: 14px;
            width: 360px;
        }

        .modal-box h4 {
            text-align: center;
            margin-bottom: 15px;
            color: #1c6059;
        }

        .modal-box input,
        .modal-box select {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
        }

        /* Modal action buttons (side-by-side on larger screens) */
        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .modal-actions .modal-confirm,
        .modal-actions .modal-cancel {
            flex: 1;
            padding: 10px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.12s ease, transform 0.08s ease, box-shadow 0.12s ease, color 0.12s ease;
        }

        /* Primary confirm button */
        .modal-actions .modal-confirm {
            background: #1c6059; /* base */
            color: #fff;
        }

        .modal-actions .modal-confirm:hover {
            background: #14543b; /* darker on hover */
            box-shadow: 0 8px 18px rgba(20,84,59,0.18);
            transform: translateY(-2px);
        }
        .modal-actions .modal-confirm:focus {
            outline: 3px solid rgba(20,84,59,0.14);
            outline-offset: 2px;
        }
        .modal-actions .modal-confirm:active { background: #0f4234; transform: translateY(0); }

        /* Secondary cancel button */
        .modal-actions .modal-cancel {
            background: #ffffff; /* base */
            color: #1c6059;
            border: 1px solid #cdeeea;
        }

        .modal-actions .modal-cancel:hover {
            background: #eef7f5; /* soft pale green */
            color: #123f38;
            box-shadow: 0 8px 18px rgba(20,84,59,0.06);
            transform: translateY(-2px);
        }
        .modal-actions .modal-cancel:focus {
            outline: 3px solid rgba(20,84,59,0.06);
            outline-offset: 2px;
        }
        .modal-actions .modal-cancel:active { background: #e6f3f1; transform: translateY(0); }

        @media (max-width: 480px) {
            .modal-actions { flex-direction: column; }
        }
    </style>
</head>

<body>

    <?php include_once 'header.php'; ?>

    <div class="profile-card">
        <h3>User Profile</h3>

        <?php if ($success): ?>
            <div class="msg success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?>
            <div class="msg error"><?= $error ?></div><?php endif; ?>

        <form method="POST" id="profileForm">

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
                <button type="submit" name="update_profile" class="update-profile">Update Profile</button>
            </div>

            <div style="text-align:center;margin-top:25px;">
                <button type="button" onclick="openModal()" class="change-password">
                    Change Password
                </button>
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

    <?php include_once 'footer.php'; ?>

    <script>
        function openModal() {
            document.getElementById("passwordModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("passwordModal").style.display = "none";
        }

        // Helper to show messages in the password modal. Success messages auto-dismiss after 4s.
        function showModalMsg(type, text) {
            const el = document.getElementById("modalMsg");
            // Reset styles
            el.style.transition = '';
            el.style.opacity = '';
            el.style.maxHeight = '';
            el.style.margin = '';

            el.className = 'msg ' + (type === 'success' ? 'success' : 'error');
            el.innerText = text;

            if (type === 'success') {
                // Auto-dismiss after 4 seconds with a short fade/collapse
                setTimeout(() => {
                    el.style.transition = 'opacity 0.45s ease, max-height 0.45s ease, margin 0.45s ease';
                    el.style.opacity = '0';
                    el.style.maxHeight = '0';
                    el.style.margin = '0';
                    setTimeout(() => {
                        el.className = 'msg';
                        el.innerText = '';
                        el.style.transition = '';
                        el.style.opacity = '';
                        el.style.maxHeight = '';
                        el.style.margin = '';
                    }, 500);
                }, 4000);
            }
        }

        document.getElementById("changePasswordForm").addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch("change_password_modal.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.text())
                .then(msg => {
                    if (msg.trim() === "success") {
                        showModalMsg('success', 'Password changed successfully.');
                        this.reset();
                    } else {
                        showModalMsg('error', msg);
                    }
                });
        });

        // Auto-dismiss profile update success messages after 4 seconds
        (function () {
            try {
                const profileSuccessMsgs = document.querySelectorAll('.profile-card .msg.success');
                profileSuccessMsgs.forEach(el => {
                    if (el.textContent.trim()) {
                        setTimeout(() => {
                            el.style.transition = 'opacity 0.45s ease, max-height 0.45s ease, margin 0.45s ease';
                            el.style.opacity = '0';
                            el.style.maxHeight = '0';
                            el.style.margin = '0';
                            setTimeout(() => el.remove(), 500);
                        }, 4000);
                    }
                });
            } catch (e) {
                // fail silently
                console.warn('Auto-dismiss script error', e);
            }
        })();

        // Confirm before profile update
        (function () {
            const profileForm = document.getElementById('profileForm');
            if (profileForm) {
                profileForm.addEventListener('submit', function (e) {
                    const confirmed = confirm('Are you sure you want to change your profile details? This will change your account information.');
                    if (!confirmed) {
                        e.preventDefault();
                    }
                });
            }
        })();
    </script>

</body>

</html>