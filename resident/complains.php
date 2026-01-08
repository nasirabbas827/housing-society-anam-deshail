<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'resident') {
    header("Location: ../login.php");
    exit;
}

$resident_id = $_SESSION["id"];
$message = "";

// Handle new complaint submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_complaint'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];

    $stmt = $conn->prepare("INSERT INTO complaints (resident_id, title, description, category) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $resident_id, $title, $description, $category);

    if ($stmt->execute()) {
        $message = '<div class="alert alert-success text-center">Complaint submitted successfully.</div>';
    } else {
        $message = '<div class="alert alert-danger text-center">Error submitting complaint. Try again.</div>';
    }

    $stmt->close();
}

// Fetch all complaints with assigned service provider name
$stmt = $conn->prepare("
    SELECT c.*, sp.full_name AS provider_name 
    FROM complaints c 
    LEFT JOIN users sp ON c.service_provider_id = sp.id 
    WHERE c.resident_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->bind_param("i", $resident_id);
$stmt->execute();
$result = $stmt->get_result();
$complaints = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Complaints</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">My Complaints</h2>

    <?php if($message) echo $message; ?>

    <!-- Button to trigger modal -->
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#complaintModal">Complain Now</button>

    <?php if(count($complaints) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Assigned Provider</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($complaints as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['title']); ?></td>
                            <td><?= htmlspecialchars($c['category']); ?></td>
                            <td>
                                <?php
                                $status = $c['status'];
                                $badge_class = match($status) {
                                    'open' => 'badge-warning',
                                    'assigned' => 'badge-info',
                                    'in_progress' => 'badge-primary',
                                    'resolved' => 'badge-success',
                                    'closed' => 'badge-secondary',
                                    default => 'badge-dark'
                                };
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= ucfirst($status) ?></span>
                            </td>
                            <td><?= $c['provider_name'] ?: '-'; ?></td>
                            <td><?= $c['created_at']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">You have no complaints yet.</div>
    <?php endif; ?>
</div>

<!-- Complaint Modal -->
<div class="modal fade" id="complaintModal" tabindex="-1" role="dialog" aria-labelledby="complaintModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST">
          <input type="hidden" name="add_complaint" value="1">
          <div class="modal-header">
            <h5 class="modal-title" id="complaintModalLabel">New Complaint</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
              <div class="form-row">
                  <div class="form-group col-md-12">
                      <label>Title</label>
                      <input type="text" name="title" class="form-control" required>
                  </div>
              </div>
              <div class="form-row">
                  <div class="form-group col-md-12">
                      <label>Description</label>
                      <textarea name="description" class="form-control" rows="3" required></textarea>
                  </div>
              </div>
              <div class="form-row">
                  <div class="form-group col-md-12">
                      <label>Category</label>
                      <select name="category" class="form-control" required>
                          <option value="">Select Category</option>
                          <option value="Plumbing">Plumbing</option>
                          <option value="Electrical">Electrical</option>
                          <option value="Cleaning">Cleaning</option>
                          <option value="Carpentry">Carpentry</option>
                          <option value="Painting">Painting</option>
                          <option value="Security">Security</option>
                          <option value="Gardening">Gardening</option>
                          <option value="Pest Control">Pest Control</option>
                          <option value="Water Supply">Water Supply</option>
                          <option value="Other">Other</option>
                      </select>
                  </div>
              </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Submit Complaint</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
