<?php
require('../shared/commonlinks.php');
require('../shared/dbconnect.php');

// Toggle verification via GET
if (isset($_GET['toggle'])) {
    $userId = intval($_GET['toggle']);
    $res = $conn->query("SELECT is_verified FROM user_creden WHERE id=$userId");
    if ($res->num_rows) {
        $row = $res->fetch_assoc();
        $newStatus = $row['is_verified'] ? 0 : 1;
        $conn->query("UPDATE user_creden SET is_verified=$newStatus WHERE id=$userId");
        $msg = $newStatus ? "User verified successfully!" : "User unverified successfully!";
        header("Location: users.php?msg=" . urlencode($msg) . "&status=$newStatus");
        exit;
    }
}

// Delete user via GET
if (isset($_GET['delete'])) {
    $userId = intval($_GET['delete']);
    $conn->query("DELETE FROM user_creden WHERE id=$userId");
    $msg = "User deleted successfully!";
    header("Location: users.php?msg=" . urlencode($msg) . "&status=2");
    exit;
}

// Fetch all users
$res = $conn->query("SELECT * FROM user_creden ORDER BY id DESC");
$users = $res->num_rows ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Panel - Users</title>
<link rel="stylesheet" href="css/header.css">
<style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .admin-content h3 {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 20px;
        color: #333;
    }
    .table-wrapper { overflow-x: auto; }
    .btn-small { padding: 3px 10px; font-size: 14px; }
    #notifyMsg {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 2000;
        padding: 10px 20px;
        border-radius: 5px;
        display: none;
        color: #fff;
    }
</style>
</head>
<body style="background-color:#f2f2f2;">

<?php require('header.php'); ?>

<main class="admin-content">
    <h3 class="mb-4">Users Management</h3>

    <!-- Search box -->
    <div class="mb-3">
        <input type="text" class="form-control" id="searchBox" placeholder="Search users...">
    </div>

    <!-- Users table -->
    <div class="table-wrapper">
        <table class="table table-bordered table-striped table-hover" id="usersTable">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Security Question</th>
                    <th>Security Answer</th>
                    <th>Verified</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($users as $u) {
                    $btnClass = $u['is_verified'] ? 'btn-success' : 'btn-warning';
                    $btnText = $u['is_verified'] ? 'Verified' : 'Unverified';
                    echo "<tr>
                        <td>{$u['id']}</td>
                        <td>".htmlspecialchars($u['name'])."</td>
                        <td>".htmlspecialchars($u['username'])."</td>
                        <td>".htmlspecialchars($u['email'])."</td>
                        <td>".htmlspecialchars($u['phone'])."</td>
                        <td>".htmlspecialchars($u['security_question'])."</td>
                        <td>".htmlspecialchars($u['security_answer'])."</td>
                        <td>
                            <a href='?toggle={$u['id']}' class='btn btn-sm btn-small $btnClass'>
                                $btnText
                            </a>
                        </td>
                        <td>{$u['created_at']}</td>
                        <td>";
                            if ($u['is_verified']) {
                                echo "<a href='?delete={$u['id']}' class='btn btn-sm btn-danger btn-small'
                                    onclick=\"return confirm('Are you sure you want to delete this user?');\">
                                    Delete
                                </a>";
                            } else {
                                echo "<span class='text-muted'></span>";
                            }
                        echo "</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Notification message -->
<?php if (isset($_GET['msg'])): ?>
    <div id="notifyMsg" style="background-color:<?= $_GET['status']==1 ? '#28a745' : ($_GET['status']==0 ? '#ffc107' : '#dc3545') ?>">
        <?= htmlspecialchars($_GET['msg']) ?>
    </div>
<?php endif; ?>

<script>
    // Simple search
    const searchBox = document.getElementById('searchBox');
    const tbody = document.getElementById('usersTable').getElementsByTagName('tbody')[0];
    searchBox?.addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        for (let row of tbody.rows) {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
        }
    });

    // Auto-hide notification & remove query params
    const notify = document.getElementById('notifyMsg');
    if (notify) {
        notify.style.display = 'block';
        setTimeout(() => { notify.style.display = 'none'; }, 2500);

        const url = new URL(window.location);
        url.searchParams.delete('msg');
        url.searchParams.delete('status');
        window.history.replaceState({}, document.title, url.pathname);
    }
</script>

</body>
</html>