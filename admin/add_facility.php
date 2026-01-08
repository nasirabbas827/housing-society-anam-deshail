<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name         = $_POST['name'];
    $description  = $_POST['description'];
    $capacity     = $_POST['capacity'];
    $availability = $_POST['availability'];

    $stmt = $conn->prepare("
        INSERT INTO facilities (name, description, capacity, availability)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("ssis", $name, $description, $capacity, $availability);

    if ($stmt->execute()) {
        $message = "Facility added successfully.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Facility</title>
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

            <h3 class="text-center mb-4">Add Facility</h3>

            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>Facility Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Capacity</label>
                        <input type="number" name="capacity" class="form-control">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Availability</label>
                        <select name="availability" class="form-control">
                            <option value="available">Available</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>

                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Add Facility</button>
                    <a href="view_facilities.php" class="btn btn-secondary">View Facilities</a>
                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>
