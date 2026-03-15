<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    die("الحالة غير موجودة.");
}

$case_id = (int) $_GET["id"];

$sql = "SELECT * FROM donation_cases WHERE id = $case_id AND is_active = 1 LIMIT 1";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("الحالة غير موجودة أو غير متاحة.");
}

$case = mysqli_fetch_assoc($result);

$image = !empty($case["image_path"]) ? $case["image_path"] : "assets/images/default-case.png";
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الحالة - أثر</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="site-body">

    <header class="site-header">
        <div class="header-container">
            <div class="header-right">
                <img src="assets/images/logo.png" alt="شعار أثر" class="header-logo">

                <div class="welcome-box">
                    <p class="welcome-text">مرحبًا بك</p>
                    <h2 class="welcome-name"><?php echo $_SESSION["user_name"]; ?></h2>
                </div>
            </div>

            <nav class="header-nav">
                <a href="home.php" class="nav-link">الرئيسية</a>
                <a href="cases.php" class="nav-link active">عرض الحالات</a>
                <a href="add-request.php" class="nav-link"> تبرع</a>
                <a href="history.php" class="nav-link">سجل طلباتي</a>
                <a href="profile.php" class="nav-link">الملف الشخصي</a>
                <a href="logout.php" class="nav-link logout-link">تسجيل الخروج</a>
            </nav>
        </div>
    </header>

    <main class="home-main">
        <section class="details-card">
            <div class="details-image-box">
                <img src="<?php echo htmlspecialchars($image); ?>" alt="صورة الحالة" class="details-image">
            </div>

            <div class="details-content">
                <span class="details-badge"><?php echo htmlspecialchars($case["category"]); ?></span>

                <h1 class="details-title">
                    <?php echo htmlspecialchars($case["title"]); ?>
                </h1>

                <div class="details-meta">
                    <p><strong>التصنيف:</strong> <?php echo htmlspecialchars($case["category"]); ?></p>

                    <?php if ($case["category"] === "أخرى" && !empty($case["other_category"])): ?>
                        <p><strong>تفاصيل التصنيف:</strong> <?php echo htmlspecialchars($case["other_category"]); ?></p>
                    <?php endif; ?>

                    <p><strong>تاريخ الإضافة:</strong> <?php echo date("Y-m-d", strtotime($case["created_at"])); ?></p>
                </div>

                <div class="details-description-box">
                    <h3 class="details-subtitle">وصف الحالة</h3>
                    <p class="details-description">
                        <?php echo nl2br(htmlspecialchars($case["description"])); ?>
                    </p>
                </div>

                <div class="details-actions">
                    <a href="donate-case.php?id=<?php echo $case["id"]; ?>" class="main-btn details-btn">
                       تبرع الان 
                    </a>

                    <a href="cases.php" class="secondary-btn details-btn">
                        العودة إلى الحالات
                    </a>
                </div>
            </div>
        </section>
    </main>

</body>
</html>