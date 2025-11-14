<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user_data'];
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>5. Follow-up - ระบบ Stroke Care</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <i class="bi bi-heart-pulse-fill"></i>
            <span>Stroke Care</span>
        </div>

        <a href="index.php">
            <i class="bi bi-list-task"></i> กลับไปหน้า Patient List
        </a>
        <hr class="sidebar-divider">

        <a href="form.php">
            <i class="bi bi-person-lines-fill"></i> 1. ข้อมูลทั่วไป
        </a>
        <a href="diagnosis_form.php">
            <i class="bi bi-hospital"></i> 2. ER
        </a>
        <a href="OR_Procedure_Form.php">
            <i class="bi bi-scissors"></i> 3. OR Procedure
        </a>
        <a href="ward.php">
            <i class="bi bi-building-check"></i> 4. Ward
        </a>
        <a href="follow.php" class="active">
            <i class="bi bi-calendar-check"></i> 5. Follow Up
        </a>

        <hr class="sidebar-divider">
        <a href="logout.php" class="text-danger">
            <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
        </a>
    </div>

    <div class="main">

        <div class="header-section">
            <div class="icon-container">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="title-container">
                <h1>5. Discharge & Follow-up</h1>
                <p class="subtitle mb-0">หน้าจำหน่ายและติดตามผล</p>
            </div>
        </div>

        <div class="section-title">
            <i class="bi bi-box-arrow-right"></i> 1. หลังจำหน่ายจากรพ.
        </div>
        <div class="row g-3">
            <div class="col-md-3 mb-3">
                <label for="dischargeDate" class="form-label fw-bold">วันที่นัดครั้งแรก</label>
                <input type="date" class="form-control " id="dischargeDate" >
                <!-- <input type="time" name="" id="" class="form-control mt-2"> -->
            </div>
            <div class="col-md-3 mb-3">
                <label for="dischargePlan" class="form-label">แผนการจำหน่าย (กลับบ้าน or refer)</label>
                <select class="form-select" id="dischargePlan">
                    <option value="home">กลับบ้าน (Go Home)</option>
                    <option value="refer">ส่งต่อ (Refer)</option>
                </select>
            </div>
        </div>

        <div class="section-title">
            <i class="bi bi-calendar-event"></i> 2. ระบบนัดหมายติดตามผล
        </div>
        <div class="row g-3">
            <div class="col-md-3 mb-3">
                <label for="mrsAtDischarge" class="form-label"><strong>mRS (ณ วันจำหน่าย) (mRS 0):</strong></label>
                <select class="form-select" id="mrsAtDischarge">
                    <option value="">-- เลือก mRS ณ วันจำหน่าย --</option>
                    <option value="0">0 - No symptoms</option>
                    <option value="1">1 - No significant disability</option>
                    <option value="2">2 - Slight disability</option>
                    <option value="3">3 - Moderate disability</option>
                    <option value="4">4 - Moderately severe disability</option>
                    <option value="5">5 - Severe disability</option>
                    <option value="6">6 - Dead</option>
                </select>
            </div>
            <div class="col-md-6 mb-4 d-flex align-items-end">
                <button type="button" class="btn btn-outline-primary" id="autoCreateAppointments">
                    <i class="bi bi-plus-circle"></i> สร้างนัดอัตโนมัติ (mRS 1, 3, 6, 12)
                </button>
            </div>
        </div>

        <table class="table table-bordered table-hover align-middle mt-3">
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

    </div>

    <div class="modal fade" id="editMrsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMrsModalLabel">บันทึกคะแนน mRS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <select class="form-select" id="mrsScoreSelect">
                        <option value="" selected disabled>-- กรุณาเลือก --</option>
                        <option value="0">0 - No symptoms</option>
                        <option value="1">1 - No significant disability</option>
                        <option value="2">2 - Slight disability</option>
                        <option value="3">3 - Moderate disability</option>
                        <option value="4">4 - Moderately severe disability</option>
                        <option value="5">5 - Severe disability</option>
                        <option value="6">6 - Dead</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-primary" id="saveMrsScore">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/th.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/buddhistYear.js"></script>
    <script>
        flatpickr(".datepicker-no-time", {
            dateFormat: "d/m/Y",
            locale: "th",
            plugins: [new window.flatpickr.plugins.buddhistYear()],
            monthSelectorType: "dropdown"
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const createButton = document.getElementById('autoCreateAppointments');
            const dischargeDateInput = document.getElementById('dischargeDate');
            const tableBody = document.getElementById('followupTableBody');
            const editMrsModal = new bootstrap.Modal(document.getElementById('editMrsModal'));
            const modalElement = document.getElementById('editMrsModal');
            const saveMrsButton = document.getElementById('saveMrsScore');
            let currentRowToUpdate = null;

            createButton.addEventListener('click', function() {
                const startDate = dischargeDateInput.value;
                if (!startDate) {
                    alert('กรุณาเลือก "วันที่นัดครั้งแรก" ก่อนครับ');
                    return;
                }
                tableBody.innerHTML = '';
                const baseDate = new Date(startDate);
                const intervals = [1, 3, 6, 12]; // mRS 1, 3, 6, 12 เดือน

                intervals.forEach(months => {
                    const futureDate = new Date(baseDate);
                    futureDate.setMonth(futureDate.getMonth() + months);
                    const formattedDate = futureDate.toLocaleDateString('th-TH', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                    const label = `mRS ${months} เดือน`;

                    // (*** แก้ไขปุ่ม Action ตรงนี้ ***)
                    tableBody.innerHTML += `
                        <tr>
                          <td data-label="${label}"><strong>${label}</strong></td>
                          <td>${formattedDate}</td>
                          <td><span class="badge bg-warning">รอนัด</span></td>
                          <td class="mrs-score-cell text-muted">(ว่าง)</td>
                          <td class="action-cell">
                            <button type="button" class="btn btn-primary btn-sm edit-mrs-btn" data-bs-toggle="modal" data-bs-target="#editMrsModal">
                              <i class="bi bi-pencil-fill"></i> บันทึก
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm no-show-btn ms-1">
                              <i class="bi bi-x-circle"></i> (ไม่มา)
                            </button>
                          </td>
                        </tr>`;
                });
            });

            // (*** อัปเดต Event Listener ให้รองรับปุ่ม "ไม่มา" ***)
            tableBody.addEventListener('click', function(event) {
                // 1. จัดการปุ่ม "บันทึก" (เปิด Modal)
                const targetButton = event.target.closest('.edit-mrs-btn');
                if (targetButton) {
                    currentRowToUpdate = targetButton.closest('tr');
                    const label = currentRowToUpdate.querySelector('td[data-label]').innerText;
                    modalElement.querySelector('.modal-title').innerText = `บันทึกคะแนน: ${label}`;
                    document.getElementById('mrsScoreSelect').value = "0"; // ตั้งค่าเริ่มต้น
                }

                // 2. (*** ใหม่: จัดการปุ่ม "ไม่มา" ***)
                const noShowButton = event.target.closest('.no-show-btn');
                if (noShowButton) {
                    // ถามยืนยัน
                    if (confirm('ยืนยันว่าผู้ป่วย "ไม่มา" ตามนัดนี้?')) {
                        const row = noShowButton.closest('tr');
                        const statusCell = row.querySelector('td span.badge');
                        
                        // เปลี่ยนสถานะเป็น "ไม่มา" (สีแดง)
                        statusCell.innerText = 'ไม่มา';
                        statusCell.classList.remove('bg-warning');
                        statusCell.classList.add('bg-danger');

                        // ปิดปุ่มทั้งหมดในแถว
                        const actionCell = row.querySelector('.action-cell');
                        actionCell.querySelectorAll('button').forEach(btn => {
                            btn.disabled = true;
                        });
                        
                        // ล้างค่า mRS
                        row.querySelector('.mrs-score-cell').innerText = '(N/A)';
                    }
                }
            });

            // (*** อัปเดตการบันทึก Modal ***)
            saveMrsButton.addEventListener('click', function() {
                if (!currentRowToUpdate) return;
                const selectElement = document.getElementById('mrsScoreSelect');
                const selectedScoreText = selectElement.options[selectElement.selectedIndex].text;

                // บันทึก mRS
                currentRowToUpdate.querySelector('.mrs-score-cell').innerText = selectedScoreText;
                currentRowToUpdate.querySelector('.mrs-score-cell').classList.remove('text-muted');
                
                const statusCell = currentRowToUpdate.querySelector('td span.badge');
                
                // (*** เปลี่ยนสถานะเป็น "มา" (สีเขียว) ***)
                statusCell.innerText = 'มา';
                statusCell.classList.remove('bg-warning');
                statusCell.classList.add('bg-success');
                
                const editButton = currentRowToUpdate.querySelector('.edit-mrs-btn');
                
                // ปิดปุ่มทั้งหมดในแถว
                const actionCell = currentRowToUpdate.querySelector('.action-cell');
                actionCell.querySelectorAll('button').forEach(btn => {
                    btn.disabled = true;
                });
                
                // อัปเดตปุ่ม "บันทึก" (เพื่อความสวยงาม)
                editButton.innerHTML = '<i class="bi bi-check-lg"></i> บันทึกแล้ว';
                editButton.classList.remove('btn-primary');
                editButton.classList.add('btn-secondary');

                editMrsModal.hide();
                currentRowToUpdate = null;
            });
        });
    </script>
</body>

</html>