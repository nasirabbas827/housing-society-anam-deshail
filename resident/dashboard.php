<?php
session_start();

// Authentication Check - Only allow resident
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "resident") {
    header("Location: ../login.php");
    exit;
}

include('../config.php');  

$resident_id = $_SESSION["id"];  
$full_name = $_SESSION["full_name"];
$email = $_SESSION["email"];
$house_no = $_SESSION["house_no"] ?? '';
$block = $_SESSION["block"] ?? '';

// ==================== FETCH STATISTICS FOR RESIDENT ====================

// My Facility Bookings
$total_bookings = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as count 
    FROM facility_bookings 
    WHERE resident_id = '$resident_id'
"))['count'];

$upcoming_bookings = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as count 
    FROM facility_bookings 
    WHERE resident_id = '$resident_id' AND booking_date >= CURDATE() AND status IN ('pending', 'approved')
"))['count'];

$approved_bookings = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as count 
    FROM facility_bookings 
    WHERE resident_id = '$resident_id' AND status = 'approved'
"))['count'];

// My Complaints
$total_complaints = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as count 
    FROM complaints 
    WHERE resident_id = '$resident_id'
"))['count'];

$open_complaints = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as count 
    FROM complaints 
    WHERE resident_id = '$resident_id' AND status NOT IN ('resolved', 'closed')
"))['count'];

$resolved_complaints = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as count 
    FROM complaints 
    WHERE resident_id = '$resident_id' AND status IN ('resolved', 'closed')
"))['count'];

// Recent Announcements (Latest 5)
$recent_announcements = mysqli_query($conn, "
    SELECT title, content, posted_date 
    FROM announcements 
    ORDER BY posted_date DESC 
    LIMIT 5
");

// My Recent Bookings (Latest 5)
$recent_bookings = mysqli_query($conn, "
    SELECT fb.booking_date, fb.start_time, fb.end_time, fb.status, f.name as facility_name
    FROM facility_bookings fb
    JOIN facilities f ON fb.facility_id = f.id
    WHERE fb.resident_id = '$resident_id'
    ORDER BY fb.created_at DESC
    LIMIT 5
");

// My Recent Complaints (Latest 5)
$recent_complaints = mysqli_query($conn, "
    SELECT title, category, status, created_at
    FROM complaints
    WHERE resident_id = '$resident_id'
    ORDER BY created_at DESC
    LIMIT 5
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Dashboard | Housing Society</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --dark: #343a40;
            --light: #f8f9fa;
        }

        body {
            background-color: var(--light);
            font-family: 'Segoe UI', sans-serif;
        }

        .welcome-card {
            background: linear-gradient(135deg, #28a745, #218838);
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
        }

        .stat-card {
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: none;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.2;
            position: absolute;
            right: 20px;
            top: 20px;
        }

        .stat-value {
            font-size: 2.4rem;
            font-weight: 700;
            color: var(--dark);
        }

        .activity-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .badge-status {
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container-fluid mt-4 pb-5">

    <!-- Welcome Header -->
    <div class="card welcome-card mb-5 shadow-lg">
        <div class="card-body text-center py-5">
            <h1 class="display-5 mb-2 font-weight-bold text-white">Resident Dashboard 🏡</h1>
            <p class="lead mb-0">
                Welcome home, <strong><?= htmlspecialchars($full_name); ?></strong>!<br>
                <small><?= htmlspecialchars($email); ?> • <?= htmlspecialchars($house_no . ', Block ' . $block); ?></small>
            </p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card stat-card position-relative overflow-hidden bg-white">
                <div class="stat-icon text-success"><i class="fas fa-calendar-check"></i></div>
                <div class="card-body text-center pt-5">
                    <h5 class="text-muted">Total Bookings</h5>
                    <h3 class="stat-value"><?= $total_bookings; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card stat-card position-relative overflow-hidden bg-white">
                <div class="stat-icon text-info"><i class="fas fa-clock"></i></div>
                <div class="card-body text-center pt-5">
                    <h5 class="text-muted">Upcoming Bookings</h5>
                    <h3 class="stat-value"><?= $upcoming_bookings; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card stat-card position-relative overflow-hidden bg-white">
                <div class="stat-icon text-warning"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="card-body text-center pt-5">
                    <h5 class="text-muted">Open Complaints</h5>
                    <h3 class="stat-value"><?= $open_complaints; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card stat-card position-relative overflow-hidden bg-white">
                <div class="stat-icon text-success"><i class="fas fa-check-circle"></i></div>
                <div class="card-body text-center pt-5">
                    <h5 class="text-muted">Resolved Complaints</h5>
                    <h3 class="stat-value"><?= $resolved_complaints; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Activity -->
    <div class="row">
        <!-- Quick Action Buttons -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-link mr-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="profile.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-user mr-3 text-primary"></i>My Profile</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="view_bookings.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-calendar-plus mr-3 text-success"></i>Book Facilities</span>
                            <span class="badge badge-success badge-pill"><?= $total_bookings ?></span>
                        </a>
                        <a href="complains.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-tools mr-3 text-warning"></i>My Complaints</span>
                            <span class="badge badge-warning badge-pill"><?= $total_complaints ?></span>
                        </a>
                        <a href="announcements.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-bullhorn mr-3 text-info"></i>Announcements</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Announcements -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-bullhorn mr-2"></i>Latest Announcements</h5>
                </div>
                <div class="card-body activity-list">
                    <?php if ($recent_announcements->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($ann = $recent_announcements->fetch_assoc()): ?>
                                <div class="list-group-item">
                                    <h6 class="mb-1 font-weight-bold"><?= htmlspecialchars($ann['title']); ?></h6>
                                    <p class="mb-1 small text-muted"><?= htmlspecialchars(substr($ann['content'], 0, 100)) . (strlen($ann['content']) > 100 ? '...' : ''); ?></p>
                                    <small class="text-muted"><?= date('M d, Y', strtotime($ann['posted_date'])); ?></small>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <div class="card-footer text-center bg-light">
                            <a href="announcements.php" class="btn btn-info btn-sm">View All Announcements</a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-bullhorn fa-3x mb-3 opacity-25"></i>
                            <p>No announcements yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Activity (Bookings & Complaints) -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-history mr-2"></i>My Recent Activity</h5>
                </div>
                <div class="card-body activity-list">
                    <div class="list-group list-group-flush">
                        <?php 
                        // Merge recent bookings and complaints by date (simplified: show latest 5 from both)
                        $all_activity = [];
                        mysqli_data_seek($recent_bookings, 0);
                        while ($book = $recent_bookings->fetch_assoc()) {
                            $all_activity[] = ['type' => 'booking', 'data' => $book, 'date' => $book['booking_date']];
                        }
                        mysqli_data_seek($recent_complaints, 0);
                        while ($comp = $recent_complaints->fetch_assoc()) {
                            $all_activity[] = ['type' => 'complaint', 'data' => $comp, 'date' => $comp['created_at']];
                        }
                        usort($all_activity, function($a, $b) {
                            return strtotime($b['date']) - strtotime($a['date']);
                        });
                        $all_activity = array_slice($all_activity, 0, 5);
                        ?>

                        <?php if (!empty($all_activity)): ?>
                            <?php foreach ($all_activity as $act): ?>
                                <?php if ($act['type'] === 'booking'): ?>
                                    <?php $b = $act['data']; ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><i class="fas fa-calendar-alt text-success mr-2"></i><?= htmlspecialchars($b['facility_name']); ?></h6>
                                            <small><?= date('M d', strtotime($b['booking_date'])); ?></small>
                                        </div>
                                        <small>Time: <?= date('h:i A', strtotime($b['start_time'])); ?> - <?= date('h:i A', strtotime($b['end_time'])); ?><br>
                                        Status: <span class="badge badge-<?= $b['status'] === 'approved' ? 'success' : ($b['status'] === 'pending' ? 'warning' : 'secondary'); ?>">
                                            <?= ucfirst($b['status']); ?>
                                        </span></small>
                                    </div>
                                <?php else: ?>
                                    <?php $c = $act['data']; ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><i class="fas fa-tools text-warning mr-2"></i><?= htmlspecialchars($c['title']); ?></h6>
                                            <small><?= date('M d', strtotime($c['created_at'])); ?></small>
                                        </div>
                                        <small>Category: <?= htmlspecialchars($c['category'] ?? 'General'); ?><br>
                                        Status: <span class="badge badge-<?= in_array($c['status'], ['resolved','closed']) ? 'success' : 'warning'; ?>">
                                            <?= ucfirst(str_replace('_', ' ', $c['status'])); ?>
                                        </span></small>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-history fa-3x mb-3 opacity-25"></i>
                                <p>No recent activity.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>