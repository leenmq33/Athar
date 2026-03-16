<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";

$user_id = (int) $_SESSION["user_id"];

$sql = "SELECT * FROM users WHERE id = $user_id LIMIT 1";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("المستخدم غير موجود.");
}

$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الشخصي - أثر</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="site-body">

<header class="site-header">
    <div class="header-container">
        <div class="header-right">
            <img src="assets/images/logo.png" alt="شعار أثر" class="header-logo">
            <div class="welcome-box">
                <p class="welcome-text">مرحبًا بك</p>
                <h2 class="welcome-name"><?php echo htmlspecialchars($_SESSION["user_name"]); ?></h2>
            </div>
        </div>

        <nav class="header-nav">
            <a href="home.php" class="nav-link">الرئيسية</a>
            <a href="cases.php" class="nav-link">عرض الحالات</a>
            <a href="add-request.php" class="nav-link">تبرع</a>
            <a href="history.php" class="nav-link">تبرعاتي</a>
            <a href="profile.php" class="nav-link active">الملف الشخصي</a>
            <a href="logout.php" class="nav-link logout-link">تسجيل الخروج</a>
        </nav>
    </div>
</header>

<main class="home-main">
    <div class="auth-card request-form-card">
        <h1 class="auth-title">الملف الشخصي</h1>
       

        <div class="details-meta">
            <p><strong>الاسم الأول:</strong> <?php echo htmlspecialchars($user["first_name"]); ?></p>
            <p><strong>الاسم الأخير:</strong> <?php echo htmlspecialchars($user["last_name"]); ?></p>
            <p><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($user["email"]); ?></p>
            <p><strong>رقم الجوال:</strong> <?php echo htmlspecialchars($user["phone"]); ?></p>
            <p><strong>تاريخ إنشاء الحساب:</strong> <?php echo date("Y-m-d", strtotime($user["created_at"])); ?></p>
        </div>

        <div class="details-actions" style="margin-top: 18px;">
            <a href="edit-profile.php" class="main-btn details-btn">تعديل الملف الشخصي</a>
        </div>
    </div>
</main>

</body>
</html>