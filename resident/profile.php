<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];
$message = "";

// Fetch current user data
$result = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($result);
if (!$user) die("User not found");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $password = $_POST['password'];
    
    $house_no = ($user['role'] === 'resident') ? $_POST['house_no'] : null;
    $block = ($user['role'] === 'resident') ? $_POST['block'] : null;
    $service_type = ($user['role'] === 'service_provider') ? $_POST['service_type'] : null;
    $experience_years = ($user['role'] === 'service_provider') ? $_POST['experience_years'] : null;

    // Handle profile picture upload
    $profile_picture = $user['profile_picture']; // keep existing
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $filename = 'uploads/profile_' . $user_id . '.' . $ext;
        if (!is_dir('uploads')) mkdir('uploads', 0755);
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $filename)) {
            $profile_picture = $filename;
        }
    }

    // Prepare password update if provided
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            UPDATE users SET full_name=?, phone=?, address=?, password=?, profile_picture=?, 
                             house_no=?, block=?, service_type=?, experience_years=? 
            WHERE id=?
        ");
        $stmt->bind_param("sssssssssi", $full_name, $phone, $address, $password_hash, $profile_picture,
                          $house_no, $block, $service_type, $experience_years, $user_id);
    } else {
        $stmt = $conn->prepare("
            UPDATE users SET full_name=?, phone=?, address=?, profile_picture=?, 
                             house_no=?, block=?, service_type=?, experience_years=? 
            WHERE id=?
        ");
        $stmt->bind_param("ssssssssi", $full_name, $phone, $address, $profile_picture,
                          $house_no, $block, $service_type, $experience_years, $user_id);
    }

    if ($stmt->execute()) {
        $message = '<div class="alert alert-success text-center">Profile updated successfully.</div>';
        $_SESSION['full_name'] = $full_name;
        $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));
    } else {
        $message = '<div class="alert alert-danger text-center">Error updating profile. Try again.</div>';
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .profile-pic {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto shadow-sm" style="max-width: 700px;">
        <div class="card-body">
            <div class="text-center">
                <img src="<?= $user['profile_picture'] ?: '../images/default-profile.png' ?>" alt="Profile Picture" class="profile-pic">
                <h3 class="mb-4"><?= htmlspecialchars($user['full_name']); ?></h3>
            </div>

            <?= $message; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Email (readonly)</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Password (leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Address</label>
                        <textarea name="address" class="form-control"><?= htmlspecialchars($user['address']); ?></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Profile Picture</label>
                        <input type="file" name="profile_picture" class="form-control-file">
                    </div>
                </div>

                <?php if ($user['role'] === 'resident'): ?>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>House No</label>
                            <input type="text" name="house_no" class="form-control" value="<?= htmlspecialchars($user['house_no']); ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Block</label>
                            <input type="text" name="block" class="form-control" value="<?= htmlspecialchars($user['block']); ?>">
                        </div>
                    </div>
                <?php elseif ($user['role'] === 'service_provider'): ?>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Service Type</label>
                            <input type="text" name="service_type" class="form-control" value="<?= htmlspecialchars($user['service_type']); ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Experience (years)</label>
                            <input type="number" name="experience_years" class="form-control" value="<?= htmlspecialchars($user['experience_years']); ?>">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-row mt-3">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-primary px-4">Update Profile</button>
                        <a href="dashboard.php" class="btn btn-secondary px-4">Back</a>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

</body>
</html>
