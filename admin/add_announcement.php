<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title   = $_POST['title'];
    $content = $_POST['content'];

    $stmt = $conn->prepare("
        INSERT INTO announcements (title, content)
        VALUES (?, ?)
    ");
    $stmt->bind_param("ss", $title, $content);

    if ($stmt->execute()) {
        $message = "Announcement posted successfully.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Announcement</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">

    <div class="card mx-auto" style="max-width: 700px;">
        <div class="card-body">

            <h3 class="text-center mb-4">Add Announcement</h3>

            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="row">

                    <div class="col-md-12 mb-3">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Content</label>
                        <textarea name="content" class="form-control" rows="5" required></textarea>
                    </div>

                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Post Announcement</button>
                    <a href="view_announcements.php" class="btn btn-secondary">View Announcements</a>
                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>
