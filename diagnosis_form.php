<?php
// 1. [ต้องมี] เริ่ม session เพื่อ "ปลุก" ข้อมูลที่เก็บไว้
session_start();
$user = $_SESSION['user_data'];

?>
<!doctype html>
<html lang="th">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>หน้า 2: ผล CT และตัดสินใจ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
    /* 1. ทำให้ "หน้าแรก" (ที่เป็นลิงก์ <a>) เป็นสีเทา */
    .breadcrumb-item a {
      color: #6c757d;
      text-decoration: none;
    }

    /* 2. ทำให้ลิงก์ "หน้าแรก" เปลี่ยนเป็นสีน้ำเงินเมื่อชี้ */
    .breadcrumb-item a:hover {
      color: #0d6efd;
    }

    /* 3. ทำให้หน้าปัจจุบัน (active) เป็นสีเข้ม */
    .breadcrumb-item.active {
      color: #2689ebff;
    }
  </style>

</head>

<body class="bg-light">

  <nav class="navbar navbar-expand-lg navbar-dark shadow-sm navbar-custom">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">
        <i class="fas fa-brain me-2"></i>
        ระบบส่งต่อผู้ป่วยโรคหลอดเลือดสมอง (Stroke)
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
  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <nav aria-label="breadcrumb" class="mb-2">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="fas fa-home me-1"></i> หน้าแรก</a></li>
            <li class="breadcrumb-item active" aria-current="page">
              2.ER
            </li>
          </ol>
        </nav>


        <div class="card shadow-sm">
          <div class="card-header navbar-custom text-white">
            <h4 class="mb-0">🖥️ ER</h4>
          </div>
          <div class="card-body p-4">
            <form>
              <!-- t-pa/tnk -->
              <label class="form-label">การตัดสินใจให้ยาละลายลิ่มเลือด (t-PA / TNK)</label>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="giveTpa">
                <label class="form-check-label" for="giveTpa">
                  ให้การรักษา (Give t-PA/TNK)
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="giveTpa">
                <label class="form-check-label" for="giveTpa">
                  ให้การรักษาไม่ได้ (Not-Give t-PA/TNK)
                </label>
              </div>
              <hr class="my-4">
              <!-- refer time -->
              <h5 class="mt-2">⏱️ refer time</h5>
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label for="onsetTime" class="form-label">เวลาที่รถออกจากต้นทาง </label>
                  <input type="time" class="form-control" id="onsetTime">
                </div>
                <div class="col-md-6">
                  <label for="arrivalTime" class="form-label">เวลาที่ถึง รพ.</label>
                  <input type="time" class="form-control" id="arrivalTime">
                </div>
              </div>
              <hr class="my-4">
              <!--  -->
              <fieldset class="border p-3 rounded mb-4">
                <legend class="float-none w-auto px-2 h5">ส่วนที่ 2: การวินิจฉัย และ Imaging</legend>
                <div class="row g-3">
                  <div class="col-md-4">
                    <label for="ctncTime" class="form-label">CT NC กี่โมง</label>
                    <input type="time" class="form-control" id="ctncTime">
                  </div>
                  <div class="col-md-4">
                    <label for="ctaTime" class="form-label">CTA กี่โมง</label>
                    <input type="time" class="form-control" id="ctaTime">
                  </div>
                  <div class="col-md-4">
                    <label for="mriTime" class="form-label">MRI กี่โมง</label>
                    <input type="time" class="form-control" id="mriTime">
                  </div>
                </div>
                <hr>
                <label class="form-label fw-bold">ผล CT (Ischemic / Hemorrhagic):</label>
                <div class="p-3 bg-light border rounded">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="ctResult" id="ctResultIschemic" value="ischemic">
                    <label class="form-check-label fs-5" for="ctResultIschemic">ไม่พบเลือดออก (Ischemic)</label>
                  </div>
                  <hr class="my-2">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="ctResult" id="ctResultHemorrhagic" value="hemorrhagic">
                    <label class="form-check-label fs-5" for="ctResultHemorrhagic">พบเลือดออก (Hemorrhagic)</label>
                  </div>
                </div>
              </fieldset>

              <fieldset class="border p-3 rounded">
                <legend class="float-none w-auto px-2 h5">ส่วนที่ 3: การตัดสินใจรักษา</legend>

                <div id="ischemicPathway" class="d-none">
                  <h5 class="text-primary">A. แนวทาง Ischemic Stroke</h5>
                  <div class="card card-body">

                    <label class="form-label fw-bold">1. การให้ยาละลายลิ่มเลือด (IV Lysis)</label>
                    <div class="row g-3 mb-3">
                      <div class="col-md-6">
                        <label for="tpaTime" class="form-label">rT-PA / TNK กี่โมง</label>
                        <input type="datetime-local" class="form-control" id="tpaTime">
                      </div>
                      <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" id="noTpa">
                          <label class="form-check-label" for="noTpa">ไม่ให้การรักษา (Contraindicated)</label>
                        </div>
                      </div>
                    </div>
                    <hr>

                    <label class="form-label fw-bold">2. การสวนลากลิ่มเลือด (Mechanical Thrombectomy - MT)</label>
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label for="anesthesiaTime" class="form-label">ดมยา กี่โมง</label>
                        <input type="datetime-local" class="form-control" id="anesthesiaTime">
                      </div>
                      <div class="col-md-6">
                        <label for="punctureTime" class="form-label">puncture กี่โมง</label>
                        <input type="datetime-local" class="form-control" id="punctureTime">
                      </div>
                      <div class="col-md-6">
                        <label for="recanTime" class="form-label">Recanalization กี่โมง</label>
                        <input type="datetime-local" class="form-control" id="recanTime">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <label for="aspect" class="form-label">ASPECT (0-10)</label>
                      <input type="number" class="form-control" id="aspect" min="0" max="10">
                    </div>
                    <div class="col-md-4">
                      <label for="collateral" class="form-label">Collateral score (0-5)</label>
                      <input type="number" class="form-control" id="collateral" min="0" max="5">
                    </div>
                    <div class="col-md-4">
                      <label for="occlusionLocation" class="form-label">ตันตรงไหน (Drop down)</label>
                      <select class="form-select" id="occlusionLocation">
                        <option selected>-- เลือกตำแหน่ง --</option>
                        <option value="ICA">ICA</option>
                        <option value="M1">M1</option>
                        <option value="M2">M2</option>
                        <option value="Basilar">Basilar</option>
                        <option value="Other">Other...</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div id="hemorrhagicPathway" class="d-none">
                  <h5 class="text-danger">B. แนวทาง Hemorrhagic Stroke</h5>
                  <div class="card card-body">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="consultNS">
                      <label class="form-check-label" for="consultNS">ปรึกษาศัลยแพทย์ระบบประสาท</label>
                    </div>
                  </div>
                </div>
              </fieldset>
              <div class="d-grid">
                <!-- <button type="submit" class="btn btn-success btn-lg">➡️ รับเข้าหอผู้ป่วย (Admit to Ward)</button> -->
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // JavaScript ง่ายๆ เพื่อซ่อน/แสดงผล ตามผล CT
    const radioIschemic = document.getElementById('ctResultIschemic');
    const radioHemorrhagic = document.getElementById('ctResultHemorrhagic');
    const ischemicPathway = document.getElementById('ischemicPathway');
    const hemorrhagicPathway = document.getElementById('hemorrhagicPathway');

    radioIschemic.addEventListener('change', () => {
      if (radioIschemic.checked) {
        ischemicPathway.classList.remove('d-none');
        hemorrhagicPathway.classList.add('d-none');
      }
    });

    radioHemorrhagic.addEventListener('change', () => {
      if (radioHemorrhagic.checked) {
        ischemicPathway.classList.add('d-none');
        hemorrhagicPathway.classList.remove('d-none');
      }
    });
  </script>
</body>

</html>