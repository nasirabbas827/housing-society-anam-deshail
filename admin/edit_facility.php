<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM facilities WHERE id = $id");
$facility = mysqli_fetch_assoc($result);

if (!$facility) {
    die("Facility not found");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name         = $_POST['name'];
    $description  = $_POST['description'];
    $capacity     = $_POST['capacity'];
    $availability = $_POST['availability'];

    $stmt = $conn->prepare("
        UPDATE facilities
        SET name=?, description=?, capacity=?, availability=?
        WHERE id=?
    ");

    $stmt->bind_param("ssisi", $name, $description, $capacity, $availability, $id);

    if ($stmt->execute()) {
        $message = "Facility updated successfully.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Facility</title>
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

            <h3 class="text-center mb-4">Edit Facility</h3>

            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>Facility Name</label>
                        <input type="text"
                               name="name"
                               value="<?= htmlspecialchars($facility['name']) ?>"
                               class="form-control"
                               required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Capacity</label>
                        <input type="number"
                               name="capacity"
                               value="<?= htmlspecialchars($facility['capacity']) ?>"
                               class="form-control">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Description</label>
                        <textarea name="description"
                                  class="form-control"
                                  rows="3"><?= htmlspecialchars($facility['description']) ?></textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Availability</label>
                        <select name="availability" class="form-control">
                            <option value="available" <?= $facility['availability']=='available'?'selected':'' ?>>Available</option>
                            <option value="maintenance" <?= $facility['availability']=='maintenance'?'selected':'' ?>>Maintenance</option>
                        </select>
                    </div>

                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="view_facilities.php" class="btn btn-secondary">Back</a>
                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>
