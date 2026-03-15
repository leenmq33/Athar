<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$admin_id = (int) $_SESSION["admin_id"];

/* إحصائيات */
$users_count = 0;
$cases_count = 0;
$requests_count = 0;
$pending_count = 0;
$accepted_count = 0;
$completed_count = 0;

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
if ($result) {
    $users_count = mysqli_fetch_assoc($result)["total"];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM donation_cases");
if ($result) {
    $cases_count = mysqli_fetch_assoc($result)["total"];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM donation_requests");
if ($result) {
    $requests_count = mysqli_fetch_assoc($result)["total"];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM donation_requests WHERE status = 'pending'");
if ($result) {
    $pending_count = mysqli_fetch_assoc($result)["total"];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM donation_requests WHERE status = 'accepted'");
if ($result) {
    $accepted_count = mysqli_fetch_assoc($result)["total"];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM donation_requests WHERE status = 'completed'");
if ($result) {
    $completed_count = mysqli_fetch_assoc($result)["total"];
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الأدمن - أثر</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="site-body">

<header class="site-header">
    <div class="header-container">
        <div class="header-right">
            <img src="assets/images/logo.png" alt="شعار أثر" class="header-logo">
            <div class="welcome-box">
                <p class="welcome-text">مرحبًا بك</p>
                <h2 class="welcome-name"><?php echo htmlspecialchars($_SESSION["admin_name"]); ?></h2>
            </div>
        </div>

        <nav class="header-nav">
            <a href="admin-dashboard.php" class="nav-link active">لوحة التحكم</a>
            <a href="admin-cases.php" class="nav-link">الحالات</a>
            <a href="admin-requests.php" class="nav-link">الطلبات</a>
            <a href="admin-profile.php" class="nav-link">الملف الشخصي</a>
            <a href="admin-logout.php" class="nav-link logout-link">تسجيل الخروج</a>
        </nav>
    </div>
</header>

<main class="home-main">
    <h1 class="section-title" style="margin-bottom: 20px;">لوحة تحكم الأدمن</h1>

    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <h3>عدد المستخدمين</h3>
            <p><?php echo $users_count; ?></p>
        </div>

        <div class="admin-stat-card">
            <h3>عدد الحالات</h3>
            <p><?php echo $cases_count; ?></p>
        </div>

        <div class="admin-stat-card">
            <h3>عدد الطلبات</h3>
            <p><?php echo $requests_count; ?></p>
        </div>

        <div class="admin-stat-card">
            <h3>الطلبات المعلقة</h3>
            <p><?php echo $pending_count; ?></p>
        </div>

        <div class="admin-stat-card">
            <h3>الطلبات المقبولة</h3>
            <p><?php echo $accepted_count; ?></p>
        </div>

        <div class="admin-stat-card">
            <h3>الطلبات المكتملة</h3>
            <p><?php echo $completed_count; ?></p>
        </div>
    </div>

    <div class="admin-actions-box">
        <a href="admin-cases.php" class="main-btn admin-action-btn">إدارة الحالات</a>
        <a href="admin-requests.php" class="secondary-btn admin-action-btn">إدارة الطلبات</a>
    </div>
</main>

</body>
</html>
