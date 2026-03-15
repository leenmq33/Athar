<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];

$branches = [
    "مركز أثر – فرع شمال الرياض (حي النرجس – طريق الأمير فيصل بن بندر)",
    "مركز أثر – فرع وسط الرياض (حي الملز – طريق صلاح الدين الأيوبي)",
    "مركز أثر – فرع جنوب الرياض (حي الشفا – طريق ديراب)"
];

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $other_category = trim($_POST["other_category"] ?? "");
    $delivery_method = trim($_POST["delivery_method"] ?? "");

    $branch_name = null;
    $city = null;
    $street = null;
    $building_number = null;
    $pickup_date = null;
    $image_path = null;

    if (empty($title) || empty($description) || empty($category) || empty($delivery_method)) {
        $message = "يرجى تعبئة جميع الحقول المطلوبة.";
        $message_type = "error";
    } else {
        if ($category === "أخرى" && empty($other_category)) {
            $message = "يرجى كتابة تفاصيل التصنيف.";
            $message_type = "error";
        }

        if ($delivery_method === "dropoff") {
            $branch_name = trim($_POST["branch_name"] ?? "");
            if (empty($branch_name)) {
                $message = "يرجى اختيار الفرع.";
                $message_type = "error";
            }
        }

        if ($delivery_method === "pickup") {
            $city = trim($_POST["city"] ?? "");
            $street = trim($_POST["street"] ?? "");
            $building_number = trim($_POST["building_number"] ?? "");
            $pickup_date = trim($_POST["pickup_date"] ?? "");

            if (empty($city) || empty($street) || empty($building_number) || empty($pickup_date)) {
                $message = "يرجى تعبئة جميع بيانات الاستلام من المنزل.";
                $message_type = "error";
            }
        }
    }

    if (empty($message)) {
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] === 0) {
            $upload_dir = "uploads/requests/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_name = time() . "_" . basename($_FILES["image"]["name"]);
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            }
        }

        $title_safe = mysqli_real_escape_string($conn, $title);
        $description_safe = mysqli_real_escape_string($conn, $description);
        $category_safe = mysqli_real_escape_string($conn, $category);
        $delivery_method_safe = mysqli_real_escape_string($conn, $delivery_method);

        $other_category_value = !empty($other_category)
            ? "'" . mysqli_real_escape_string($conn, $other_category) . "'"
            : "NULL";

        $branch_name_value = !empty($branch_name)
            ? "'" . mysqli_real_escape_string($conn, $branch_name) . "'"
            : "NULL";

        $city_value = !empty($city)
            ? "'" . mysqli_real_escape_string($conn, $city) . "'"
            : "NULL";

        $street_value = !empty($street)
            ? "'" . mysqli_real_escape_string($conn, $street) . "'"
            : "NULL";

        $building_value = !empty($building_number)
            ? "'" . mysqli_real_escape_string($conn, $building_number) . "'"
            : "NULL";

        $pickup_date_value = !empty($pickup_date)
            ? "'" . mysqli_real_escape_string($conn, $pickup_date) . "'"
            : "NULL";

        $image_path_value = !empty($image_path)
            ? "'" . mysqli_real_escape_string($conn, $image_path) . "'"
            : "NULL";

        $sql = "INSERT INTO donation_requests (
                    user_id, case_id, title, category, other_category, description, image_path,
                    status, delivery_method, branch_name, city, street, building_number,
                    pickup_date, created_at
                ) VALUES (
                    $user_id, NULL, '$title_safe', '$category_safe', $other_category_value, '$description_safe', $image_path_value,
                    'pending', '$delivery_method_safe', $branch_name_value, $city_value, $street_value, $building_value,
                    $pickup_date_value, NOW()
                )";

        if (mysqli_query($conn, $sql)) {
            header("Location: history.php");
            exit;
        } else {
            $message = "حدث خطأ أثناء إرسال الطلب: " . mysqli_error($conn);
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تبرع - أثر</title>
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
            <a href="add-request.php" class="nav-link active">تبرع</a>
            <a href="history.php" class="nav-link">تبرعاتي</a>
            <a href="profile.php" class="nav-link">الملف الشخصي</a>
            <a href="logout.php" class="nav-link logout-link">تسجيل الخروج</a>
        </nav>
    </div>
</header>

<main class="home-main">
    <div class="auth-card request-form-card">
        <h1 class="auth-title">تبرع</h1>
        <p class="auth-subtitle">أدخل تفاصيل التبرع ثم اختر طريقة التسليم المناسبة</p>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="auth-form" enctype="multipart/form-data">

            <div class="input-group">
                <label for="title">العنوان</label>
                <input type="text" id="title" name="title" placeholder="ادخل العنوان" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
            </div>

            <div class="input-group">
                <label for="description">الوصف</label>
                <textarea id="description" name="description" class="custom-textarea" placeholder="اكتب وصف التبرع"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>

            <div class="input-group">
                <label for="image">إضافة صورة (اختياري)</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <div class="input-group">
                <label for="category">التصنيف</label>
                <select name="category" id="category" class="custom-select" onchange="toggleOtherCategory()">
                    <option value="">اختر التصنيف</option>
                    <?php
                    $categories = ["تعليم", "أثاث", "ملابس", "أجهزة إلكترونية", "ألعاب", "أخرى"];
                    $selectedCategory = $_POST['category'] ?? '';
                    foreach ($categories as $cat) {
                        $selected = ($selectedCategory === $cat) ? "selected" : "";
                        echo "<option value=\"$cat\" $selected>$cat</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="input-group" id="other_category_group" style="display:none;">
                <label for="other_category">تفاصيل التصنيف</label>
                <input type="text" id="other_category" name="other_category" placeholder="اكتب التصنيف" value="<?php echo htmlspecialchars($_POST['other_category'] ?? ''); ?>">
            </div>

            <div class="input-group">
                <label for="delivery_method">طريقة التسليم</label>
                <select name="delivery_method" id="delivery_method" class="custom-select" onchange="toggleDeliveryFields()">
                    <option value="">اختر الطريقة</option>
                    <option value="pickup" <?php echo (($_POST['delivery_method'] ?? '') === 'pickup') ? 'selected' : ''; ?>>استلام من المنزل</option>
                    <option value="dropoff" <?php echo (($_POST['delivery_method'] ?? '') === 'dropoff') ? 'selected' : ''; ?>>تسليم للفرع</option>
                </select>
            </div>

            <div id="pickup_fields" style="display:none;">
                <div class="input-group">
                    <label for="city">المدينة</label>
                    <input type="text" id="city" name="city" placeholder="ادخل المدينة" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
                </div>

                <div class="input-group">
                    <label for="street">الشارع</label>
                    <input type="text" id="street" name="street" placeholder="ادخل الشارع" value="<?php echo htmlspecialchars($_POST['street'] ?? ''); ?>">
                </div>

                <div class="input-group">
                    <label for="building_number">رقم المبنى</label>
                    <input type="text" id="building_number" name="building_number" placeholder="ادخل رقم المبنى" value="<?php echo htmlspecialchars($_POST['building_number'] ?? ''); ?>">
                </div>

                <div class="input-group">
                    <label for="pickup_date">تاريخ الاستلام</label>
                    <input type="date" id="pickup_date" name="pickup_date" value="<?php echo htmlspecialchars($_POST['pickup_date'] ?? ''); ?>">
                </div>
            </div>

            <div id="dropoff_fields" style="display:none;">
                <div class="input-group">
                    <label for="branch_name">اختر الفرع</label>
                    <select name="branch_name" id="branch_name" class="custom-select">
                        <option value="">اختر الفرع</option>
                        <?php
                        $selectedBranch = $_POST['branch_name'] ?? '';
                        foreach ($branches as $branch) {
                            $selected = ($selectedBranch === $branch) ? "selected" : "";
                            echo "<option value=\"" . htmlspecialchars($branch) . "\" $selected>" . htmlspecialchars($branch) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="main-btn">إرسال الطلب</button>
        </form>
    </div>
</main>

<script>
function toggleOtherCategory() {
    const category = document.getElementById("category").value;
    const otherGroup = document.getElementById("other_category_group");
    otherGroup.style.display = (category === "أخرى") ? "block" : "none";
}

function toggleDeliveryFields() {
    const method = document.getElementById("delivery_method").value;
    const pickupFields = document.getElementById("pickup_fields");
    const dropoffFields = document.getElementById("dropoff_fields");

    pickupFields.style.display = "none";
    dropoffFields.style.display = "none";

    if (method === "pickup") {
        pickupFields.style.display = "block";
    } else if (method === "dropoff") {
        dropoffFields.style.display = "block";
    }
}

window.onload = function() {
    toggleOtherCategory();
    toggleDeliveryFields();
};
</script>

</body>
</html>