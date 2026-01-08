<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("User not found");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Profile</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">

    <div class="card mx-auto" style="max-width: 700px;">
        <div class="card-body">

            <h3 class="text-center mb-4">User Profile</h3>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <strong>Full Name:</strong><br>
                    <?= htmlspecialchars($user['full_name']) ?>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Email:</strong><br>
                    <?= htmlspecialchars($user['email']) ?>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Phone:</strong><br>
                    <?= htmlspecialchars($user['phone']) ?>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Role:</strong><br>
                    <?= ucfirst($user['role']) ?>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Status:</strong><br>
                    <?= ucfirst($user['status']) ?>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>House No:</strong><br>
                    <?= $user['house_no'] ?: 'N/A' ?>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Block:</strong><br>
                    <?= $user['block'] ?: 'N/A' ?>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Service Type:</strong><br>
                    <?= $user['service_type'] ?: 'N/A' ?>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Experience (Years):</strong><br>
                    <?= $user['experience_years'] ?: 'N/A' ?>
                </div>

                <div class="col-12 mb-3">
                    <strong>Address:</strong><br>
                    <?= $user['address'] ?: 'N/A' ?>
                </div>

            </div>

            <div class="text-center">
                <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-primary">Edit</a>
                <a href="view_users.php" class="btn btn-secondary">Back</a>
            </div>

        </div>
    </div>

</div>

</body>
</html>
