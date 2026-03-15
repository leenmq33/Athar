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

$branches = [
    "مركز أثر – فرع شمال الرياض (حي النرجس – طريق الأمير فيصل بن بندر)",
    "مركز أثر – فرع وسط الرياض (حي الملز – طريق صلاح الدين الأيوبي)",
    "مركز أثر – فرع جنوب الرياض (حي الشفا – طريق ديراب)"
];

$categories = ["تعليم", "أثاث", "ملابس", "أجهزة إلكترونية", "ألعاب", "أخرى"];

$message = "";
$message_type = "";

/* جلب الطلب والتأكد أنه يخص المستخدم وحالته pending */
$sql = "SELECT * FROM donation_requests
        WHERE id = $request_id
        AND user_id = $user_id
        AND status = 'pending'
        LIMIT 1";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("لا يمكن تعديل هذا الطلب.");
}

$request = mysqli_fetch_assoc($result);

/* هل الطلب مرتبط بحالة؟ */
$is_case_based = !empty($request["case_id"]);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $delivery_method = trim($_POST["delivery_method"] ?? "");

    /* القيم الأساسية */
    $title = $request["title"];
    $description = $request["description"];
    $category = $request["category"];
    $other_category = $request["other_category"];
    $image_path = $request["image_path"];

    /* إذا كان الطلب مستقلًا، اسمح بتعديل بياناته */
    if (!$is_case_based) {
        $title = trim($_POST["title"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $category = trim($_POST["category"] ?? "");
        $other_category = trim($_POST["other_category"] ?? "");

        if (empty($title) || empty($description) || empty($category)) {
            $message = "يرجى تعبئة جميع الحقول المطلوبة.";
            $message_type = "error";
        }

        if ($category === "أخرى" && empty($other_category)) {
            $message = "يرجى كتابة تفاصيل التصنيف.";
            $message_type = "error";
        }

        if ($category !== "أخرى") {
            $other_category = null;
        }

        /* رفع صورة جديدة اختياريًا */
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
    }

    $branch_name = null;
    $city = null;
    $street = null;
    $building_number = null;
    $pickup_date = null;

    if (empty($delivery_method)) {
        $message = "يرجى اختيار طريقة التسليم.";
        $message_type = "error";
    } else {
        if ($delivery_method === "dropoff") {
            $branch_name = trim($_POST["branch_name"] ?? "");

            if (empty($branch_name)) {
                $message = "يرجى اختيار الفرع.";
                $message_type = "error";
            }
        } elseif ($delivery_method === "pickup") {
            $city = trim($_POST["city"] ?? "");
            $street = trim($_POST["street"] ?? "");
            $building_number = trim($_POST["building_number"] ?? "");
            $pickup_date = trim($_POST["pickup_date"] ?? "");

            if (empty($city) || empty($street) || empty($building_number) || empty($pickup_date)) {
                $message = "يرجى تعبئة جميع بيانات الاستلام من المنزل.";
                $message_type = "error";
            }
        } else {
            $message = "طريقة التسليم غير صحيحة.";
            $message_type = "error";
        }
    }

    if (empty($message)) {
        $title_safe = mysqli_real_escape_string($conn, $title);
        $description_safe = mysqli_real_escape_string($conn, $description);
        $category_safe = mysqli_real_escape_string($conn, $category);
        $delivery_method_safe = mysqli_real_escape_string($conn, $delivery_method);

        $other_category_value = !empty($other_category)
            ? "'" . mysqli_real_escape_string($conn, $other_category) . "'"
            : "NULL";

        $image_path_value = !empty($image_path)
            ? "'" . mysqli_real_escape_string($conn, $image_path) . "'"
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

        $update_sql = "UPDATE donation_requests SET
            title = '$title_safe',
            description = '$description_safe',
            category = '$category_safe',
            other_category = $other_category_value,
            image_path = $image_path_value,
            delivery_method = '$delivery_method_safe',
            branch_name = $branch_name_value,
            city = $city_value,
            street = $street_value,
            building_number = $building_value,
            pickup_date = $pickup_date_value,
            updated_at = NOW()
            WHERE id = $request_id
            AND user_id = $user_id
            AND status = 'pending'";

        if (mysqli_query($conn, $update_sql)) {
            $message = "تم تعديل الطلب بنجاح.";
            $message_type = "success";

            /* إعادة جلب البيانات بعد التحديث */
            $refresh_sql = "SELECT * FROM donation_requests
                            WHERE id = $request_id
                            AND user_id = $user_id
                            AND status = 'pending'
                            LIMIT 1";
            $refresh_result = mysqli_query($conn, $refresh_sql);

            if ($refresh_result && mysqli_num_rows($refresh_result) > 0) {
                $request = mysqli_fetch_assoc($refresh_result);
                $is_case_based = !empty($request["case_id"]);
            }
        } else {
            $message = "حدث خطأ أثناء تعديل الطلب: " . mysqli_error($conn);
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
    <title>تعديل الطلب - أثر</title>
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
    <div class="auth-card request-form-card">
        <h1 class="auth-title">تعديل الطلب</h1>
        <p class="auth-subtitle">
            <?php if ($is_case_based): ?>
                يمكنك تعديل طريقة التسليم فقط لهذا الطلب المرتبط بحالة.
            <?php else: ?>
                يمكنك تعديل بيانات الطلب وطريقة التسليم.
            <?php endif; ?>
        </p>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="auth-form" enctype="multipart/form-data">

            <?php if (!$is_case_based): ?>
                <div class="input-group">
                    <label for="title">العنوان</label>
                    <input type="text" id="title" name="title" placeholder="ادخل العنوان"
                           value="<?php echo htmlspecialchars($request["title"]); ?>">
                </div>

                <div class="input-group">
                    <label for="description">الوصف</label>
                    <textarea id="description" name="description" class="custom-textarea" placeholder="اكتب وصف التبرع"><?php echo htmlspecialchars($request["description"]); ?></textarea>
                </div>

                <div class="input-group">
                    <label for="image">تغيير الصورة (اختياري)</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

                <div class="input-group">
                    <label for="category">التصنيف</label>
                    <select name="category" id="category" class="custom-select" onchange="toggleOtherCategory()">
                        <option value="">اختر التصنيف</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo ($request["category"] === $cat) ? "selected" : ""; ?>>
                                <?php echo $cat; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="input-group" id="other_category_group" style="display:none;">
                    <label for="other_category">تفاصيل التصنيف</label>
                    <input type="text" id="other_category" name="other_category" placeholder="اكتب التصنيف"
                           value="<?php echo htmlspecialchars($request["other_category"] ?? ""); ?>">
                </div>
            <?php endif; ?>

            <div class="input-group">
                <label for="delivery_method">طريقة التسليم</label>
                <select name="delivery_method" id="delivery_method" onchange="toggleDeliveryFields()" class="custom-select">
                    <option value="">اختر الطريقة</option>
                    <option value="pickup" <?php echo ($request["delivery_method"] === "pickup") ? "selected" : ""; ?>>
                        استلام من المنزل
                    </option>
                    <option value="dropoff" <?php echo ($request["delivery_method"] === "dropoff") ? "selected" : ""; ?>>
                        تسليم للفرع
                    </option>
                </select>
            </div>

            <div id="pickup_fields" style="display:none;">
                <div class="input-group">
                    <label for="city">المدينة</label>
                    <input type="text" id="city" name="city" placeholder="ادخل المدينة"
                           value="<?php echo htmlspecialchars($request["city"] ?? ""); ?>">
                </div>

                <div class="input-group">
                    <label for="street">الشارع</label>
                    <input type="text" id="street" name="street" placeholder="ادخل الشارع"
                           value="<?php echo htmlspecialchars($request["street"] ?? ""); ?>">
                </div>

                <div class="input-group">
                    <label for="building_number">رقم المبنى</label>
                    <input type="text" id="building_number" name="building_number" placeholder="ادخل رقم المبنى"
                           value="<?php echo htmlspecialchars($request["building_number"] ?? ""); ?>">
                </div>

                <div class="input-group">
                    <label for="pickup_date">تاريخ الاستلام</label>
                    <input type="date" id="pickup_date" name="pickup_date"
                           value="<?php echo htmlspecialchars($request["pickup_date"] ?? ""); ?>">
                </div>
            </div>

            <div id="dropoff_fields" style="display:none;">
                <div class="input-group">
                    <label for="branch_name">اختر الفرع</label>
                    <select name="branch_name" id="branch_name" class="custom-select">
                        <option value="">اختر الفرع</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?php echo htmlspecialchars($branch); ?>"
                                <?php echo (($request["branch_name"] ?? "") === $branch) ? "selected" : ""; ?>>
                                <?php echo htmlspecialchars($branch); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="details-actions" style="margin-top: 10px;">
                <a href="history.php" class="secondary-btn details-btn">العودة إلى تبرعاتي</a>
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

function toggleDeliveryFields() {
    const deliveryMethod = document.getElementById("delivery_method").value;
    const pickupFields = document.getElementById("pickup_fields");
    const dropoffFields = document.getElementById("dropoff_fields");

    pickupFields.style.display = "none";
    dropoffFields.style.display = "none";

    if (deliveryMethod === "pickup") {
        pickupFields.style.display = "block";
    } else if (deliveryMethod === "dropoff") {
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