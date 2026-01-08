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

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = $_POST['full_name'];
    $phone     = $_POST['phone'];
    $role      = $_POST['role'];
    $status    = $_POST['status'];
    $password  = trim($_POST['password']);

    if (!empty($password)) {
        // Update WITH password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            UPDATE users 
            SET full_name=?, phone=?, role=?, status=?, password=?
            WHERE id=?
        ");
        $stmt->bind_param(
            "sssssi",
            $full_name,
            $phone,
            $role,
            $status,
            $hashed_password,
            $id
        );
    } else {
        // Update WITHOUT password
        $stmt = $conn->prepare("
            UPDATE users 
            SET full_name=?, phone=?, role=?, status=?
            WHERE id=?
        ");
        $stmt->bind_param(
            "ssssi",
            $full_name,
            $phone,
            $role,
            $status,
            $id
        );
    }

    if ($stmt->execute()) {
        $message = "User updated successfully.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
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

            <h3 class="text-center mb-4">Edit User</h3>

            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>Full Name</label>
                        <input type="text"
                               name="full_name"
                               value="<?= htmlspecialchars($user['full_name']) ?>"
                               class="form-control"
                               required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Phone</label>
                        <input type="text"
                               name="phone"
                               value="<?= htmlspecialchars($user['phone']) ?>"
                               class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Role</label>
                        <select name="role" class="form-control">
                            <option value="resident" <?= $user['role']=='resident'?'selected':'' ?>>Resident</option>
                            <option value="service_provider" <?= $user['role']=='service_provider'?'selected':'' ?>>Service Provider</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="pending" <?= $user['status']=='pending'?'selected':'' ?>>Pending</option>
                            <option value="active" <?= $user['status']=='active'?'selected':'' ?>>Active</option>
                            <option value="inactive" <?= $user['status']=='inactive'?'selected':'' ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>New Password (leave blank to keep current)</label>
                        <input type="password"
                               name="password"
                               class="form-control"
                               placeholder="Leave empty if not changing">
                    </div>

                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="view_users.php" class="btn btn-secondary">Back</a>
                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>
