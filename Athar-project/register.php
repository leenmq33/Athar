<?php
require_once "config/db.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name  = trim($_POST["last_name"]);
    $email      = trim($_POST["email"]);
    $phone      = trim($_POST["phone"]);
    $password   = trim($_POST["password"]);

    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password)) {
        $message = "يرجى تعبئة جميع الحقول المطلوبة.";
        $message_type = "error";
    } else {
        $check_sql = "SELECT * FROM users WHERE email = '$email' OR phone = '$phone'";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            $message = "البريد الإلكتروني أو رقم الجوال مستخدم مسبقًا.";
            $message_type = "error";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (first_name, last_name, email, phone, password)
                    VALUES ('$first_name', '$last_name', '$email', '$phone', '$hashed_password')";

            if (mysqli_query($conn, $sql)) {
                $message = "تم إنشاء الحساب بنجاح.";
                $message_type = "success";
            } else {
                $message = "حدث خطأ أثناء إنشاء الحساب.";
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
    <title>إنشاء حساب - أثر</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">

    <div class="auth-wrapper">
        <div class="auth-card">

            <div class="logo-box">
                <img src="assets/images/logo.png" alt="شعار أثر" class="logo">
            </div>

            <h1 class="auth-title">إنشاء حساب جديد</h1>
            <p class="auth-subtitle">انضم إلى منصة أثر وابدأ رحلتك في العطاء</p>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <div class="input-group">
                    <label for="first_name">الاسم الأول</label>
                    <input type="text" id="first_name" name="first_name" placeholder="أدخل الاسم الأول">
                </div>

                <div class="input-group">
                    <label for="last_name">الاسم الأخير</label>
                    <input type="text" id="last_name" name="last_name" placeholder="أدخل الاسم الأخير">
                </div>

                <div class="input-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" placeholder="example@email.com">
                </div>

                <div class="input-group">
                    <label for="phone">رقم الجوال</label>
                    <input type="text" id="phone" name="phone" placeholder="05xxxxxxxx">
                </div>

                <div class="input-group">
                    <label for="password">كلمة المرور</label>
                    <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور">
                </div>

                <button type="submit" class="main-btn">تسجيل</button>
            </form>

            <p class="auth-footer">
                لديك حساب بالفعل؟
                <a href="login.php">تسجيل الدخول</a>
            </p>

        </div>
    </div>

</body>
</html>
