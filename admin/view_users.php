<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE id = $id");
    echo "<script>alert('User deleted successfully'); window.location='view_users.php';</script>";
    exit;
}

$result = mysqli_query($conn, "SELECT id, full_name, email, phone, role, status FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Users</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">

        <h3>All Users</h3>
        <a href="add_users.php" class="btn btn-success">Add New User</a>
 

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Status</th>
                <th width="260">Actions</th>
            </tr>
            </thead>

            <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= ucfirst($row['role']) ?></td>
                    <td><?= ucfirst($row['status']) ?></td>

                    <td>
                        <a href="view_profile.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm m-1">
                            View Profile
                        </a>

                        <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm m-1">
                            Edit
                        </a>

                        <a href="?delete=<?= $row['id'] ?>"
                           onclick="return confirm('Are you sure you want to delete this user?');"
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
