<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";

$user_id = (int) $_SESSION["user_id"];

$sql = "SELECT * FROM donation_requests
        WHERE user_id = $user_id
        ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("خطأ في جلب الطلبات: " . mysqli_error($conn));
}

function getStatusText($status) {
    switch ($status) {
        case "accepted":
            return "مقبول";
        case "rejected":
            return "مرفوض";
        case "completed":
            return "مكتمل";
        default:
            return "قيد الانتظار";
    }
}

function getDeliveryText($method) {
    if ($method === "pickup") {
        return "استلام من المنزل";
    }
    return "تسليم للفرع";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل طلباتي - أثر</title>
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
            <a href="add-request.php" class="nav-link"> تبرع</a>
            <a href="history.php" class="nav-link active">تبرعاتي</a>
            <a href="profile.php" class="nav-link">الملف الشخصي</a>
            <a href="logout.php" class="nav-link logout-link">تسجيل الخروج</a>
        </nav>
    </div>
</header>

<main class="home-main">
    <h1 class="section-title" style="margin-bottom: 20px;">تبرعاتي</h1>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="history-grid">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <?php
                    $status = $row["status"];
                    $status_text = getStatusText($status);
                    $delivery_text = getDeliveryText($row["delivery_method"]);
                ?>

                <div class="history-card">

                    <div class="history-top">
                        <div>
                            <h3 class="history-title"><?php echo htmlspecialchars($row["title"]); ?></h3>
                            <p class="history-category"><?php echo htmlspecialchars($row["category"]); ?></p>
                        </div>

                        <span class="history-status-badge status-<?php echo htmlspecialchars($status); ?>">
                            <?php echo $status_text; ?>
                        </span>
                    </div>

                    <div class="status-progress <?php echo htmlspecialchars($status); ?>">
                        <?php if ($status === "rejected"): ?>
                            <div class="progress-step active">قيد الانتظار</div>
                            <div class="progress-line active"></div>
                            <div class="progress-step rejected-step">مرفوض</div>
                        <?php else: ?>
                            <div class="progress-step <?php echo in_array($status, ["pending","accepted","completed"]) ? "active" : ""; ?>">قيد الانتظار</div>
                            <div class="progress-line <?php echo in_array($status, ["accepted","completed"]) ? "active" : ""; ?>"></div>
                            <div class="progress-step <?php echo in_array($status, ["accepted","completed"]) ? "active" : ""; ?>">مقبول</div>
                            <div class="progress-line <?php echo $status === "completed" ? "active" : ""; ?>"></div>
                            <div class="progress-step <?php echo $status === "completed" ? "active" : ""; ?>">مكتمل</div>
                        <?php endif; ?>
                    </div>

                    <p class="history-desc">
                        <?php echo htmlspecialchars(mb_strimwidth($row["description"], 0, 120, "...", "UTF-8")); ?>
                    </p>

                    <div class="history-meta">
                        <p><strong>طريقة التسليم:</strong> <?php echo $delivery_text; ?></p>
                        <p><strong>تاريخ التبرع:</strong> <?php echo date("Y-m-d", strtotime($row["created_at"])); ?></p>
                    </div>

                    <div class="history-buttons">
                        <a href="request-details.php?id=<?php echo $row["id"]; ?>" class="secondary-btn history-btn">التفاصيل</a>

                        <?php if ($status === "pending"): ?>
                            <a href="edit-request.php?id=<?php echo $row["id"]; ?>" class="main-btn history-btn">تعديل</a>
                            <a href="delete-request.php?id=<?php echo $row["id"]; ?>" class="danger-btn history-btn"
                               onclick="return confirm('هل أنت متأكد من حذف الطلب؟');">حذف</a>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-history-card">
            <h3>لا يوجد تبرعات حتى الآن</h3>
            <p>ابدأ بإرسال أول طلب تبرع من الحالات أو من إضافة تبرع.</p>
            <a href="cases.php" class="main-btn empty-history-btn">عرض الحالات</a>
        </div>
    <?php endif; ?>
</main>

</body>
</html>