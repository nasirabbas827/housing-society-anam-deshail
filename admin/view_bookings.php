<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle status update via POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_booking'])) {
    $booking_id = intval($_POST['booking_id']);
    $status = $_POST['status'];
    $reason = $_POST['reason'];

    $stmt = $conn->prepare("UPDATE facility_bookings SET status=?, reason=? WHERE id=?");
    $stmt->bind_param("ssi", $status, $reason, $booking_id);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Booking updated successfully'); window.location='view_bookings.php';</script>";
    exit;
}

// Fetch all bookings with facility and resident details
$query = "
    SELECT fb.*, f.name AS facility_name, r.full_name AS resident_name
    FROM facility_bookings fb
    JOIN facilities f ON fb.facility_id = f.id
    JOIN users r ON fb.resident_id = r.id
    ORDER BY fb.created_at DESC
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Facility Bookings</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">

    <h3>All Facility Bookings</h3>

    <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Facility</th>
                <th>Resident</th>
                <th>Booking Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Status</th>
                <th>Reason</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            </thead>

            <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['facility_name']) ?></td>
                    <td><?= htmlspecialchars($row['resident_name']) ?></td>
                    <td><?= $row['booking_date'] ?></td>
                    <td><?= $row['start_time'] ?></td>
                    <td><?= $row['end_time'] ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['reason'] ?: '-') ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm m-1" data-toggle="modal"
                                data-target="#updateModal<?= $row['id'] ?>">Update
                        </button>
                    </td>
                </tr>

                <!-- Update Modal -->
                <div class="modal fade" id="updateModal<?= $row['id'] ?>" tabindex="-1" role="dialog"
                     aria-labelledby="updateModalLabel<?= $row['id'] ?>" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <form method="POST">
                          <input type="hidden" name="update_booking" value="1">
                          <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                          <div class="modal-header">
                            <h5 class="modal-title" id="updateModalLabel<?= $row['id'] ?>">Update Booking #<?= $row['id'] ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                              <div class="form-row">
                                  <div class="form-group col-md-6">
                                      <label>Status</label>
                                      <select name="status" class="form-control" required>
                                          <option value="pending" <?= $row['status']=='pending'?'selected':'' ?>>Pending</option>
                                          <option value="approved" <?= $row['status']=='approved'?'selected':'' ?>>Approved</option>
                                          <option value="rejected" <?= $row['status']=='rejected'?'selected':'' ?>>Rejected</option>
                                          <option value="completed" <?= $row['status']=='completed'?'selected':'' ?>>Completed</option>
                                      </select>
                                  </div>
                                  <div class="form-group col-md-6">
                                      <label>Reason</label>
                                      <input type="text" name="reason" class="form-control" value="<?= htmlspecialchars($row['reason']) ?>">
                                  </div>
                              </div>
                          </div>
                          <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Save Changes</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                          </div>
                      </form>
                    </div>
                  </div>
                </div>
            <?php endwhile; ?>
            </tbody>

        </table>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
