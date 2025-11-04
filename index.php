<?php
// เริ่ม session เพื่อดึงข้อมูลที่เก็บไว้
session_start();

// ตรวจสอบว่าล็อกอินมารึยัง
// ถ้ายังไม่ได้ล็อกอิน (ไม่มี $_SESSION['logged_in']) ให้เด้งกลับไปหน้า login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 3. สั่งเบราว์เซอร์ห้ามจำ (Cache Control) - นี่คือส่วนที่เพิ่มเข้ามา
// สั่งว่าห้ามเก็บหน้านี้ไว้ใน Cache เลย
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// คำสั่งเพิ่มเติมสำหรับเบราว์เซอร์เก่าๆ
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0"); // ตั้งวันหมดอายุเป็นอดีตไปเลย

// ดึงข้อมูลผู้ใช้ที่เก็บไว้ใน session ตอนล็อกอินสำเร็จ
$user = $_SESSION['user_data'];
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>หน้าหลัก - ระบบ Stroke Care</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        /* Custom CSS เพื่อให้ได้สไตล์แบบในรูปตัวอย่าง
        */

        /* 1. พื้นหลังสีเทาอ่อนแบบในรูป */
        body {
            background-color: #f4f7f6;
        }

        /* 2. สไตล์ Top Navbar ให้เป็นสีน้ำเงิน/ม่วง แบบในรูป */
        .navbar-custom {
            background-color: #4a559d;
            /* สีม่วงน้ำเงินจากรูป (โดยประมาณ) */
        }

        /* 3. สไตล์ของ "การ์ด" เมนู */
        .nav-card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            /* เงาจางๆ */
            transition: all 0.3s ease;
            /* ทำให้ขยับได้อย่างนุ่มนวล */
            display: flex;
            /* จัดไอคอนกับข้อความให้อยู่แนวเดียวกัน */
            align-items: center;
            padding: 24px;
            text-decoration: none;
            color: #333;
            height: 100%;
            /* ทำให้การ์ดสูงเท่ากัน */
        }

        /* 4. สไตล์เมื่อเอาเมาส์ไปชี้ */
        .nav-card:hover {
            transform: translateY(-5px);
            /* ขยับขึ้นเล็กน้อย */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* 5. วงกลมไอคอน */
        .nav-card .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 24px;
        }

        /* สีของไอคอน (แบ่งสีให้สวยงาม) */
        .bg-icon-1 {
            background-color: #e3f2fd;
            color: #1e88e5;
        }

        /* สีฟ้า */
        .bg-icon-2 {
            background-color: #e8f5e9;
            color: #43a047;
        }

        /* สีเขียว */
        .bg-icon-3 {
            background-color: #fff3e0;
            color: #fb8c00;
        }

        /* สีส้ม */
        .bg-icon-4 {
            background-color: #fce4ec;
            color: #d81b60;
        }

        /* สีชมพู */
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-brain me-2"></i> ระบบส่งต่อผู้ป่วยโรคหลอดเลือดสมอง (Stroke)
            </a>
            <div class="d-flex">
                <span class="navbar-text text-white d-flex align-items-center">

                    <i class="fas fa-user-circle fa-2x me-3"></i>

                    <span>
                        <strong>ชื่อ-สกุล:</strong> <?php echo htmlspecialchars($user['HR_FNAME']); ?>
                    </span>

                </span>

            </div>

        </div>
    </nav>

    <div class="container mt-5">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="fas fa-home me-1"></i>
                    หน้าแรก
                </li>
            </ol>
        </nav>

        <h3 class="mb-4">เมนูหลัก (Main Menu)</h3>

        <div class="row g-4">

            <div class="col-lg-6">
                <a href="form.php" class="nav-card">
                    <div class="icon-circle bg-icon-1">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <h5>1. บันทึกข้อมูลแรกรับ</h5>
                        <p class="mb-0 text-muted">บันทึกข้อมูลผู้ป่วยใหม่, ประวัติ, และการประเมินเบื้องต้น</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-6">
                <a href="diagnosis_form.php" class="nav-card">
                    <div class="icon-circle bg-icon-2">
                        <i class="bi bi-hospital"></i>
                    </div>
                    <div>
                        <h5>2. ER</h5>
                        <p class="mb-0 text-muted">บันทึก "ผลลัพธ์" จากการสแกนสมอง และ "คะแนน" ที่อ่านได้จากภาพ</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-6">
                <a href="OR_Procedure_Form.php" class="nav-card">
                    <div class="icon-circle bg-icon-3">
                        <i class="bi bi-scissors"></i>
                    </div>
                    <div>
                        <h5>3. OR Procedure Form</h5>
                        <p class="mb-0 text-muted">"ฟอร์มบันทึกการรักษา" หรือ "ใบบันทึกหัตถการ/ผ่าตัด"</p>
                    </div>
                </a>
            </div>

            <div class="col-lg-6">
                <a href="ward.php" class="nav-card">
                    <div class="icon-circle bg-icon-4">
                        <i class="bi bi-hospital"></i>
                    </div>
                    <div>
                        <h5>4. ward</h5>
                        <p class="mb-0 text-muted">เป็น "Flowsheet เฝ้าระวัง" หรือ "ใบบันทึกข้างเตียง" แบบดิจิทัล</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-6">
                <a href="follow.php" class="nav-card">
                    <div class="icon-circle bg-icon-4">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <h5>5. Follow-up</h5>
                        <p class="mb-0 text-muted">บันทึกภาวะแทรกซ้อน และคะแนน MRS หลังการรักษา</p>
                    </div>
                </a>
            </div>
            <div class="d-grid gap-2 d-md-block">
            <a href="logout.php" class="btn btn-danger">
                <i class="bi bi-box-arrow-right "></i> ออกจากระบบ
            </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>