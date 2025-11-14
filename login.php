<?php
// 1. เริ่ม session (ต้องอยู่อันแรกเสมอ)
session_start();

// 2. 🛡️ โค้ดยาม (เช็กว่าล็อกอินอยู่)
// ถ้าล็อกอินอยู่แล้ว ให้เด้งไป index ทันที
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: index.php");
    exit;
}

// 3. 🧠 คำสั่งห้ามจำ (Cache Control)
// บังคับให้เบราว์เซอร์ต้องมาถามเซิร์ฟเวอร์ใหม่ทุกครั้งที่เปิดหน้านี้
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0"); 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* จัดหน้าให้อยู่กลางจอ */
        body,
        html {
            height: 100%;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            /* สีพื้นหลังเทาอ่อน */
        }
    </style>
    <script type="text/javascript">
        // 1. เมื่อหน้านี้ (login.php) โหลดเสร็จ
        // เราจะแอบเพิ่ม "หน้าปลอม" เข้าไปใน history 1 ครั้ง
        history.pushState(null, null, location.href);

        // 2. เราจะดักฟัง event เมื่อมีการกดปุ่ม Back (popstate)
        window.addEventListener('popstate', function(event) {
            // เมื่อไหร่ก็ตามที่ User พยายามกด Back
            // ให้เราดัน "หน้าปลอม" กลับเข้าไปใน history อีกครั้ง
            // ผลคือ: User จะวนกลับมาที่หน้า login.php เหมือนเดิม
            history.pushState(null, null, location.href);
            
        });

        window.addEventListener('pageshow', function(event) {
            // event.persisted จะเป็น 'true' 
            // เมื่อหน้านี้ถูกโหลดมาจาก Back-Forward Cache (เป็นซอมบี้)
            if (event.persisted) {
                // ถ้าเป็นซอมบี้ ให้สั่ง Refresh ทันที
                window.location.reload();
            }
        });
    </script>
</head>

<body>
    <div class="container col-md-4 ">
        <div class="row justify-content-center ">
                <div class="card shadow-sm border-0  text-primary-info-emphasis" style='background-color: #4a559d;'>
                    <div class="card-body p-4 p-md-5 ">

                        <h1 class="text-center mb-4">Login</h1>

                        <form action="process_login.php" method="post" class="needs-validation" novalidate>

                            <div class="mb-3">
                                <label for="id_card" class="form-label">Username</label>
                                <input type="text"  class="form-control" id="id_card"  name="uname" placeholder="กรอกเลขบัตรประชาชน 13 หลัก" required maxlength="13" pattern="[0-9]{13}">
                                <div class="invalid-feedback">
                                    กรุณากรอกเลขบัตรประชาชน 13 หลักให้ถูกต้อง
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="psword" placeholder="กรอกรหัสผ่าน" required>
                                <div class="invalid-feedback">
                                    กรุณากรอกรหัสผ่าน
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-info btn-outline-info btn-lg text-dark ">
                                    Login
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>