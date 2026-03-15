<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";

$user_id = (int) $_SESSION["user_id"];
$message = "";
$message_type = "";

$sql = "SELECT * FROM users WHERE id = $user_id LIMIT 1";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("المستخدم غير موجود.");
}

$user = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"] ?? "");
    $last_name = trim($_POST["last_name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if (empty($first_name) || empty($last_name) || empty($phone)) {
        $message = "يرجى تعبئة جميع الحقول المطلوبة.";
        $message_type = "error";
    } else {
        $first_name_safe = mysqli_real_escape_string($conn, $first_name);
        $last_name_safe = mysqli_real_escape_string($conn, $last_name);
        $phone_safe = mysqli_real_escape_string($conn, $phone);

        $check_sql = "SELECT * FROM users
                      WHERE phone = '$phone_safe'
                      AND id != $user_id
                      LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $message = "رقم الجوال مستخدم مسبقًا.";
            $message_type = "error";
        } else {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $hashed_password_safe = mysqli_real_escape_string($conn, $hashed_password);

                $update_sql = "UPDATE users SET
                    first_name = '$first_name_safe',
                    last_name = '$last_name_safe',
                    phone = '$phone_safe',
                    password = '$hashed_password_safe',
                    updated_at = NOW()
                    WHERE id = $user_id";
            } else {
                $update_sql = "UPDATE users SET
                    first_name = '$first_name_safe',
                    last_name = '$last_name_safe',
                    phone = '$phone_safe',
                    updated_at = NOW()
                    WHERE id = $user_id";
            }

            if (mysqli_query($conn, $update_sql)) {
                $_SESSION["user_name"] = $first_name . " " . $last_name;

                $message = "تم تحديث الملف الشخصي بنجاح.";
                $message_type = "success";

                $refresh_sql = "SELECT * FROM users WHERE id = $user_id LIMIT 1";
                $refresh_result = mysqli_query($conn, $refresh_sql);
                if ($refresh_result && mysqli_num_rows($refresh_result) > 0) {
                    $user = mysqli_fetch_assoc($refresh_result);
                }
            } else {
                $message = "حدث خطأ أثناء التحديث: " . mysqli_error($conn);
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
    <title>تعديل الملف الشخصي - أثر</title>
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
            <a href="history.php" class="nav-link">تبرعاتي</a>
            <a href="profile.php" class="nav-link active">الملف الشخصي</a>
            <a href="logout.php" class="nav-link logout-link">تسجيل الخروج</a>
        </nav>
    </div>
</header>

<main class="home-main">
    <div class="auth-card request-form-card">
        <h1 class="auth-title">تعديل الملف الشخصي</h1>
        <p class="auth-subtitle">يمكنك تعديل بياناتك الشخصية من هنا</p>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="auth-form">
            <div class="input-group">
                <label for="first_name">الاسم الأول</label>
                <input type="text" id="first_name" name="first_name" placeholder="ادخل الاسم الأول"
                       value="<?php echo htmlspecialchars($user["first_name"]); ?>">
            </div>

            <div class="input-group">
                <label for="last_name">الاسم الأخير</label>
                <input type="text" id="last_name" name="last_name" placeholder="ادخل الاسم الأخير"
                       value="<?php echo htmlspecialchars($user["last_name"]); ?>">
            </div>

            <div class="input-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($user["email"]); ?>" disabled>
            </div>

            <div class="input-group">
                <label for="phone">رقم الجوال</label>
                <input type="text" id="phone" name="phone" placeholder="ادخل رقم الجوال"
                       value="<?php echo htmlspecialchars($user["phone"]); ?>">
            </div>

            <div class="input-group">
                <label for="password">كلمة المرور الجديدة (اختياري)</label>
                <input type="password" id="password" name="password" placeholder="اتركه فارغًا إذا لا تريد التغيير">
            </div>

            <div class="details-actions" style="margin-top: 10px;">
                <a href="profile.php" class="secondary-btn details-btn">العودة إلى الملف الشخصي</a>
                <button type="submit" class="main-btn details-btn">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</main>

</body>
</html>
