<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الرئيسية - أثر</title>
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
                <a href="home.php" class="nav-link active">الرئيسية</a>
                <a href="cases.php" class="nav-link">عرض الحالات</a>
                <a href="add-request.php" class="nav-link"> تبرع</a>
                <a href="history.php" class="nav-link">تبرعاتي</a>
                <a href="profile.php" class="nav-link">الملف الشخصي</a>
                <a href="logout.php" class="nav-link logout-link">تسجيل الخروج</a>
            </nav>
        </div>
    </header>

    <main class="home-main">
        <section class="hero-section">
            <div class="hero-text">
                <span class="hero-badge">منصة أثر</span>
                <h1 class="hero-title">معًا نصنع أثرًا يدوم</h1>
                <p class="hero-description">
                    منصة أثر تسهّل التبرع بطريقة منظمة وواضحة، 
                    وتمنح المستخدم تجربة مريحة لمشاركة الخير ومتابعة طلباته بكل سهولة.
                </p>

                <div class="hero-buttons">
                    <a href="cases.php" class="main-btn hero-btn">عرض الحالات</a>
                    <a href="add-request.php" class="secondary-btn hero-btn">إضافة تبرع</a>
                </div>
            </div>

            <div class="hero-media">

<div class="hero-slider">

    <div class="hero-slide active">
        <img src="assets/images/hero-1.jpg" class="hero-slide-image">

        <div class="hero-slide-overlay">
            <h3 class="hero-slide-title">أثر يوصل عطائك</h3>
            <p class="hero-slide-text">عبر أثر… ملابسك تصل لمن يحتاجها </p>
        </div>
    </div>

    <div class="hero-slide">
        <img src="assets/images/hero-2.jpg" class="hero-slide-image">

        <div class="hero-slide-overlay">
            <h3 class="hero-slide-title">أثر يصنع ابتسامة</h3>
            <p class="hero-slide-text">شارك ألعاب طفلك لتمنح طفلًا آخر لحظات من الفرح.</p>
        </div>
    </div>

    <div class="hero-slide">
        <img src="assets/images/hero-3.jpg" class="hero-slide-image">

        <div class="hero-slide-overlay">
            <h3 class="hero-slide-title">أثر يشارك المعرفة</h3>
            <p class="hero-slide-text">مع أثر… كتابك يصل لقارئ جديد</p>
        </div>
    </div>

    <!-- الأسهم -->
    <button class="hero-arrow hero-prev">&#10094;</button>
    <button class="hero-arrow hero-next">&#10095;</button>

    <!-- النقاط -->
    <div class="hero-dots">
        <span class="hero-dot active"></span>
        <span class="hero-dot"></span>
        <span class="hero-dot"></span>
    </div>

</div>

</div>
        </section>

        <section class="about-section">
            <div class="section-card">
                <h3 class="section-title">عن أثر</h3>
                <p class="section-text">
                    يهدف أثر إلى تسهيل الوصول إلى التبرعات العينية وتنظيمها، من خلال عرض الحالات
                    المتاحة، وتمكين المستخدم من إنشاء طلب تبرع مستقل أو التبرع لحالة موجودة،
                    مع متابعة الطلب من لحظة الإرسال حتى اكتماله.
                </p>
            </div>

            <div class="section-card">
                <h3 class="section-title">كيف يعمل؟</h3>
                <p class="section-text">
                    يمكنك استعراض الحالات، اختيار الحالة المناسبة، أو إضافة تبرع جديد من الصفحة الرئيسية.
                    بعد ذلك يتم تحديد طريقة التسليم، ثم متابعة حالة الطلب بسهولة من خلال سجل الطلبات.
                </p>
            </div>
        </section>
    </main>
    <script>

const slides=document.querySelectorAll(".hero-slide");
const dots=document.querySelectorAll(".hero-dot");

let index=0;

function showSlide(i){

slides.forEach(s=>s.classList.remove("active"));
dots.forEach(d=>d.classList.remove("active"));

slides[i].classList.add("active");
dots[i].classList.add("active");

index=i;
}

document.querySelector(".hero-next").onclick=()=>{
let i=(index+1)%slides.length;
showSlide(i);
}

document.querySelector(".hero-prev").onclick=()=>{
let i=(index-1+slides.length)%slides.length;
showSlide(i);
}

dots.forEach((dot,i)=>{
dot.onclick=()=>showSlide(i);
});

setInterval(()=>{
let i=(index+1)%slides.length;
showSlide(i);
},5000);

</script>

</body>
</html>