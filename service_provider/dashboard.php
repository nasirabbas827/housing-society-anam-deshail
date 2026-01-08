<?php
session_start();

// Authentication Check - Only allow service_provider
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "service_provider") {
    header("Location: ../login.php");
    exit;
}

include('../config.php'); // Adjust path if needed

$service_provider_id = $_SESSION["id"]; // Assuming you store user_id in session after login
$full_name = $_SESSION["full_name"];
$email = $_SESSION["email"];
$service_type = $_SESSION["service_type"] ?? 'Service Provider'; // Optional: store in session

// ==================== FETCH STATISTICS FOR SERVICE PROVIDER ====================

// Total complaints assigned to this provider
$total_assigned = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as count 
    FROM complaints 
    WHERE service_provider_id = '$service_provider_id'
"))['count'];

// Complaints currently in progress
$in_progress = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as count 
    FROM complaints 
    WHERE service_provider_id = '$service_provider_id' AND status = 'in_progress'
"))['count'];

// Resolved complaints
$resolved = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as count 
    FROM complaints 
    WHERE service_provider_id = '$service_provider_id' AND status IN ('resolved', 'closed')
"))['count'];

// Open/Assigned but not started
$pending_work = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as count 
    FROM complaints 
    WHERE service_provider_id = '$service_provider_id' AND status = 'assigned'
"))['count'];

// Recent Assigned Complaints (Latest 6)
$recent_complaints = mysqli_query($conn, "
    SELECT c.id, c.title, c.category, c.status, c.created_at, u.full_name as resident_name, u.house_no, u.block
    FROM complaints c
    JOIN users u ON c.resident_id = u.id
    WHERE c.service_provider_id = '$service_provider_id'
    ORDER BY c.created_at DESC
    LIMIT 6
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Provider Dashboard | Housing Society</title>
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
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 123, 255, 0.3);
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

        .complaint-list {
            max-height: 420px;
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
            <h1 class="display-5 mb-2 font-weight-bold text-white">Service Provider Dashboard 🔧</h1>
            <p class="lead mb-0">
                Welcome back, <strong><?= htmlspecialchars($full_name); ?></strong>!<br>
                <small><?= htmlspecialchars($email); ?> • <?= htmlspecialchars($service_type); ?></small>
            </p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card stat-card position-relative overflow-hidden bg-white">
                <div class="stat-icon text-primary"><i class="fas fa-tools"></i></div>
                <div class="card-body text-center pt-5">
                    <h5 class="text-muted">Total Assigned Jobs</h5>
                    <h3 class="stat-value"><?= $total_assigned; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card stat-card position-relative overflow-hidden bg-white">
                <div class="stat-icon text-warning"><i class="fas fa-clock"></i></div>
                <div class="card-body text-center pt-5">
                    <h5 class="text-muted">Pending / Assigned</h5>
                    <h3 class="stat-value"><?= $pending_work; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card stat-card position-relative overflow-hidden bg-white">
                <div class="stat-icon text-info"><i class="fas fa-spinner"></i></div>
                <div class="card-body text-center pt-5">
                    <h5 class="text-muted">In Progress</h5>
                    <h3 class="stat-value"><?= $in_progress; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card stat-card position-relative overflow-hidden bg-white">
                <div class="stat-icon text-success"><i class="fas fa-check-circle"></i></div>
                <div class="card-body text-center pt-5">
                    <h5 class="text-muted">Completed</h5>
                    <h3 class="stat-value"><?= $resolved; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Complaints -->
    <div class="row">
        <!-- Quick Action Buttons -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-link mr-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="profile.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-user mr-3 text-primary"></i>My Profile</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="complains.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-tools mr-3 text-warning"></i>Manage Service Requests</span>
                            <span class="badge badge-warning badge-pill"><?= $total_assigned ?></span>
                        </a>
                        <a href="announcements.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-bullhorn mr-3 text-info"></i>View Announcements</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Assigned Complaints -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list mr-2"></i>Recently Assigned Complaints</h5>
                    <a href="complains.php" class="btn btn-light btn-sm">View All</a>
                </div>
                <div class="card-body complaint-list">
                    <?php if ($recent_complaints->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($comp = $recent_complaints->fetch_assoc()): ?>
                                <?php
                                $status_class = $comp['status'] === 'in_progress' ? 'warning' :
                                    ($comp['status'] === 'assigned' ? 'info' :
                                        ($comp['status'] === 'resolved' || $comp['status'] === 'closed' ? 'success' : 'secondary'));
                                ?>
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 font-weight-bold"><?= htmlspecialchars($comp['title']); ?></h6>
                                        <small class="text-muted"><?= date('M d, Y', strtotime($comp['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-1 small">
                                        <strong>Resident:</strong> <?= htmlspecialchars($comp['resident_name']); ?>
                                        (<?= htmlspecialchars($comp['house_no'] . ', ' . $comp['block']); ?>)
                                    </p>
                                    <p class="mb-1 small text-muted">
                                        Category: <strong><?= htmlspecialchars($comp['category'] ?? 'General'); ?></strong>
                                    </p>
                                    <span class="badge badge-<?= $status_class ?> badge-status">
                                        <?= ucfirst(str_replace('_', ' ', $comp['status'])); ?>
                                    </span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                            <p>No complaints assigned to you yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
