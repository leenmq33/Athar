<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    die("الطلب غير موجود.");
}

$request_id = (int) $_GET["id"];
$user_id = (int) $_SESSION["user_id"];

$sql = "SELECT * FROM donation_requests
        WHERE id = $request_id AND user_id = $user_id
        LIMIT 1";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("الطلب غير موجود أو لا تملك صلاحية عرضه.");
}

$request = mysqli_fetch_assoc($result);

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

$status_text = getStatusText($request["status"]);
$delivery_text = getDeliveryText($request["delivery_method"]);

$image = !empty($request["image_path"]) ? $request["image_path"] : "assets/images/default-case.png";
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الطلب - أثر</title>
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
            <a href="history.php" class="nav-link active">تبرعاتي</a>
            <a href="profile.php" class="nav-link">الملف الشخصي</a>
            <a href="logout.php" class="nav-link logout-link">تسجيل الخروج</a>
        </nav>
    </div>
</header>

<main class="home-main">
    <section class="details-card">
        <div class="details-image-box">
            <img src="<?php echo htmlspecialchars($image); ?>" alt="صورة الطلب" class="details-image">
        </div>

        <div class="details-content">
            <div class="history-top">
                <div>
                    <span class="details-badge"><?php echo htmlspecialchars($request["category"]); ?></span>
                    <h1 class="details-title" style="margin-top: 12px;">
                        <?php echo htmlspecialchars($request["title"]); ?>
                    </h1>
                </div>

                <span class="history-status-badge status-<?php echo htmlspecialchars($request["status"]); ?>">
                    <?php echo $status_text; ?>
                </span>
            </div>

            <div class="status-progress <?php echo htmlspecialchars($request["status"]); ?>">
                <?php if ($request["status"] === "rejected"): ?>
                    <div class="progress-step active">قيد الانتظار</div>
                    <div class="progress-line active"></div>
                    <div class="progress-step rejected-step">مرفوض</div>
                <?php else: ?>
                    <div class="progress-step <?php echo in_array($request["status"], ["pending","accepted","completed"]) ? "active" : ""; ?>">قيد الانتظار</div>
                    <div class="progress-line <?php echo in_array($request["status"], ["accepted","completed"]) ? "active" : ""; ?>"></div>
                    <div class="progress-step <?php echo in_array($request["status"], ["accepted","completed"]) ? "active" : ""; ?>">مقبول</div>
                    <div class="progress-line <?php echo $request["status"] === "completed" ? "active" : ""; ?>"></div>
                    <div class="progress-step <?php echo $request["status"] === "completed" ? "active" : ""; ?>">مكتمل</div>
                <?php endif; ?>
            </div>

            <div class="details-meta">
                <p><strong>الحالة:</strong> <?php echo $status_text; ?></p>
                <p><strong>طريقة التسليم:</strong> <?php echo $delivery_text; ?></p>
                <p><strong>تاريخ الطلب:</strong> <?php echo date("Y-m-d", strtotime($request["created_at"])); ?></p>

                <?php if (!empty($request["case_id"])): ?>
                    <p><strong>نوع التبرع:</strong> مرتبط بحالة</p>
                <?php else: ?>
                    <p><strong>نوع التبرع:</strong> تبرع مستقل</p>
                <?php endif; ?>

                <?php if ($request["category"] === "أخرى" && !empty($request["other_category"])): ?>
                    <p><strong>تفاصيل التصنيف:</strong> <?php echo htmlspecialchars($request["other_category"]); ?></p>
                <?php endif; ?>
            </div>

            <div class="details-description-box">
                <h3 class="details-subtitle">وصف التبرع</h3>
                <p class="details-description">
                    <?php echo nl2br(htmlspecialchars($request["description"])); ?>
                </p>
            </div>

            <?php if ($request["delivery_method"] === "pickup"): ?>
                <div class="details-description-box">
                    <h3 class="details-subtitle">بيانات الاستلام</h3>
                    <p class="details-description"><strong>المدينة:</strong> <?php echo htmlspecialchars($request["city"]); ?></p>
                    <p class="details-description"><strong>الشارع:</strong> <?php echo htmlspecialchars($request["street"]); ?></p>
                    <p class="details-description"><strong>رقم المبنى:</strong> <?php echo htmlspecialchars($request["building_number"]); ?></p>
                    <p class="details-description"><strong>تاريخ الاستلام:</strong> <?php echo htmlspecialchars($request["pickup_date"]); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($request["delivery_method"] === "dropoff"): ?>
                <div class="details-description-box">
                    <h3 class="details-subtitle">بيانات الفرع</h3>
                    <p class="details-description"><strong>الفرع المختار:</strong> <?php echo htmlspecialchars($request["branch_name"]); ?></p>
                </div>
            <?php endif; ?>

            <div class="details-actions">
                <a href="history.php" class="secondary-btn details-btn">العودة إلى تبرعاتي</a>

                <?php if ($request["status"] === "pending"): ?>
                    <a href="edit-request.php?id=<?php echo $request["id"]; ?>" class="main-btn details-btn">تعديل التبرع</a>

                    <a href="delete-request.php?id=<?php echo $request["id"]; ?>"
                       class="danger-btn details-btn"
                       onclick="return confirm('هل أنت متأكد من حذف الطلب؟');">
                        حذف التبرع
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

</body>
</html>