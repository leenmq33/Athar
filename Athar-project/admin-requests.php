<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$admin_id = (int) $_SESSION["admin_id"];
$message = "";
$message_type = "";

/* تحديث حالة الطلب */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $request_id = (int) ($_POST["request_id"] ?? 0);
    $action = trim($_POST["action"] ?? "");

    if ($request_id > 0 && !empty($action)) {
        $check_sql = "SELECT * FROM donation_requests WHERE id = $request_id LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $request = mysqli_fetch_assoc($check_result);
            $current_status = $request["status"];
            $case_id = $request["case_id"];

            $new_status = null;

            if ($action === "accept" && $current_status === "pending") {
                $new_status = "accepted";
            } elseif ($action === "reject" && $current_status === "pending") {
                $new_status = "rejected";
            } elseif ($action === "complete" && $current_status === "accepted") {
                $new_status = "completed";
            }

            if (!empty($new_status)) {
                $update_sql = "UPDATE donation_requests SET
                               status = '$new_status',
                               reviewed_by_admin_id = $admin_id,
                               updated_at = NOW()
                               WHERE id = $request_id";

                if (mysqli_query($conn, $update_sql)) {

                    /* إذا تم قبول طلب مرتبط بحالة، نجعل الحالة غير نشطة */
                    if ($new_status === "accepted" && !empty($case_id)) {
                        $case_id = (int) $case_id;

                        $update_case_sql = "UPDATE donation_cases
                                            SET is_active = 0,
                                                updated_at = NOW()
                                            WHERE id = $case_id";

                        mysqli_query($conn, $update_case_sql);
                    }

                    /* إذا تم رفض طلب مرتبط بحالة، نعيد تفعيل الحالة */
                    if ($new_status === "rejected" && !empty($case_id)) {
                        $case_id = (int) $case_id;

                        $update_case_sql = "UPDATE donation_cases
                                            SET is_active = 1,
                                                updated_at = NOW()
                                            WHERE id = $case_id";

                        mysqli_query($conn, $update_case_sql);
                    }

                    if ($new_status === "accepted") {
                        $message = "تم قبول الطلب بنجاح.";
                    } elseif ($new_status === "rejected") {
                        $message = "تم رفض الطلب بنجاح، وتمت إعادة تفعيل الحالة المرتبطة.";
                    } elseif ($new_status === "completed") {
                        $message = "تم تحديث الطلب إلى مكتمل.";
                    }

                    $message_type = "success";
                } else {
                    $message = "حدث خطأ أثناء تحديث الطلب: " . mysqli_error($conn);
                    $message_type = "error";
                }
            } else {
                $message = "الإجراء غير مسموح لهذه الحالة.";
                $message_type = "error";
            }
        } else {
            $message = "الطلب غير موجود.";
            $message_type = "error";
        }
    }
}

/* استعلامات الأقسام */
$pending_sql = "SELECT donation_requests.*, users.first_name, users.last_name
                FROM donation_requests
                INNER JOIN users ON donation_requests.user_id = users.id
                WHERE donation_requests.status = 'pending'
                ORDER BY donation_requests.created_at DESC";

$accepted_sql = "SELECT donation_requests.*, users.first_name, users.last_name
                 FROM donation_requests
                 INNER JOIN users ON donation_requests.user_id = users.id
                 WHERE donation_requests.status = 'accepted'
                 ORDER BY donation_requests.created_at DESC";

$finished_sql = "SELECT donation_requests.*, users.first_name, users.last_name
                 FROM donation_requests
                 INNER JOIN users ON donation_requests.user_id = users.id
                 WHERE donation_requests.status IN ('rejected', 'completed')
                 ORDER BY donation_requests.created_at DESC";

$pending_result = mysqli_query($conn, $pending_sql);
$accepted_result = mysqli_query($conn, $accepted_sql);
$finished_result = mysqli_query($conn, $finished_sql);

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
    <title>إدارة الطلبات - أثر</title>
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
            <a href="admin-requests.php" class="nav-link active">الطلبات</a>
            <a href="admin-profile.php" class="nav-link">الملف الشخصي</a>
            <a href="admin-logout.php" class="nav-link logout-link">تسجيل الخروج</a>
        </nav>
    </div>
</header>

<main class="home-main">
    <h1 class="section-title" style="margin-bottom: 20px;">إدارة الطلبات</h1>

    <?php if (!empty($message)): ?>
        <div class="message <?php echo $message_type; ?>" style="max-width: 700px; margin-bottom: 20px;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- القسم الأول: طلبات بانتظار القبول -->
    <section style="margin-bottom: 34px;">
        <h2 class="section-title" style="font-size: 24px; margin-bottom: 16px;">طلبات بانتظار القبول</h2>

        <?php if ($pending_result && mysqli_num_rows($pending_result) > 0): ?>
            <div class="history-grid">
                <?php while ($row = mysqli_fetch_assoc($pending_result)): ?>
                    <?php
                        $status = $row["status"];
                        $status_text = getStatusText($status);
                        $delivery_text = getDeliveryText($row["delivery_method"]);
                        $full_name = $row["first_name"] . " " . $row["last_name"];
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
                            <div class="progress-step active">قيد الانتظار</div>
                            <div class="progress-line"></div>
                            <div class="progress-step">مقبول</div>
                            <div class="progress-line"></div>
                            <div class="progress-step">مكتمل</div>
                        </div>

                        <p class="history-desc">
                            <?php echo htmlspecialchars(mb_strimwidth($row["description"], 0, 120, "...", "UTF-8")); ?>
                        </p>

                        <div class="history-meta">
                            <p><strong>اسم المستخدم:</strong> <?php echo htmlspecialchars($full_name); ?></p>
                            <p><strong>طريقة التسليم:</strong> <?php echo $delivery_text; ?></p>
                            <p><strong>تاريخ الطلب:</strong> <?php echo date("Y-m-d", strtotime($row["created_at"])); ?></p>

                            <?php if (!empty($row["case_id"])): ?>
                                <p><strong>نوع التبرع:</strong> مرتبط بحالة</p>
                            <?php else: ?>
                                <p><strong>نوع التبرع:</strong> تبرع مستقل</p>
                            <?php endif; ?>
                        </div>

                        <div class="history-buttons">
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?php echo $row["id"]; ?>">
                                <input type="hidden" name="action" value="accept">
                                <button type="submit" class="main-btn history-btn">قبول</button>
                            </form>

                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?php echo $row["id"]; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="danger-btn history-btn">رفض</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-history-card">
                <h3>لا توجد طلبات جديدة</h3>
                <p>لا يوجد حاليًا أي طلب بانتظار القبول أو الرفض.</p>
            </div>
        <?php endif; ?>
    </section>

    <!-- القسم الثاني: طلبات بانتظار الاكتمال -->
    <section style="margin-bottom: 34px;">
        <h2 class="section-title" style="font-size: 24px; margin-bottom: 16px;">طلبات بانتظار الاستلام </h2>

        <?php if ($accepted_result && mysqli_num_rows($accepted_result) > 0): ?>
            <div class="history-grid">
                <?php while ($row = mysqli_fetch_assoc($accepted_result)): ?>
                    <?php
                        $status = $row["status"];
                        $status_text = getStatusText($status);
                        $delivery_text = getDeliveryText($row["delivery_method"]);
                        $full_name = $row["first_name"] . " " . $row["last_name"];
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
                            <div class="progress-step active">قيد الانتظار</div>
                            <div class="progress-line active"></div>
                            <div class="progress-step active">مقبول</div>
                            <div class="progress-line"></div>
                            <div class="progress-step">مكتمل</div>
                        </div>

                        <p class="history-desc">
                            <?php echo htmlspecialchars(mb_strimwidth($row["description"], 0, 120, "...", "UTF-8")); ?>
                        </p>

                        <div class="history-meta">
                            <p><strong>اسم المستخدم:</strong> <?php echo htmlspecialchars($full_name); ?></p>
                            <p><strong>طريقة التسليم:</strong> <?php echo $delivery_text; ?></p>
                            <p><strong>تاريخ الطلب:</strong> <?php echo date("Y-m-d", strtotime($row["created_at"])); ?></p>

                            <?php if (!empty($row["case_id"])): ?>
                                <p><strong>نوع التبرع:</strong> مرتبط بحالة</p>
                            <?php else: ?>
                                <p><strong>نوع التبرع:</strong> تبرع مستقل</p>
                            <?php endif; ?>
                        </div>

                        <div class="history-buttons">
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?php echo $row["id"]; ?>">
                                <input type="hidden" name="action" value="complete">
                                <button type="submit" class="secondary-btn history-btn">مكتمل</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-history-card">
                <h3>لا توجد طلبات مقبولة حاليًا</h3>
                <p>لا توجد طلبات مقبولة تنتظر الاستلام </p>
            </div>
        <?php endif; ?>
    </section>

    <!-- القسم الثالث: الطلبات المنتهية -->
    <section>
        <h2 class="section-title" style="font-size: 24px; margin-bottom: 16px;">الطلبات المنتهية</h2>

        <?php if ($finished_result && mysqli_num_rows($finished_result) > 0): ?>
            <div class="history-grid">
                <?php while ($row = mysqli_fetch_assoc($finished_result)): ?>
                    <?php
                        $status = $row["status"];
                        $status_text = getStatusText($status);
                        $delivery_text = getDeliveryText($row["delivery_method"]);
                        $full_name = $row["first_name"] . " " . $row["last_name"];
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
                                <div class="progress-step active">قيد الانتظار</div>
                                <div class="progress-line active"></div>
                                <div class="progress-step active">مقبول</div>
                                <div class="progress-line active"></div>
                                <div class="progress-step active">مكتمل</div>
                            <?php endif; ?>
                        </div>

                        <p class="history-desc">
                            <?php echo htmlspecialchars(mb_strimwidth($row["description"], 0, 120, "...", "UTF-8")); ?>
                        </p>

                        <div class="history-meta">
                            <p><strong>اسم المستخدم:</strong> <?php echo htmlspecialchars($full_name); ?></p>
                            <p><strong>طريقة التسليم:</strong> <?php echo $delivery_text; ?></p>
                            <p><strong>تاريخ الطلب:</strong> <?php echo date("Y-m-d", strtotime($row["created_at"])); ?></p>

                            <?php if (!empty($row["case_id"])): ?>
                                <p><strong>نوع التبرع:</strong> مرتبط بحالة</p>
                            <?php else: ?>
                                <p><strong>نوع التبرع:</strong> تبرع مستقل</p>
                            <?php endif; ?>
                        </div>

                        <div class="history-buttons">
                            <span class="locked-case-text">لا توجد إجراءات متاحة</span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-history-card">
                <h3>لا توجد طلبات منتهية</h3>
                <p>الطلبات المرفوضة أو المكتملة ستظهر هنا.</p>
            </div>
        <?php endif; ?>
    </section>
</main>

</body>
</html>