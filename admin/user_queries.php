<?php
session_start();
require_once("../shared/dbconnect.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

/* Current page name (used in header/sidebar highlighting) */
$currentPage = basename($_SERVER['PHP_SELF']);

/* 
   AJAX: MARK AS SEEN
*/
if (isset($_POST['mark_seen'])) {

    if ($_POST['mark_seen'] === 'all') {
        $stmt = $conn->prepare("UPDATE user_queries SET seen = 1");
        $stmt->execute();
        echo "all_updated";
        exit;
    }

    $id = (int) $_POST['id'];
    $stmt = $conn->prepare("UPDATE user_queries SET seen = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "updated";
    exit;
}

/* 
   AJAX: DELETE QUERY
*/
if (isset($_POST['delete'])) {

    if ($_POST['delete'] === 'all') {
        $stmt = $conn->prepare("DELETE FROM user_queries");
        $stmt->execute();
        echo "all_deleted";
        exit;
    }

    $id = (int) $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM user_queries WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "deleted";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Queries | Admin Panel</title>

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

        .table-header {
            background: #00796b;
            color: #6fc822ff;
        }

        .seen-badge {
            font-size: 0.8rem;
        }

        .bulk-actions {
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <?php include_once "header.php"; ?>

    <div class="admin-content">
        <h3 class="mb-3">User Queries</h3>

        <div class="bulk-actions">
            <button class="btn btn-success btn-sm" id="markAllSeen">Mark All Seen</button>
            <button class="btn btn-danger btn-sm" id="deleteAll">Delete All</button>
        </div>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Seen</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody id="queryTable">
                <?php
                $res = $conn->query("SELECT * FROM user_queries ORDER BY date DESC");

                if ($res->num_rows === 0) {
                    echo "<tr><td colspan='9' class='text-center text-muted'>No queries found</td></tr>";
                }

                while ($row = $res->fetch_assoc()) {

                    $seenBadge = $row['seen']
                        ? "<span class='badge bg-success seen-badge'>Seen</span>"
                        : "<span class='badge bg-warning text-dark seen-badge'>New</span>";

                    echo "<tr id='row{$row['id']}'>
                <td>{$row['id']}</td>
                <td>" . htmlspecialchars($row['name']) . "</td>
                <td>" . htmlspecialchars($row['email']) . "</td>
                <td>" . htmlspecialchars($row['phone']) . "</td>
                <td>" . htmlspecialchars($row['subject']) . "</td>
                <td>" . htmlspecialchars($row['message']) . "</td>
                <td>{$row['date']}</td>
                <td id='seenBadge{$row['id']}'>$seenBadge</td>
                <td>";

                    if (!$row['seen']) {
                        echo "<button class='btn btn-sm btn-success mark-seen' data-id='{$row['id']}'>Mark Seen</button> ";
                    }

                    echo "<button class='btn btn-sm btn-danger delete' data-id='{$row['id']}'>Delete</button>
                </td>
            </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        const queryTable = document.getElementById('queryTable');

        /* Mark single as seen */
        queryTable.addEventListener('click', function (e) {

            if (e.target.classList.contains('mark-seen')) {
                const id = e.target.dataset.id;

                fetch('user_queries.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'mark_seen=1&id=' + id
                })
                    .then(res => res.text())
                    .then(res => {
                        if (res.trim() === 'updated') {
                            document.getElementById('seenBadge' + id).innerHTML =
                                "<span class='badge bg-success seen-badge'>Seen</span>";
                            e.target.remove();
                        }
                    });
            }

            /* Delete single */
            if (e.target.classList.contains('delete')) {
                if (!confirm("Delete this query?")) return;

                const id = e.target.dataset.id;

                fetch('user_queries.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'delete=1&id=' + id
                })
                    .then(res => res.text())
                    .then(res => {
                        if (res.trim() === 'deleted') {
                            document.getElementById('row' + id).remove();
                        }
                    });
            }
        });

        /* Mark all seen */
        document.getElementById('markAllSeen').addEventListener('click', function () {
            if (!confirm("Mark all queries as seen?")) return;

            fetch('user_queries.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'mark_seen=all'
            })
                .then(res => res.text())
                .then(res => {
                    if (res.trim() === 'all_updated') {
                        document.querySelectorAll('.seen-badge').forEach(b => {
                            b.className = 'badge bg-success seen-badge';
                            b.textContent = 'Seen';
                        });
                        document.querySelectorAll('.mark-seen').forEach(b => b.remove());
                    }
                });
        });

        /* Delete all */
        document.getElementById('deleteAll').addEventListener('click', function () {
            if (!confirm("Delete all queries? This cannot be undone.")) return;

            fetch('user_queries.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'delete=all'
            })
                .then(res => res.text())
                .then(res => {
                    if (res.trim() === 'all_deleted') {
                        queryTable.innerHTML =
                            "<tr><td colspan='9' class='text-center text-muted'>No queries found</td></tr>";
                    }
                });
        });
    </script>

</body>

</html>