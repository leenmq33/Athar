<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: admin-cases.php");
    exit;
}

$case_id = (int) $_GET["id"];

/* التحقق أن الحالة موجودة */
$case_sql = "SELECT * FROM donation_cases WHERE id = $case_id LIMIT 1";
$case_result = mysqli_query($conn, $case_sql);

if (!$case_result || mysqli_num_rows($case_result) == 0) {
    die("الحالة غير موجودة.");
}

/*
   نمنع الحذف فقط إذا كانت الحالة مرتبطة بطلب فعّال:
   pending أو accepted أو completed
*/
$check_sql = "SELECT COUNT(*) AS total
              FROM donation_requests
              WHERE case_id = $case_id
              AND status IN ('pending', 'accepted', 'completed')";

$check_result = mysqli_query($conn, $check_sql);

if (!$check_result) {
    die("خطأ أثناء التحقق من الطلبات: " . mysqli_error($conn));
}

$check_row = mysqli_fetch_assoc($check_result);

if ((int)$check_row["total"] > 0) {
    die("لا يمكن حذف هذه الحالة لأنها مرتبطة بطلب فعّال.");
}

/*
   نفك ارتباط الطلبات المرفوضة بالحالة
   حتى لا يمنعنا الـ foreign key من حذف الحالة
*/
$unlink_rejected_sql = "UPDATE donation_requests
                        SET case_id = NULL
                        WHERE case_id = $case_id
                        AND status = 'rejected'";

if (!mysqli_query($conn, $unlink_rejected_sql)) {
    die("حدث خطأ أثناء فك ارتباط الطلبات المرفوضة: " . mysqli_error($conn));
}

/* تنفيذ الحذف */
$delete_sql = "DELETE FROM donation_cases WHERE id = $case_id";

if (mysqli_query($conn, $delete_sql)) {
    header("Location: admin-cases.php");
    exit;
} else {
    die("حدث خطأ أثناء حذف الحالة: " . mysqli_error($conn));
}
?>