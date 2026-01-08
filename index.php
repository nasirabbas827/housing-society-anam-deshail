<?php
session_start();
include('config.php');

// Fetch latest 3 announcements for public display
$latest_announcements = mysqli_query($conn, "
    SELECT title, content, posted_date 
    FROM announcements 
    ORDER BY posted_date DESC 
    LIMIT 3
");

// Fetch all facilities for showcase
$facilities = mysqli_query($conn, "
    SELECT name, description, capacity 
    FROM facilities 
    WHERE availability = 'available'
    LIMIT 6
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Housing Society Management System</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
            --accent: #007bff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Hero Section */
        .hero-section {
            height: 90vh;
            min-height: 600px;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('./images/hotel.jpg') center/cover no-repeat;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
        }

        .hero-section .lead {
            font-size: 1.4rem;
            max-width: 800px;
            margin: 20px auto;
        }

        .btn-cta {
            padding: 14px 40px;
            font-size: 1.2rem;
            border-radius: 50px;
            background: var(--success);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-cta:hover {
            background: #218838;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.4);
        }

        /* Section Styling */
        .section {
            padding: 80px 0;
        }

        .section-title {
            color: var(--dark);
            font-weight: 700;
            position: relative;
            margin-bottom: 50px;
        }

        .section-title::after {
            content: '';
            width: 80px;
            height: 5px;
            background: var(--primary);
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 3px;
        }

        .feature-card {
            text-align: center;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .feature-icon {
            font-size: 3.5rem;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .facility-card img {
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
        }

        .announcement-card {
            border-left: 5px solid var(--success);
            transition: all 0.3s;
        }

        .announcement-card:hover {
            border-left-color: var(--primary);
            transform: translateX(10px);
        }

        footer {
            background: var(--dark);
            color: white;
            padding: 50px 0 20px;
        }

        footer a {
            color: var(--success);
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<!-- 1. Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1 class="display-4 text-white">Welcome to Your Modern Housing Society</h1>
        <p class="lead">A centralized platform to manage complaints, book facilities, stay updated with announcements, and live harmoniously.</p>
        <?php if (!isset($_SESSION['id']) && !isset($_SESSION['user_id'])): ?>
            <a href="login.php" class="btn btn-cta btn-lg">
                <i class="fas fa-sign-in-alt mr-2"></i> Login / Register
            </a>
        <?php else: ?>
            <a href="dashboard.php" class="btn btn-cta btn-lg">
                <i class="fas fa-tachometer-alt mr-2"></i> Go to Dashboard
            </a>
        <?php endif; ?>
    </div>
</section>

<!-- 2. Key Features -->
<section class="section bg-light">
    <div class="container">
        <h2 class="text-center section-title">Key Features</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="feature-card bg-white">
                    <div class="feature-icon"><i class="fas fa-home"></i></div>
                    <h4>Resident Management</h4>
                    <p>Easy registration, profile updates, and secure access for all residents and service providers.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card bg-white">
                    <div class="feature-icon"><i class="fas fa-calendar-alt"></i></div>
                    <h4>Facility Booking</h4>
                    <p>Book community hall, sports ground, or other amenities with real-time availability checking.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card bg-white">
                    <div class="feature-icon"><i class="fas fa-tools"></i></div>
                    <h4>Complaint Tracking</h4>
                    <p>Lodge complaints and track their status. Admins assign to service providers for quick resolution.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card bg-white">
                    <div class="feature-icon"><i class="fas fa-bullhorn"></i></div>
                    <h4>Announcements</h4>
                    <p>Stay informed with important notices, events, and updates from the society management.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card bg-white">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <h4>Secure & Transparent</h4>
                    <p>Role-based access ensures privacy and transparency in all society operations.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card bg-white">
                    <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
                    <h4>Responsive Design</h4>
                    <p>Access the system anytime, anywhere — from desktop or mobile devices.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. Available Facilities -->
<section class="section">
    <div class="container">
        <h2 class="text-center section-title">Society Facilities</h2>
        <div class="row">
            <?php if (mysqli_num_rows($facilities) > 0): ?>
                <?php while ($fac = mysqli_fetch_assoc($facilities)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card facility-card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?= htmlspecialchars($fac['name']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($fac['description'] ?: 'A premium facility for residents.') ?></p>
                                <p class="text-muted"><i class="fas fa-users mr-1"></i> Capacity: <?= $fac['capacity'] ?> persons</p>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'resident'): ?>
                                    <a href="resident/view_bookings.php" class="btn btn-success btn-sm">Book Now</a>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-outline-primary btn-sm">Login to Book</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">No facilities listed yet. Contact admin.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 4. Latest Announcements -->
<section class="section bg-light">
    <div class="container">
        <h2 class="text-center section-title">Latest Announcements</h2>
        <div class="row">
            <?php if (mysqli_num_rows($latest_announcements) > 0): ?>
                <?php mysqli_data_seek($latest_announcements, 0); ?>
                <?php while ($ann = mysqli_fetch_assoc($latest_announcements)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card announcement-card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($ann['title']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars(substr($ann['content'], 0, 120)) ?>...</p>
                                <small class="text-muted">
                                    <i class="fas fa-calendar mr-1"></i> <?= date('M d, Y', strtotime($ann['posted_date'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <div class="alert alert-secondary">No announcements at the moment.</div>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="announcements.php" class="btn btn-outline-primary">View All Announcements</a>
        </div>
    </div>
</section>

<!-- 5. Why Choose Us / Call to Action -->
<section class="section">
    <div class="container text-center">
        <h2 class="section-title mb-4">A Better Way to Manage Your Society</h2>
        <p class="lead col-lg-8 mx-auto">
            Say goodbye to manual registers, delayed complaints, and missed updates. 
            Our system brings efficiency, transparency, and convenience to your housing society.
        </p>
        <div class="mt-5">
            <i class="fas fa-users fa-4x text-success mb-4"></i>
            <h4>Trusted by Residents & Management Committees</h4>
            <p class="text-muted col-lg-6 mx-auto">
                Join hundreds of societies already using digital management for smoother operations.
            </p>
        </div>
        <div class="mt-5">
            <?php if (!isset($_SESSION['id']) && !isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="btn btn-primary btn-lg px-5">Get Started Today</a>
            <?php else: ?>
                <a href="dashboard.php" class="btn btn-success btn-lg px-5">Go to Your Dashboard</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>Housing Society Management System</h5>
                <p>Empowering communities with digital efficiency.</p>
            </div>
            <div class="col-md-6 text-md-right">
                <p>&copy; <?= date('Y') ?> All Rights Reserved.</p>
                <p><a href="#">Privacy Policy</a> | <a href="#">Terms of Use</a></p>
            </div>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>