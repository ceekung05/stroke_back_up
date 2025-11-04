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
  <title>หน้าจำหน่ายและติดตามผล (Follow-up)</title>
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
        <nav aria-label="breadcrumb" class="mb-2">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="fas fa-home me-1"></i> หน้าแรก</a></li>
            <li class="breadcrumb-item active" aria-current="page">
              5. Follow
            </li>
          </ol>
        </nav>
        <div class="card shadow-sm">
          <div class="card-header navbar-custom text-white">
            <h4 class="mb-0">📝 หน้าจำหน่ายและติดตามผล (Discharge & Follow-up)</h4>
          </div>
          <div class="card-body p-4">

            <fieldset class="border p-3 rounded mb-4">
              <legend class="float-none w-auto px-2 h5">1. แผนการจำหน่าย</legend>

              <div class="mb-3">
                <label for="dischargeDate" class="form-label fw-bold">วันที่จำหน่าย (Discharge Date)</label>
                <input type="date" class="form-control" id="dischargeDate">
              </div>
              <div class="mb-3">
                <label for="dischargePlan" class="form-label">แผนการจำหน่าย (กลับบ้าน or refer)</label>
                <select class="form-select" id="dischargePlan">
                  <option value="home">กลับบ้าน (Go Home)</option>
                  <option value="refer">ส่งต่อ (Refer)</option>
                </select>
              </div>
            </fieldset>
            <fieldset class="border p-3 rounded">
              <legend class="float-none w-auto px-2 h5">2. ระบบนัดหมายติดตามผล</legend>
              <p>
                <strong>mRS (ณ วันจำหน่าย) (mRS 0):</strong> [ 2 ]
              </p>
              <div class="mb-3 no-print">
                <button type="button" class="btn btn-outline-primary" id="autoCreateAppointments">
                  ➕ สร้างนัดอัตโนมัติ (mRS 1, 3, 6, 12)
                </button>
              </div>
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>การติดตามผล</th>
                    <th>วันที่นัดหมาย</th>
                    <th>สถานะ</th>
                    <th>mRS Score</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody id="followupTableBody">
                </tbody>
              </table>
            </fieldset>

          </div>
        </div>
      </div>
    </div>
  </div>


  <div class="modal fade" id="editMrsModal" tabindex="-1" aria-labelledby="editMrsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editMrsModalLabel">บันทึกคะแนน mRS</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="mrsEntryForm">
            <div class="mb-3">
              <label for="mrsScoreSelect" class="form-label">เลือกคะแนน mRS (0-6)</label>
              <select class="form-select" id="mrsScoreSelect">
                <option value="0">0 - ไม่มีอาการ</option>
                <option value="1">1 - มีอาการแต่ไม่กระทบ</option>
                <option value="2">2 - พิการเล็กน้อย</option>
                <option value="3">3 - พิการปานกลาง</option>
                <option value="4">4 - พิการค่อนข้างรุนแรง</option>
                <option value="5">5 - พิการรุนแรง</option>
                <option value="6">6 - เสียชีวิต</option>
              </select>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
          <button type="button" class="btn btn-primary" id="saveMrsScore">บันทึก</button>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {

      // --- ส่วนที่ 1: สร้างนัดอัตโนมัติ (ปุ่ม "สร้างนัดอัตโนมัติ") ---

      const createButton = document.getElementById('autoCreateAppointments');
      const dischargeDateInput = document.getElementById('dischargeDate');
      const tableBody = document.getElementById('followupTableBody');

      createButton.addEventListener('click', function() {
        const startDate = dischargeDateInput.value;

        // 1. ตรวจสอบว่าเลือกวันที่หรือยัง
        if (!startDate) {
          alert('กรุณาเลือก "วันที่จำหน่าย" ก่อนครับ');
          return;
        }

        // 2. ล้างตารางเก่า (ถ้ามี)
        tableBody.innerHTML = '';

        const baseDate = new Date(startDate);
        const intervals = [1, 3, 6, 12]; // mRS 1, 3, 6, 12 เดือน

        // 3. วนลูปสร้างแถว 4 แถว
        intervals.forEach(months => {
          // คำนวณวันที่ในอนาคต
          const futureDate = new Date(baseDate);
          futureDate.setMonth(futureDate.getMonth() + months);

          // แปลงเป็นรูปแบบ "วัน/เดือน/ปี"
          const formattedDate = futureDate.toLocaleDateString('th-TH');
          const label = `mRS ${months} เดือน`;

          // 4. สร้าง HTML สำหรับแถวใหม่
          const newRowHTML = `
            <tr>
              <td data-label="${label}"><strong>${label}</strong></td>
              <td>${formattedDate}</td>
              <td><span class="badge bg-warning">รอนัด</span></td>
              <td class="mrs-score-cell">(ว่าง)</td>
              <td>
                <button type="button" class="btn btn-primary btn-sm edit-mrs-btn" data-bs-toggle="modal" data-bs-target="#editMrsModal">
                  บันทึกคะแนน
                </button>
              </td>
            </tr>
          `;

          // 5. เพิ่มแถวใหม่ลงในตาราง
          tableBody.innerHTML += newRowHTML;
        });
      });


      // --- ส่วนที่ 2: บันทึกคะแนน mRS (ปุ่ม "บันทึกคะแนน") (เหมือนเดิม) ---

      const editMrsModal = new bootstrap.Modal(document.getElementById('editMrsModal'));
      const modalElement = document.getElementById('editMrsModal');
      const saveMrsButton = document.getElementById('saveMrsScore');
      let currentRowToUpdate = null;

      tableBody.addEventListener('click', function(event) {
        if (event.target.classList.contains('edit-mrs-btn')) {
          currentRowToUpdate = event.target.closest('tr');
          const label = currentRowToUpdate.querySelector('td[data-label]').innerText;
          modalElement.querySelector('.modal-title').innerText = `บันทึกคะแนน: ${label}`;
        }
      });

      saveMrsButton.addEventListener('click', function() {
        if (!currentRowToUpdate) return;

        const selectedScoreText = document.getElementById('mrsScoreSelect').options[document.getElementById('mrsScoreSelect').selectedIndex].text;

        const scoreCell = currentRowToUpdate.querySelector('.mrs-score-cell');
        scoreCell.innerText = selectedScoreText;
        scoreCell.style.fontWeight = 'bold';

        const statusCell = currentRowToUpdate.querySelector('td span.badge');
        statusCell.innerText = 'เสร็จสิ้น';
        statusCell.classList.remove('bg-warning');
        statusCell.classList.add('bg-success');

        const editButton = currentRowToUpdate.querySelector('.edit-mrs-btn');
        editButton.disabled = true;
        editButton.innerText = 'บันทึกแล้ว';

        editMrsModal.hide();
        currentRowToUpdate = null;
      });

    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>