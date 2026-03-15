<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: history.php");
    exit;
}

$request_id = (int) $_GET["id"];
$user_id = (int) $_SESSION["user_id"];

/* جلب الطلب أولًا */
$sql = "SELECT id, case_id, image_path, status
        FROM donation_requests
        WHERE id = $request_id
        AND user_id = $user_id
        AND status = 'pending'
        LIMIT 1";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: history.php");
    exit;
}

$request = mysqli_fetch_assoc($result);
$case_id = $request["case_id"];
$image_path = $request["image_path"];

/*
    إذا كان الطلب مستقلًا:
    - نحذف الصورة إن وجدت

    إذا كان الطلب مرتبطًا بحالة:
    - لا نحذف الصورة
*/
if (empty($case_id) && !empty($image_path) && file_exists($image_path)) {
    unlink($image_path);
}

/* حذف الطلب */
$delete_sql = "DELETE FROM donation_requests
               WHERE id = $request_id
               AND user_id = $user_id
               AND status = 'pending'";

if (mysqli_query($conn, $delete_sql)) {

    /* إذا كان الطلب مرتبطًا بحالة، أعد تفعيل الحالة */
    if (!empty($case_id)) {
        $case_id = (int) $case_id;

        $reactivate_sql = "UPDATE donation_cases
                           SET is_active = 1,
                               updated_at = NOW()
                           WHERE id = $case_id";

        mysqli_query($conn, $reactivate_sql);
    }

    header("Location: history.php");
    exit;
} else {
    die("حدث خطأ أثناء حذف الطلب: " . mysqli_error($conn));
}
?>
