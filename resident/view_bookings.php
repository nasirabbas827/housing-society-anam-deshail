<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'resident') {
    header("Location: ../login.php");
    exit;
}

$resident_id = $_SESSION["id"];

// Fetch resident bookings with facility names
$sql = "SELECT fb.*, f.name AS facility_name 
        FROM facility_bookings fb
        JOIN facilities f ON fb.facility_id = f.id
        WHERE fb.resident_id = ?
        ORDER BY fb.booking_date DESC, fb.start_time ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $resident_id);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Facility Bookings</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">My Facility Bookings</h2>

    <?php if(count($bookings) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Facility</th>
                        <th>Booking Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Status</th>
                        <th>Reason (if any)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($bookings as $b): ?>
                        <tr>
                            <td><?= htmlspecialchars($b['facility_name']); ?></td>
                            <td><?= $b['booking_date']; ?></td>
                            <td><?= $b['start_time']; ?></td>
                            <td><?= $b['end_time']; ?></td>
                            <td>
                                <?php
                                    $status = $b['status'];
                                    $badge_class = match($status) {
                                        'pending' => 'badge-warning',
                                        'approved' => 'badge-success',
                                        'rejected' => 'badge-danger',
                                        'completed' => 'badge-primary',
                                        default => 'badge-secondary'
                                    };
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= ucfirst($status) ?></span>
                            </td>
                            <td><?= htmlspecialchars($b['reason'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">You have no bookings yet.</div>
    <?php endif; ?>
</div>

</body>
</html>
