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
  <title>หน้าจอหอผู้ป่วย (ฉบับอัปเกรด)</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Custom CSS เพื่อให้ได้สไตล์แบบในรูปตัวอย่าง
        */
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

    .navbar-custom {
      background-color: #4a559d;
      /* สีม่วงน้ำเงินจากรูป (โดยประมาณ) */
    }

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
      <div class="col-lg-10">
        <div class="card-body p-4">
          <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="fas fa-home me-1"></i> หน้าแรก</a></li>
              <li class="breadcrumb-item active" aria-current="page">
                4.ward
              </li>
            </ol>
          </nav>
          <div class="card shadow-sm">
            <div class="card-header navbar-custom text-white">
              <h4 class="mb-0">🖥️ หน้าจอหอผู้ป่วย (Ward Monitoring)</h4>
            </div>

            <fieldset class="border p-3 py-1 rounded mb-3">
              <legend class="float-none w-auto px-2 py-3 h5">1. การเฝ้าระวัง (Monitoring)</legend>
              <div class="mb-1">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEntryModal">
                  ➕ เพิ่มบันทึกการเฝ้าระวัง
                </button>
              </div>
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th>วันที่/เวลา</th>
                    <th>SBP</th>
                    <th>NIHSS</th>
                    <th>GCS</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>30/10/2025 10:00</td>
                    <td>140</td>
                    <td>10</td>
                    <td>E4M6V5 (15)</td>
                  </tr>
                </tbody>
              </table>
            </fieldset>

            <fieldset class="border p-3 rounded mb-4">
              <legend class="float-none w-auto px-2 h5">2. การตรวจติดตาม และ สถานะ</legend>
              <div class="mb-3">
                <label for="ctFirstDay" class="form-label">CT วันแรก (ผล)</label>
                <input type="text" class="form-control" id="ctFirstDay" placeholder="บันทึกผล CT วันแรก...">
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">สถานะการผ่าตัดกะโหลก:</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="craniectomyStatus" id="craniectomyYes">
                  <label class="form-check-label" for="craniectomyYes">
                    Yes - ได้รับการทำ Post stroke craniectomy
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="craniectomyStatus" id="craniectomyNo" checked>
                  <label class="form-check-label" for="craniectomyNo">
                    No - ไม่ได้ทำ
                  </label>
                </div>
              </div>
            </fieldset>

            <fieldset class="border p-3 rounded">
              <legend class="float-none w-auto px-2 h5">3. การประเมินเพื่อจำหน่าย</legend>
              <div class="row g-3">
                <div class="col-md-4">
                  <label for="mrsDischarge" class="form-label">mRS (ณ วันจำหน่าย)</label>
                  <select class="form-select" id="mrsDischarge">
                    <option value="0">0</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label for="barthel" class="form-label">Barthel Index</label>
                  <input type="number" class="form-control" id="barthel">
                </div>
                <div class="col-md-4">
                  <label for="hrs" class="form-label">HRS</label>
                  <input type="number" class="form-control" id="hrs">
                </div>
              </div>
            </fieldset>

          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="addEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">➕ เพิ่มบันทึกการเฝ้าระวังใหม่</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="wardEntryForm">
            <div class="mb-3">
              <label for="modalSBP" class="form-label">วันที่/เวลา (Date/Time)</label>
              <input type="datetime-local" class="form-control" id="modalSBP">
            </div>
            <div class="mb-3">
              <label for="modalSBP" class="form-label">SBP (ความดัน)</label>
              <input type="number" class="form-control" id="modalSBP">
            </div>
            <div class="mb-3">
              <label for="modalNIHSS" class="form-label">NIHSS (ประเมินซ้ำ)</label>
              <input type="number" class="form-control" id="modalNIHSS">
            </div>
            <div class="mb-3">
              <label for="modalGCS" class="form-label">GCS (E_M_V_)</label>
              <input type="text" class="form-control" id="modalGCS" placeholder="E_M_V_">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
          <button type="button" class="btn btn-primary" id="saveWardEntry">บันทึก</button>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>