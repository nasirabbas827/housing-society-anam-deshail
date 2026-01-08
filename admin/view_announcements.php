<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM announcements WHERE id = $id");
    echo "<script>alert('Announcement deleted successfully'); window.location='view_announcements.php';</script>";
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM announcements ORDER BY posted_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Announcements</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">

    <div >
        <h3>Announcements</h3>
        <a href="add_announcement.php" class="btn btn-success">Add Announcement</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">

            <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Posted Date</th>
                <th width="220">Actions</th>
            </tr>
            </thead>

            <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= $row['posted_date'] ?></td>
                    <td>
                        <a href="edit_announcement.php?id=<?= $row['id'] ?>"
                           class="btn btn-primary btn-sm m-1">
                            Edit
                        </a>
                        <a href="?delete=<?= $row['id'] ?>"
                           onclick="return confirm('Delete this announcement?');"
                           class="btn btn-danger btn-sm m-1">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>

        </table>
    </div>

</div>

</body>
</html>
