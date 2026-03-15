<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";

$search = "";

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

$sql = "SELECT * FROM donation_cases WHERE is_active = 1";

if (!empty($search)) {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND category LIKE '%$safe_search%'";
}

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("خطأ في الاستعلام: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>الحالات - أثر</title>
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
            <a href="history.php" class="nav-link">تبرعاتي</a>
            <a href="profile.php" class="nav-link">الملف الشخصي</a>
            <a href="logout.php" class="nav-link logout-link">تسجيل الخروج</a>
        </nav>
    </div>
</header>

<main class="home-main">

    <h1 class="section-title" style="margin-bottom:20px;">الحالات المتاحة للتبرع</h1>

    <form method="GET" class="search-box">
        <input
            type="text"
            name="search"
            placeholder="ابحث عن تصنيف مثل: ملابس، أثاث، تعليم، أجهزة إلكترونية، ألعاب"
            value="<?php echo htmlspecialchars($search); ?>"
        >
        <button type="submit">بحث</button>
    </form>

    <div class="cases-grid">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($case = mysqli_fetch_assoc($result)): ?>
                <?php
                    $image = !empty($case['image_path']) ? $case['image_path'] : "assets/images/default-case.png";
                ?>
                <div class="case-card">
                    <img src="<?php echo $image; ?>" alt="صورة الحالة" class="case-image">

                    <h3 class="case-title"><?php echo htmlspecialchars($case['title']); ?></h3>

                    <p class="case-category"><?php echo htmlspecialchars($case['category']); ?></p>

                    <p class="case-desc">
                        <?php echo htmlspecialchars(mb_strimwidth($case['description'], 0, 120, "...", "UTF-8")); ?>
                    </p>

                    <div class="case-buttons">
                        <a href="case-details.php?id=<?php echo $case['id']; ?>" class="secondary-btn">التفاصيل</a>
                        <a href="donate-case.php?id=<?php echo $case['id']; ?>" class="main-btn">تبرع</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>لا توجد حالات مطابقة للبحث.</p>
        <?php endif; ?>
    </div>

</main>

</body>
</html>