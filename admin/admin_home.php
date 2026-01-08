<?php
session_start();
include('config.php');

// Authentication Check
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// ==================== FETCH STATISTICS ====================

$total_residents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'resident' AND status = 'active'"))['count'];
$total_pending_residents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'resident' AND status = 'pending'"))['count'];

$total_service_providers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'service_provider' AND status = 'active'"))['count'];
$total_pending_providers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'service_provider' AND status = 'pending'"))['count'];

$total_pending_approvals = $total_pending_residents + $total_pending_providers;

$total_facilities = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM facilities"))['count'];
$total_announcements = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM announcements"))['count'];

$total_complaints = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints"))['count'];
$open_complaints = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE status IN ('open', 'assigned', 'in_progress')"))['count'];

// Upcoming Bookings Count (from today onwards)
$upcoming_bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM facility_bookings WHERE booking_date >= CURDATE()"))['count'];

// Pending Facility Bookings
$pending_bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM facility_bookings WHERE status = 'pending'"))['count'];


// ==================== CHART DATA: User Distribution ====================
$user_roles_labels = ['Active Residents', 'Pending Residents', 'Active Providers', 'Pending Providers'];
$user_roles_data = [$total_residents, $total_pending_residents, $total_service_providers, $total_pending_providers];


// ==================== CHART DATA: Complaint Status ====================
$complaint_status_query = mysqli_query($conn, "
    SELECT status, COUNT(*) as count 
    FROM complaints 
    GROUP BY status
");
$complaint_status_labels = [];
$complaint_status_data = [];
while ($row = mysqli_fetch_assoc($complaint_status_query)) {
    $complaint_status_labels[] = ucfirst(str_replace('_', ' ', $row['status']));
    $complaint_status_data[] = $row['count'];
}


// ==================== CHART DATA: Upcoming Bookings (Next 14 Days) ====================
$booking_trend_labels = [];
$booking_trend_data = [];

for ($i = 0; $i < 14; $i++) {
    $date = date('Y-m-d', strtotime("+$i days"));
    $label = date('M d', strtotime($date));
    if ($i == 0) $label = 'Today';
    elseif ($i == 1) $label = 'Tomorrow';

    $booking_trend_labels[] = $label;

    $count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM facility_bookings WHERE booking_date = '$date'");
    $count = mysqli_fetch_assoc($count_result)['count'];
    $booking_trend_data[] = $count;
}


// ==================== RECENT ACTIVITY QUERIES ====================

// Recent Registrations (Residents & Service Providers)
$recent_users = mysqli_query($conn, "
    SELECT full_name, email, role, status, created_at 
    FROM users 
    WHERE role IN ('resident', 'service_provider')
    ORDER BY created_at DESC 
    LIMIT 5
");

// Recent Complaints
$recent_complaints = mysqli_query($conn, "
    SELECT c.id, c.title, c.category, c.status, c.created_at, u.full_name as resident_name
    FROM complaints c
    JOIN users u ON c.resident_id = u.id
    ORDER BY c.created_at DESC
    LIMIT 5
");

// Recent Facility Bookings
$recent_bookings = mysqli_query($conn, "
    SELECT fb.id, fb.booking_date, fb.start_time, fb.end_time, fb.status, f.name as facility_name, u.full_name as resident_name
    FROM facility_bookings fb
    JOIN facilities f ON fb.facility_id = f.id
    JOIN users u ON fb.resident_id = u.id
    ORDER BY fb.created_at DESC
    LIMIT 5
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Housing Society Management</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --secondary: #6c757d;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        body { background-color: var(--light); font-family: 'Segoe UI', sans-serif; }
        
        .welcome-card {
            background: linear-gradient(135deg, var(--primary), #0056b3);
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 123, 255, 0.3);
        }

        .card-stats {
            transition: all 0.3s ease;
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }
        .card-stats:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .card-header-icon {
            padding: 1.2rem;
            border-radius: 12px;
            position: absolute;
            top: -18px;
            right: 18px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            color: white;
        }

        .stats-value {
            font-size: 2.4rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .notification-list {
            max-height: 380px;
            overflow-y: auto;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container-fluid mt-4 pb-5">
    <!-- Welcome Section -->
    <div class="card welcome-card mb-5 shadow-lg">
        <div class="card-body text-center py-5">
            <h1 class="mb-2 font-weight-bold text-white">Housing Society Admin Dashboard 🏡</h1>
            <p class="lead mb-0">Welcome back, <strong><?php echo htmlspecialchars($_SESSION["username"] ?? 'Admin'); ?>!</strong> Manage your society with ease.</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-stats">
                <div class="card-header-icon" style="background: linear-gradient(45deg, #28a745, #218838);"><i class="fas fa-home fa-2x"></i></div>
                <div class="card-body pt-4">
                    <h5 class="text-muted">Active Residents</h5>
                    <h3 class="stats-value"><?php echo $total_residents; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-stats">
                <div class="card-header-icon" style="background: linear-gradient(45deg, #ffc107, #ffb300);"><i class="fas fa-user-clock fa-2x"></i></div>
                <div class="card-body pt-4">
                    <h5 class="text-muted">Pending Approvals</h5>
                    <h3 class="stats-value"><?php echo $total_pending_approvals; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-stats">
                <div class="card-header-icon" style="background: linear-gradient(45deg, #17a2b8, #138496);"><i class="fas fa-calendar-check fa-2x"></i></div>
                <div class="card-body pt-4">
                    <h5 class="text-muted">Upcoming Bookings</h5>
                    <h3 class="stats-value"><?php echo $upcoming_bookings; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-stats">
                <div class="card-header-icon" style="background: linear-gradient(45deg, #dc3545, #c82333);"><i class="fas fa-exclamation-triangle fa-2x"></i></div>
                <div class="card-body pt-4">
                    <h5 class="text-muted">Open Complaints</h5>
                    <h3 class="stats-value"><?php echo $open_complaints; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">User Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="userRoleChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Complaint Status</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="complaintStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Upcoming Bookings (Next 14 Days)</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="bookingTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Recent Registrations</h5>
                </div>
                <div class="card-body notification-list">
                    <?php if ($recent_users->num_rows > 0): ?>
                        <ul class="list-unstyled">
                            <?php while ($user = $recent_users->fetch_assoc()): ?>
                                <li class="media alert <?php echo $user['status'] === 'pending' ? 'alert-warning' : 'alert-light'; ?> mb-3 p-3 rounded">
                                    <i class="fas fa-user-circle fa-3x mr-3 text-primary"></i>
                                    <div class="media-body">
                                        <h6 class="mt-0 mb-1"><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></h6>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($user['email']); ?><br>
                                            Role: <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?> | 
                                            Status: <span class="badge badge-<?php echo $user['status'] === 'pending' ? 'warning' : 'success'; ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span><br>
                                            Joined: <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                        </small>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">No recent registrations.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Recent Complaints</h5>
                </div>
                <div class="card-body notification-list">
                    <?php if ($recent_complaints->num_rows > 0): ?>
                        <ul class="list-unstyled">
                            <?php while ($comp = $recent_complaints->fetch_assoc()): ?>
                                <li class="alert alert-light mb-3 border-left-danger">
                                    <h6><strong><?php echo htmlspecialchars($comp['title']); ?></strong></h6>
                                    <small>
                                        By: <?php echo htmlspecialchars($comp['resident_name']); ?><br>
                                        Category: <?php echo htmlspecialchars($comp['category'] ?? 'General'); ?> | 
                                        Status: <span class="badge badge-<?php echo in_array($comp['status'], ['resolved','closed']) ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $comp['status'])); ?>
                                        </span><br>
                                        Filed: <?php echo date('M d, Y H:i', strtotime($comp['created_at'])); ?>
                                    </small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">No recent complaints.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Recent Facility Bookings</h5>
                </div>
                <div class="card-body notification-list">
                    <?php if ($recent_bookings->num_rows > 0): ?>
                        <ul class="list-unstyled">
                            <?php while ($book = $recent_bookings->fetch_assoc()): ?>
                                <li class="alert alert-light mb-3 border-left-success">
                                    <h6><strong><?php echo htmlspecialchars($book['facility_name']); ?></strong></h6>
                                    <small>
                                        By: <?php echo htmlspecialchars($book['resident_name']); ?><br>
                                        Date: <?php echo date('M d, Y', strtotime($book['booking_date'])); ?><br>
                                        Time: <?php echo date('h:i A', strtotime($book['start_time'])); ?> - <?php echo date('h:i A', strtotime($book['end_time'])); ?><br>
                                        Status: <span class="badge badge-<?php echo $book['status'] === 'approved' ? 'success' : ($book['status'] === 'pending' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst($book['status']); ?>
                                        </span>
                                    </small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">No recent bookings.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Pass PHP data to JavaScript
const userRoleLabels = <?php echo json_encode($user_roles_labels); ?>;
const userRoleData = <?php echo json_encode($user_roles_data); ?>;

const complaintStatusLabels = <?php echo json_encode($complaint_status_labels); ?>;
const complaintStatusData = <?php echo json_encode($complaint_status_data); ?>;

const bookingTrendLabels = <?php echo json_encode($booking_trend_labels); ?>;
const bookingTrendData = <?php echo json_encode($booking_trend_data); ?>;

// User Distribution Chart
new Chart(document.getElementById('userRoleChart'), {
    type: 'doughnut',
    data: {
        labels: userRoleLabels,
        datasets: [{
            data: userRoleData,
            backgroundColor: ['#28a745', '#ffc107', '#007bff', '#fd7e14'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
});

// Complaint Status Chart
new Chart(document.getElementById('complaintStatusChart'), {
    type: 'pie',
    data: {
        labels: complaintStatusLabels,
        datasets: [{
            data: complaintStatusData,
            backgroundColor: ['#dc3545', '#ffc107', '#17a2b8', '#28a745', '#6c757d'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
});

// Upcoming Bookings Line Chart
new Chart(document.getElementById('bookingTrendChart'), {
    type: 'line',
    data: {
        labels: bookingTrendLabels,
        datasets: [{
            label: 'Bookings per Day',
            data: bookingTrendData,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.2)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#28a745',
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        },
        plugins: {
            legend: { display: true }
        }
    }
});
</script>
</body>
</html>