<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT donation_cases.*,

        (
            SELECT COUNT(*)
            FROM donation_requests
            WHERE donation_requests.case_id = donation_cases.id
            AND donation_requests.status = 'pending'
        ) AS pending_count,

        (
            SELECT COUNT(*)
            FROM donation_requests
            WHERE donation_requests.case_id = donation_cases.id
            AND donation_requests.status = 'accepted'
        ) AS accepted_count,

        (
            SELECT COUNT(*)
            FROM donation_requests
            WHERE donation_requests.case_id = donation_cases.id
            AND donation_requests.status = 'completed'
        ) AS completed_count

        FROM donation_cases
        ORDER BY donation_cases.created_at DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("خطأ في جلب الحالات: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الحالات - أثر</title>
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
            <a href="admin-cases.php" class="nav-link active">الحالات</a>
            <a href="admin-requests.php" class="nav-link">الطلبات</a>
            <a href="admin-profile.php" class="nav-link">الملف الشخصي</a>
            <a href="admin-logout.php" class="nav-link logout-link">تسجيل الخروج</a>
        </nav>
    </div>
</header>

<main class="home-main">
    <div class="admin-page-head">
        <div>
            <h1 class="section-title">إدارة الحالات</h1>
        </div>

        <a href="admin-add-case.php" class="main-btn add-case-btn">
            إضافة حالة جديدة
        </a>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>العنوان</th>
                        <th>التصنيف</th>
                        <th>الوصف</th>
                        <th>الحالة</th>
                        <th>العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <?php
                            $has_pending = (int)$row["pending_count"] > 0;
                            $has_accepted = (int)$row["accepted_count"] > 0;
                            $has_completed = (int)$row["completed_count"] > 0;

                            $case_status_text = "";
                            $case_status_class = "";

                            $operations_mode = "buttons"; // buttons أو text
                            $operations_text = "";
                            $operations_text_class = "";

                            if ($has_completed) {
                                $case_status_text = "مكتملة";
                                $case_status_class = "completed-status";

                                $operations_mode = "text";
                                $operations_text = "مكتملة";
                                $operations_text_class = "completed-action-text";
                            } elseif ($has_accepted) {
                                $case_status_text = "مقبولة";
                                $case_status_class = "accepted-status";

                                $operations_mode = "text";
                                $operations_text = "مقبولة";
                                $operations_text_class = "accepted-action-text";
                            } elseif ($has_pending) {
                                $case_status_text = "معلقة";
                                $case_status_class = "pending-status";

                                $operations_mode = "text";
                                $operations_text = "معلقة";
                                $operations_text_class = "pending-action-text";
                            } else {
                                if ((int)$row["is_active"] === 1) {
                                    $case_status_text = "نشطة";
                                    $case_status_class = "active-status";
                                } else {
                                    $case_status_text = "غير نشطة";
                                    $case_status_class = "inactive-status";
                                }

                                $operations_mode = "buttons";
                            }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row["title"]); ?></td>

                            <td>
                                <?php echo htmlspecialchars($row["category"]); ?>
                                <?php if ($row["category"] === "أخرى" && !empty($row["other_category"])): ?>
                                    <div class="admin-subtext">
                                        <?php echo htmlspecialchars($row["other_category"]); ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(mb_strimwidth($row["description"], 0, 70, "...", "UTF-8")); ?>
                            </td>

                            <td>
                                <?php if ($has_completed): ?>
                                    <span class="admin-status" style="background-color:#E6F2FF; color:#2E5C86;">
                                        مكتملة
                                    </span>

                                <?php elseif ($has_accepted): ?>
                                    <span class="admin-status" style="background-color:#E4F2E1; color:#2F5A2F;">
                                        مقبولة
                                    </span>

                                <?php elseif ($has_pending): ?>
                                    <span class="admin-status" style="background-color:#FFF3CD; color:#8A6D1F;">
                                        معلقة
                                    </span>

                                <?php elseif ((int)$row["is_active"] === 1): ?>
                                    <span class="admin-status active-status">
                                        نشطة
                                    </span>

                                <?php else: ?>
                                    <span class="admin-status inactive-status">
                                        غير نشطة
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="admin-actions">
                                <div class="actions-wrapper">
                                    <?php if ($operations_mode === "buttons"): ?>
                                        <a href="admin-edit-case.php?id=<?php echo $row["id"]; ?>" class="secondary-btn admin-table-btn">
                                            تعديل
                                        </a>

                                        <a href="admin-delete-case.php?id=<?php echo $row["id"]; ?>"
                                           class="danger-btn admin-table-btn"
                                           onclick="return confirm('هل أنت متأكد من حذف الحالة؟');">
                                            حذف
                                        </a>
                                    <?php else: ?>
                                        <span class="plain-action-text <?php echo $operations_text_class; ?>">
                                            <?php echo $operations_text; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-history-card">
            <h3>لا توجد حالات حتى الآن</h3>
            <p>ابدأ بإضافة أول حالة لتظهر للمستخدمين.</p>
            <a href="admin-add-case.php" class="main-btn empty-history-btn">إضافة حالة جديدة</a>
        </div>
    <?php endif; ?>
</main>

</body>
</html>
