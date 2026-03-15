<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    die("الحالة غير موجودة.");
}

$case_id = (int) $_GET["id"];
$message = "";
$message_type = "";

$categories = ["تعليم", "أثاث", "ملابس", "أجهزة إلكترونية", "ألعاب", "أخرى"];

/* جلب الحالة */
$sql = "SELECT * FROM donation_cases WHERE id = $case_id LIMIT 1";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("الحالة غير موجودة.");
}

$case = mysqli_fetch_assoc($result);
$check_request_sql = "SELECT COUNT(*) AS total
                      FROM donation_requests
                      WHERE case_id = $case_id
                      AND status IN ('pending', 'accepted', 'completed')";
$check_request_result = mysqli_query($conn, $check_request_sql);
$check_request_row = mysqli_fetch_assoc($check_request_result);

if ($check_request_row["total"] > 0) {
    die("لا يمكن تعديل هذه الحالة لأنها مرتبطة بطلب تبرع.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $other_category = trim($_POST["other_category"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $is_active = isset($_POST["is_active"]) ? 1 : 0;

    if (empty($title) || empty($category) || empty($description)) {
        $message = "يرجى تعبئة جميع الحقول المطلوبة.";
        $message_type = "error";
    } else {
        if ($category === "أخرى" && empty($other_category)) {
            $message = "يرجى كتابة تفاصيل التصنيف.";
            $message_type = "error";
        }

        if ($category !== "أخرى") {
            $other_category = null;
        }

        $image_path = $case["image_path"];

        if (isset($_FILES["image"]) && $_FILES["image"]["error"] === 0) {
            $upload_dir = "uploads/cases/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_name = time() . "_" . basename($_FILES["image"]["name"]);
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            }
        }

        if (empty($message)) {
            $title_safe = mysqli_real_escape_string($conn, $title);
            $category_safe = mysqli_real_escape_string($conn, $category);
            $description_safe = mysqli_real_escape_string($conn, $description);

            $other_category_value = !empty($other_category)
                ? "'" . mysqli_real_escape_string($conn, $other_category) . "'"
                : "NULL";

            $image_path_value = !empty($image_path)
                ? "'" . mysqli_real_escape_string($conn, $image_path) . "'"
                : "NULL";

            $update_sql = "UPDATE donation_cases SET
                title = '$title_safe',
                category = '$category_safe',
                other_category = $other_category_value,
                description = '$description_safe',
                image_path = $image_path_value,
                is_active = $is_active,
                updated_at = NOW()
                WHERE id = $case_id";

            if (mysqli_query($conn, $update_sql)) {
                $message = "تم تعديل الحالة بنجاح.";
                $message_type = "success";

                $refresh_sql = "SELECT * FROM donation_cases WHERE id = $case_id LIMIT 1";
                $refresh_result = mysqli_query($conn, $refresh_sql);
                if ($refresh_result && mysqli_num_rows($refresh_result) > 0) {
                    $case = mysqli_fetch_assoc($refresh_result);
                }
            } else {
                $message = "حدث خطأ أثناء تعديل الحالة: " . mysqli_error($conn);
                $message_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل الحالة - أثر</title>
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
    <div class="auth-card request-form-card">
        <h1 class="auth-title">تعديل الحالة</h1>
        <p class="auth-subtitle">يمكنك تعديل بيانات الحالة وإدارة ظهورها للمستخدمين</p>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="auth-form" enctype="multipart/form-data">

            <div class="input-group">
                <label for="title">العنوان</label>
                <input type="text" id="title" name="title" placeholder="ادخل عنوان الحالة"
                       value="<?php echo htmlspecialchars($case["title"]); ?>">
            </div>

            <div class="input-group">
                <label for="category">التصنيف</label>
                <select name="category" id="category" class="custom-select" onchange="toggleOtherCategory()">
                    <option value="">اختر التصنيف</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo ($case["category"] === $cat) ? "selected" : ""; ?>>
                            <?php echo $cat; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="input-group" id="other_category_group" style="display:none;">
                <label for="other_category">تفاصيل التصنيف</label>
                <input type="text" id="other_category" name="other_category" placeholder="اكتب التصنيف"
                       value="<?php echo htmlspecialchars($case["other_category"] ?? ""); ?>">
            </div>

            <div class="input-group">
                <label for="description">الوصف</label>
                <textarea id="description" name="description" class="custom-textarea" placeholder="اكتب وصف الحالة"><?php echo htmlspecialchars($case["description"]); ?></textarea>
            </div>

            <div class="input-group">
                <label for="image">تغيير الصورة (اختياري)</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <div class="input-group">
                <label style="display:flex; align-items:center; gap:10px;">
                    <input type="checkbox" name="is_active" <?php echo ($case["is_active"] == 1) ? "checked" : ""; ?>>
                    الحالة نشطة وتظهر للمستخدمين
                </label>
            </div>

            <div class="details-actions" style="margin-top: 10px;">
                <a href="admin-cases.php" class="secondary-btn details-btn">العودة إلى الحالات</a>
                <button type="submit" class="main-btn details-btn">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</main>

<script>
function toggleOtherCategory() {
    const categoryElement = document.getElementById("category");
    const otherGroup = document.getElementById("other_category_group");

    if (!categoryElement || !otherGroup) return;
    otherGroup.style.display = (categoryElement.value === "أخرى") ? "block" : "none";
}

window.onload = function() {
    toggleOtherCategory();
};
</script>

</body>
</html>