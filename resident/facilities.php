<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'resident') {
    header("Location: ../login.php");
    exit;
}

$resident_id = $_SESSION["id"];
$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Fetch facilities
$sql = "SELECT * FROM facilities WHERE name LIKE ? ORDER BY name ASC";
$stmt = $conn->prepare($sql);
$like_search = "%$search%";
$stmt->bind_param("s", $like_search);
$stmt->execute();
$result = $stmt->get_result();
$facilities = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle booking submission
$booking_msg = '';
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['facility_id'])) {
    $facility_id = $_POST['facility_id'];
    $booking_date = $_POST['booking_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $stmt = $conn->prepare("INSERT INTO facility_bookings (facility_id, resident_id, booking_date, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $facility_id, $resident_id, $booking_date, $start_time, $end_time);

    if ($stmt->execute()) {
        $booking_msg = '<div class="alert alert-success text-center">Booking request submitted successfully!</div>';
    } else {
        $booking_msg = '<div class="alert alert-danger text-center">Error: Could not submit booking.</div>';
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Facilities</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">Available Facilities</h2>

    <?php echo $booking_msg; ?>

    <form class="form-inline mb-3" method="get">
        <input type="text" name="search" class="form-control mr-2" placeholder="Search by name" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <div class="row">
        <?php if (count($facilities) > 0): ?>
            <?php foreach ($facilities as $facility): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($facility['name']); ?></h5>
                            <p class="card-text"><?= htmlspecialchars($facility['description']); ?></p>
                            <p><strong>Capacity:</strong> <?= $facility['capacity'] ?: 'N/A'; ?></p>
                            <p><strong>Status:</strong> <?= ucfirst($facility['availability']); ?></p>
                            <?php if ($facility['availability'] === 'available'): ?>
                                <button class="btn btn-success btn-block" data-toggle="modal" data-target="#bookModal" data-id="<?= $facility['id'] ?>" data-name="<?= htmlspecialchars($facility['name']) ?>">Book Now</button>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-block" disabled>Unavailable</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">No facilities found.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Booking Modal -->
<div class="modal fade" id="bookModal" tabindex="-1" role="dialog" aria-labelledby="bookModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" id="bookingForm">
            <input type="hidden" name="facility_id" id="facility_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookModalLabel">Book Facility</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                    
                <div class="modal-body">
                    <div class="form-group">
                        <label>Facility Name</label>
                        <input type="text" class="form-control" id="facility_name" readonly>
                    </div>
                    <div class="form-group">
                        <label>Booking Date</label>
                        <input type="date" name="booking_date" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Start Time</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>End Time</label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                </div>
                    
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit Booking</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $('#bookModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var facilityId = button.data('id');
        var facilityName = button.data('name');
        var modal = $(this);
        modal.find('#facility_id').val(facilityId);
        modal.find('#facility_name').val(facilityName);
    });
</script>

</body>
</html>
