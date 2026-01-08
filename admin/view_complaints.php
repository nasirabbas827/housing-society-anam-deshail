<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle assignment/update via POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_complaint'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $status = $_POST['status'];
    $service_provider_id = !empty($_POST['service_provider_id']) ? intval($_POST['service_provider_id']) : null;

    $stmt = $conn->prepare("UPDATE complaints SET status=?, service_provider_id=? WHERE id=?");
    $stmt->bind_param("sii", $status, $service_provider_id, $complaint_id);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Complaint updated successfully'); window.location='view_complaints.php';</script>";
    exit;
}

// Fetch all complaints with resident and service provider info
$query = "
    SELECT c.*, r.full_name AS resident_name, sp.full_name AS provider_name
    FROM complaints c
    JOIN users r ON c.resident_id = r.id
    LEFT JOIN users sp ON c.service_provider_id = sp.id
    ORDER BY c.created_at DESC
";
$result = mysqli_query($conn, $query);

// Fetch all service providers for assignment dropdown
$providers_result = mysqli_query($conn, "SELECT id, full_name FROM users WHERE role='service_provider' AND status='active'");
$providers = [];
while ($p = mysqli_fetch_assoc($providers_result)) {
    $providers[] = $p;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Complaints</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h3>All Complaints</h3>

    <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Resident</th>
                    <th>Service Provider</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['resident_name']) ?></td>
                        <td><?= htmlspecialchars($row['provider_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td><?= ucfirst($row['status']) ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#updateModal<?= $row['id'] ?>">Assign / Update</button>
                        </td>
                    </tr>

                    <!-- Update Modal -->
                    <div class="modal fade" id="updateModal<?= $row['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel<?= $row['id'] ?>" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <form method="POST">
                              <input type="hidden" name="update_complaint" value="1">
                              <input type="hidden" name="complaint_id" value="<?= $row['id'] ?>">
                              <div class="modal-header">
                                <h5 class="modal-title" id="updateModalLabel<?= $row['id'] ?>">Update Complaint #<?= $row['id'] ?></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                  <div class="form-row">
                                      <div class="form-group col-md-6">
                                          <label>Status</label>
                                          <select name="status" class="form-control" required>
                                              <option value="open" <?= $row['status']=='open'?'selected':'' ?>>Open</option>
                                              <option value="assigned" <?= $row['status']=='assigned'?'selected':'' ?>>Assigned</option>
                                              <option value="in_progress" <?= $row['status']=='in_progress'?'selected':'' ?>>In Progress</option>
                                              <option value="resolved" <?= $row['status']=='resolved'?'selected':'' ?>>Resolved</option>
                                              <option value="closed" <?= $row['status']=='closed'?'selected':'' ?>>Closed</option>
                                          </select>
                                      </div>
                                      <div class="form-group col-md-6">
                                          <label>Service Provider</label>
                                          <select name="service_provider_id" class="form-control">
                                              <option value="">-- Select Provider --</option>
                                              <?php foreach($providers as $p): ?>
                                                  <option value="<?= $p['id'] ?>" <?= $row['service_provider_id']==$p['id']?'selected':'' ?>>
                                                      <?= htmlspecialchars($p['full_name']) ?>
                                                  </option>
                                              <?php endforeach; ?>
                                          </select>
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
