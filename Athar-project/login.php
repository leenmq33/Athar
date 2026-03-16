<?php
session_start();
require_once "config/db.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if (empty($email) || empty($password)) {
        $message = "يرجى تعبئة جميع الحقول المطلوبة.";
        $message_type = "error";
    } else {
        $email_safe = mysqli_real_escape_string($conn, $email);

        /* أولًا: نبحث في جدول المستخدمين */
        $user_sql = "SELECT * FROM users WHERE email = '$email_safe' LIMIT 1";
        $user_result = mysqli_query($conn, $user_sql);

        if ($user_result && mysqli_num_rows($user_result) == 1) {
            $user = mysqli_fetch_assoc($user_result);

            if (password_verify($password, $user["password"])) {
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["first_name"] . " " . $user["last_name"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["role"] = "user";

                header("Location: home.php");
                exit;
            } else {
                $message = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
                $message_type = "error";
            }

        } else {
            /* ثانيًا: نبحث في جدول الأدمن */
            $admin_sql = "SELECT * FROM admins WHERE email = '$email_safe' LIMIT 1";
            $admin_result = mysqli_query($conn, $admin_sql);

            if ($admin_result && mysqli_num_rows($admin_result) == 1) {
                $admin = mysqli_fetch_assoc($admin_result);

                /* يدعم كلمة مرور مشفرة أو نص عادي للتجربة */
                if ($password === $admin["password"] || password_verify($password, $admin["password"])) {
                    $_SESSION["admin_id"] = $admin["id"];
                    $_SESSION["admin_name"] = $admin["first_name"] . " " . $admin["last_name"];
                    $_SESSION["admin_email"] = $admin["email"];
                    $_SESSION["role"] = "admin";

                    header("Location: admin-dashboard.php");
                    exit;
                } else {
                    $message = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
                    $message_type = "error";
                }

            } else {
                $message = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
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
    <title>تسجيل الدخول - أثر</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">

    <div class="auth-wrapper">
        <div class="auth-card">

            <div class="logo-box">
                <img src="assets/images/logo.png" alt="شعار أثر" class="logo">
            </div>

            <h1 class="auth-title">تسجيل الدخول</h1>
            <p class="auth-subtitle">أهلًا بك في منصة أثر</p>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <div class="input-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" placeholder="ادخل البريد الإلكتروني">
                </div>

                <div class="input-group">
                    <label for="password">كلمة المرور</label>
                    <input type="password" id="password" name="password" placeholder="ادخل كلمة المرور">
                </div>

                <button type="submit" class="main-btn">دخول</button>
            </form>

            <p class="auth-footer">
                ليس لديك حساب؟
                <a href="register.php">إنشاء حساب جديد</a>
            </p>

        </div>
    </div>

</body>
</html>
