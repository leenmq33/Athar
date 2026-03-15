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

$categories = ["تعليم", "أثاث", "ملابس", "أجهزة إلكترونية", "ألعاب", "أخرى"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $other_category = trim($_POST["other_category"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if (empty($title) || empty($category) || empty($description)) {
        $message = "يرجى تعبئة جميع الحقول المطلوبة.";
        $message_type = "error";
    } else {
        if ($category === "أخرى" && empty($other_category)) {
            $message = "يرجى كتابة تفاصيل التصنيف.";
            $message_type = "error";
        }

        $image_path = null;

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

            $sql = "INSERT INTO donation_cases (
                        title, category, other_category, description, image_path,
                        is_active, created_by_admin_id, created_at
                    ) VALUES (
                        '$title_safe', '$category_safe', $other_category_value, '$description_safe', $image_path_value,
                        1, $admin_id, NOW()
                    )";

            if (mysqli_query($conn, $sql)) {
                header("Location: admin-cases.php");
                exit;
            } else {
                $message = "حدث خطأ أثناء إضافة الحالة: " . mysqli_error($conn);
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
    <title>إضافة حالة - أثر</title>
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
        <h1 class="auth-title">إضافة حالة جديدة</h1>
        <p class="auth-subtitle">أدخل بيانات الحالة التي ستظهر للمستخدمين</p>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="auth-form" enctype="multipart/form-data">

            <div class="input-group">
                <label for="title">العنوان</label>
                <input type="text" id="title" name="title" placeholder="ادخل عنوان الحالة"
                       value="<?php echo htmlspecialchars($_POST["title"] ?? ""); ?>">
            </div>

            <div class="input-group">
                <label for="category">التصنيف</label>
                <select name="category" id="category" class="custom-select" onchange="toggleOtherCategory()">
                    <option value="">اختر التصنيف</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo (($_POST["category"] ?? "") === $cat) ? "selected" : ""; ?>>
                            <?php echo $cat; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="input-group" id="other_category_group" style="display:none;">
                <label for="other_category">تفاصيل التصنيف</label>
                <input type="text" id="other_category" name="other_category" placeholder="اكتب التصنيف"
                       value="<?php echo htmlspecialchars($_POST["other_category"] ?? ""); ?>">
            </div>

            <div class="input-group">
                <label for="description">الوصف</label>
                <textarea id="description" name="description" class="custom-textarea" placeholder="اكتب وصف الحالة"><?php echo htmlspecialchars($_POST["description"] ?? ""); ?></textarea>
            </div>

            <div class="input-group">
                <label for="image">الصورة (اختياري)</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <div class="details-actions" style="margin-top: 10px;">
                <a href="admin-cases.php" class="secondary-btn details-btn">العودة إلى الحالات</a>
                <button type="submit" class="main-btn details-btn">إضافة الحالة</button>
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
