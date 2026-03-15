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
$user_id = $_SESSION["user_id"];

$branches = [
    "مركز أثر – فرع شمال الرياض (حي النرجس – طريق الأمير فيصل بن بندر)",
    "مركز أثر – فرع وسط الرياض (حي الملز – طريق صلاح الدين الأيوبي)",
    "مركز أثر – فرع جنوب الرياض (حي الشفا – طريق ديراب)"
];

$message = "";
$message_type = "";

/* جلب الحالة */
$case_sql = "SELECT * FROM donation_cases WHERE id = $case_id AND is_active = 1 LIMIT 1";
$case_result = mysqli_query($conn, $case_sql);

if (!$case_result || mysqli_num_rows($case_result) == 0) {
    die("الحالة غير موجودة أو لم تعد متاحة.");
}

$case = mysqli_fetch_assoc($case_result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $delivery_method = $_POST["delivery_method"] ?? "";

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
          

            if (
                empty($city) || empty($street) || empty($building_number) ||
                empty($pickup_date) 
            ) {
                $message = "يرجى تعبئة جميع بيانات الاستلام.";
                $message_type = "error";
            }
        }

        if (empty($message)) {
            $title = mysqli_real_escape_string($conn, $case["title"]);
            $category = mysqli_real_escape_string($conn, $case["category"]);
            $other_category = mysqli_real_escape_string($conn, $case["other_category"] ?? "");
            $description = mysqli_real_escape_string($conn, $case["description"]);
            $image_path = mysqli_real_escape_string($conn, $case["image_path"] ?? "");
            $delivery_method_safe = mysqli_real_escape_string($conn, $delivery_method);

            $branch_name_safe = $branch_name ? "'" . mysqli_real_escape_string($conn, $branch_name) . "'" : "NULL";
            $city_safe = $city ? "'" . mysqli_real_escape_string($conn, $city) . "'" : "NULL";
            $street_safe = $street ? "'" . mysqli_real_escape_string($conn, $street) . "'" : "NULL";
            $building_safe = $building_number ? "'" . mysqli_real_escape_string($conn, $building_number) . "'" : "NULL";
            $pickup_date_safe = $pickup_date ? "'" . mysqli_real_escape_string($conn, $pickup_date) . "'" : "NULL";
            $other_category_safe = !empty($other_category) ? "'" . $other_category . "'" : "NULL";
            $image_path_safe = !empty($image_path) ? "'" . $image_path . "'" : "NULL";

            $insert_sql = "
                INSERT INTO donation_requests (
                    user_id, case_id, title, category, other_category, description, image_path,
                    status, delivery_method, branch_name, city, street, building_number,
                    pickup_date, created_at
                ) VALUES (
                    $user_id, $case_id, '$title', '$category', $other_category_safe, '$description', $image_path_safe,
                    'pending', '$delivery_method_safe', $branch_name_safe, $city_safe, $street_safe, $building_safe,
                    $pickup_date_safe, NOW()
                )
            ";

            if (mysqli_query($conn, $insert_sql)) {
                $update_case_sql = "UPDATE donation_cases SET is_active = 0 WHERE id = $case_id";
                mysqli_query($conn, $update_case_sql);

                $message = "تم إرسال طلب التبرع بنجاح.";
                $message_type = "success";
            } else {
                $message = "حدث خطأ أثناء إرسال الطلب.";
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
    <title>طريقة التسليم - أثر</title>
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
    <div class="auth-card" style="max-width: 760px; margin: 0 auto;">
        <h1 class="auth-title">اختيار طريقة التسليم</h1>
        <p class="auth-subtitle">
            أنت الآن تتبرع للحالة:
            <strong><?php echo htmlspecialchars($case["title"]); ?></strong>
        </p>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label for="delivery_method">طريقة التسليم</label>
                <select name="delivery_method" id="delivery_method" onchange="toggleDeliveryFields()" class="custom-select">
                    <option value="">اختر طريقة التسليم</option>
                    <option value="pickup">استلام من المنزل </option>
                    <option value="dropoff">تسليم للفرع</option>
                </select>
            </div>

            <div id="pickup_fields" style="display:none;">
                <div class="input-group">
                    <label>المدينة</label>
                    <input type="text" name="city" placeholder="ادخل المدينة">
                </div>

                <div class="input-group">
                    <label>الشارع</label>
                    <input type="text" name="street" placeholder="ادخل الشارع">
                </div>

                <div class="input-group">
                    <label>رقم المبنى</label>
                    <input type="text" name="building_number" placeholder="ادخل رقم المبنى">
                </div>

                <div class="input-group">
                    <label>تاريخ الاستلام</label>
                    <input type="date" name="pickup_date">
                </div>

            </div>

            <div id="dropoff_fields" style="display:none;">
                <div class="input-group">
                    <label for="branch_name">اختر الفرع</label>
                    <select name="branch_name" id="branch_name" class="custom-select">
                        <option value="">اختر الفرع</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?php echo htmlspecialchars($branch); ?>">
                                <?php echo htmlspecialchars($branch); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="main-btn">إرسال الطلب</button>
        </form>
    </div>
</main>

<script>
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
</script>

</body>
</html>
