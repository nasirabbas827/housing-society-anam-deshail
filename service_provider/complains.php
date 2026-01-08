<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'service_provider') {
    header("Location: ../login.php");
    exit;
}

$provider_id = $_SESSION["id"];
$message = "";

// Handle status update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_status'])) {
    $complaint_id = $_POST['complaint_id'];
    $status = $_POST['status'];

    // Optional: Add resolved_at timestamp when status becomes 'resolved'
    if ($status === 'resolved') {
        $stmt = $conn->prepare("UPDATE complaints SET status=?, resolved_at=NOW() WHERE id=? AND service_provider_id=?");
        $stmt->bind_param("sii", $status, $complaint_id, $provider_id);
    } else {
        $stmt = $conn->prepare("UPDATE complaints SET status=? WHERE id=? AND service_provider_id=?");
        $stmt->bind_param("sii", $status, $complaint_id, $provider_id);
    }

    if ($stmt->execute()) {
        $message = '<div class="alert alert-success text-center">Status updated successfully.</div>';
    } else {
        $message = '<div class="alert alert-danger text-center">Error updating status. Try again.</div>';
    }
    $stmt->close();
}

// Fetch all complaints assigned to this provider
$stmt = $conn->prepare("
    SELECT c.*, u.full_name AS resident_name
    FROM complaints c
    JOIN users u ON c.resident_id = u.id
    WHERE c.service_provider_id = ?
    ORDER BY c.created_at DESC
");
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$result = $stmt->get_result();
$complaints = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Assigned Complaints</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">My Assigned Complaints</h2>

    <?php if($message) echo $message; ?>

    <?php if(count($complaints) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Resident</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($complaints as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['resident_name']); ?></td>
                            <td><?= htmlspecialchars($c['title']); ?></td>
                            <td><?= htmlspecialchars($c['category']); ?></td>
                            <td><?= nl2br(htmlspecialchars($c['description'])); ?></td>
                            <td>
                                <?php
                                $status = $c['status'] ?? 'open';
                                $badge_class = match($status) {
                                    'open' => 'badge-warning',
                                    'assigned' => 'badge-info',
                                    'in_progress' => 'badge-primary',
                                    'resolved' => 'badge-success',
                                    'closed' => 'badge-secondary',
                                    default => 'badge-dark'
                                };
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= ucfirst(str_replace('_', ' ', $status)) ?></span>
                            </td>
                            <td><?= $c['created_at']; ?></td>
                            <td>
                                <?php if (in_array($status, ['assigned', 'in_progress', 'resolved'])): ?>
                                    <button class="btn btn-sm btn-primary update-status-btn"
                                            data-id="<?= $c['id'] ?>"
                                            data-status="<?= $c['status'] ?>">
                                        Update Status
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">No complaints assigned to you yet.</div>
    <?php endif; ?>
</div>

<!-- Shared Update Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="update_status" value="1">
                <input type="hidden" name="complaint_id" id="modalComplaintId">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Update Complaint Status</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="modalStatus">Status</label>
                        <select name="status" id="modalStatus" class="form-control" required>
                            <option value="assigned">Assigned</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts: Full jQuery + Bootstrap Bundle -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Populate modal with complaint ID and current status when button is clicked
$(document).on('click', '.update-status-btn', function() {
    var complaintId = $(this).data('id');
    var currentStatus = $(this).data('status');

    $('#modalComplaintId').val(complaintId);
    $('#modalStatus').val(currentStatus);

    $('#statusModal').modal('show');
});
</script>

</body>
</html>