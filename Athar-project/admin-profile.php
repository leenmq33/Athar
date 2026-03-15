<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";

$admin_id = (int) $_SESSION["admin_id"];

$sql = "SELECT * FROM admins WHERE id = $admin_id LIMIT 1";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("الأدمن غير موجود.");
}

$admin = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الشخصي للأدمن - أثر</title>
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
            <a href="admin-dashboard.php" class="nav-link">لوحة التحكم</a>
            <a href="admin-cases.php" class="nav-link">الحالات</a>
            <a href="admin-requests.php" class="nav-link">الطلبات</a>
            <a href="admin-profile.php" class="nav-link active">الملف الشخصي</a>
            <a href="admin-logout.php" class="nav-link logout-link">تسجيل الخروج</a>
        </nav>
    </div>
</header>

<main class="home-main">
    <div class="auth-card request-form-card">
        <h1 class="auth-title">الملف الشخصي</h1>
        <p class="auth-subtitle">يمكنك الاطلاع على بيانات الأدمن وتعديلها</p>

        <div class="details-meta">
            <p><strong>الاسم الأول:</strong> <?php echo htmlspecialchars($admin["first_name"]); ?></p>
            <p><strong>الاسم الأخير:</strong> <?php echo htmlspecialchars($admin["last_name"]); ?></p>
            <p><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($admin["email"]); ?></p>
            <p><strong>رقم الجوال:</strong> <?php echo htmlspecialchars($admin["phone"]); ?></p>
            <p><strong>تاريخ إنشاء الحساب:</strong> <?php echo date("Y-m-d", strtotime($admin["created_at"])); ?></p>
        </div>

        <div class="details-actions" style="margin-top: 18px;">
            <a href="edit-admin-profile.php" class="main-btn details-btn">تعديل الملف الشخصي</a>
        </div>
    </div>
</main>

</body>
</html>
