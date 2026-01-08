<?php
include('config.php');

$full_name = $email = $password = $phone = $role = $address = "";
$full_name_err = $email_err = $password_err = "";
$registration_msg = "";

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate Full Name
    if (empty(trim($_POST["full_name"]))) {
        $full_name_err = "Please enter your full name.";
    } else {
        $full_name = trim($_POST["full_name"]);
    }

    // Validate Email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $email_err = "This email is already registered.";
        }
        mysqli_stmt_close($stmt);
    }

    // Validate Password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 3) {
        $password_err = "Password must be at least 3 characters long.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Optional fields
    $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : NULL;
    $role = !empty($_POST['role']) ? $_POST['role'] : 'resident';
    $address = !empty($_POST['address']) ? trim($_POST['address']) : NULL;

    // Insert if no errors
    if (empty($full_name_err) && empty($email_err) && empty($password_err)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (full_name, email, password, phone, role, address)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssss", 
            $full_name, $email, $hashed_password, $phone, $role, $address
        );

        if (mysqli_stmt_execute($stmt)) {
            $registration_msg = '<div class="alert alert-success text-center">Registration successful! Your account is pending approval.</div>';
            // Clear form fields
            $full_name = $email = $password = $phone = $role = $address = "";
        } else {
            $registration_msg = '<div class="alert alert-danger text-center">Error: Unable to register. Try again.</div>';
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto shadow-sm" style="max-width: 700px;">
        <div class="card-body">
            <h3 class="text-center mb-4">User Registration</h3>
            <?php echo $registration_msg; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control <?php echo (!empty($full_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $full_name; ?>">
                        <span class="invalid-feedback"><?php echo $full_name_err; ?></span>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                        <span class="invalid-feedback"><?php echo $email_err; ?></span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo $phone; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Role</label>
                        <select name="role" class="form-control">
                            <option value="resident" <?php echo ($role=='resident') ? 'selected' : ''; ?>>Resident</option>
                            <option value="service_provider" <?php echo ($role=='service_provider') ? 'selected' : ''; ?>>Service Provider</option>
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Address</label>
                        <textarea name="address" class="form-control" rows="1"><?php echo $address; ?></textarea>
                    </div>
                </div>

                <div class="form-group text-center mt-4">
                    <input type="submit" class="btn btn-primary px-4" value="Register">
                </div>

            </form>
        </div>
    </div>
</div>

</body>
</html>
