<?php
session_start();
require_once 'connectdb.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user_data'];
$admission_id = $_GET['admission_id'] ?? '';

// --- ส่วนดึงข้อมูลเก่า (จาก tbl_ward) ---
$row = [];
if ($admission_id) {
    $sql = "SELECT * FROM tbl_ward WHERE admission_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
}

function val($field)
{
    global $row;
    return htmlspecialchars($row[$field] ?? '');
}
function sel($field, $value)
{
    global $row;
    return (isset($row[$field]) && $row[$field] == $value) ? 'selected' : '';
}
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <title>5. Follow-up - ระบบ Stroke Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="style.css">
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <i class="bi bi-heart-pulse-fill"></i>
            <span>Stroke Care</span>
        </div>
        <hr class="sidebar-divider"><a href="dashboard.php" >
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="index.php"><i class="bi bi-list-task"></i> กลับไปหน้า Patient List</a>
        <hr class="sidebar-divider">
        <a href="form.php?admission_id=<?= $admission_id ?>">
            <i class="bi bi-person-lines-fill"></i> 1. ข้อมูลทั่วไป
        </a>
        <a href="diagnosis_form.php?admission_id=<?= $admission_id ?>">
            <i class="bi bi-hospital"></i> 2. ER
        </a>
        <a href="OR_Procedure_Form.php?admission_id=<?= $admission_id ?>">
            <i class="bi bi-scissors"></i> 3. OR Procedure
        </a>
        <a href="ward.php?admission_id=<?= $admission_id ?>">
            <i class="bi bi-building-check"></i> 4. Ward
        </a>
        <a href="follow.php?admission_id=<?= $admission_id ?>" class="active">
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
                <h1>5. Follow Up & Outcome</h1>
                <p class="subtitle mb-0">การติดตามผลการรักษาหลังจำหน่าย (Follow-up System)</p>
            </div>
        </div>

        <div class="card-form">
            <div class="section-title" style="margin-top:0;">
                <i class="bi bi-box-arrow-right"></i> 1. ข้อมูลหลังจำหน่าย (Post-Discharge Info)
            </div>
            <form id="dischargeInfoForm" onsubmit="return false;">
                <input type="hidden" id="admission_id" name="admission_id" value="<?php echo $admission_id; ?>">

                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="dischargeDate" class="form-label fw-bold">วันที่นัดครั้งแรก (First Appointment)</label>
                        <input type="date" class="form-control" id="dischargeDate" name="first_followup_date" value="<?= val('first_followup_date') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="dischargePlan" class="form-label fw-bold">แผนการจำหน่าย (Destination)</label>
                        <select class="form-select" id="dischargePlan" name="discharge_destination">
                            <option value="home" <?= sel('discharge_destination', 'home') ?>>กลับบ้าน (Go Home)</option>
                            <option value="refer" <?= sel('discharge_destination', 'refer') ?>>ส่งต่อ (Refer)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div id="referHospitalDiv" class="mt-2" style="display: none;">
                            <input type="text" class="form-control" name="refer_name_hospital"
                                id="referHospitalName"
                                placeholder="ระบุชื่อโรงพยาบาลปลายทาง..."
                                value="<?= val('refer_name_hospital') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary w-100 shadow-sm" id="saveDischargeBtn">
                            <i class="bi bi-save me-1"></i> บันทึกข้อมูล
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-form">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom flex-wrap gap-2">
                <div class="section-title mb-0 border-0 p-0 m-0">
                    <i class="bi bi-calendar-event"></i> 2. ตารางนัดหมาย (Appointments)
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" id="autoCreateAppointments">
                    <i class="bi bi-magic me-1"></i> สร้างนัดอัตโนมัติ (1, 3, 6, 12 เดือน)
                </button>
            </div>

            <div class="alert alert-light border border-info border-opacity-25 text-info d-flex align-items-center small py-2">
                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                <span>ระบบจะช่วยสร้างรายการนัดหมายล่วงหน้าตามมาตรฐาน Stroke Care (สามารถกดปุ่ม "สร้างนัดอัตโนมัติ" ด้านบนได้)</span>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-hover align-middle">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th style="width: 20%;">ระยะเวลา (Period)</th>
                            <th style="width: 20%;">วันที่นัดหมาย</th>
                            <th style="width: 15%;">สถานะ</th>
                            <th style="width: 25%;">mRS Score</th>
                            <th class="text-center" style="width: 20%;">จัดการ (Action)</th>
                        </tr>
                    </thead>
                    <tbody id="followupTableBody">
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">กำลังโหลดข้อมูล...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-center mt-5 mb-5">
            <a href="index.php" class="btn btn-success btn-lg px-5 py-3 shadow" style="border-radius: 50px;">
                <i class="bi bi-check-circle-fill me-2"></i> เสร็จสิ้นการทำงาน (Finish)
            </a>
        </div>

    </div>

    <div class="modal fade" id="editMrsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-clipboard-check me-2"></i> บันทึกผลการติดตาม (Outcome)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="currentFollowupId">
                    <label class="form-label fw-bold mb-3">ระบุคะแนน mRS (Modified Rankin Scale):</label>
                    <select class="form-select form-select-lg mb-3" id="mrsScoreSelect">
                        <option value="" selected disabled>-- กรุณาเลือกคะแนน --</option>
                        <option value="0">0 - No symptoms</option>
                        <option value="1">1 - No significant disability</option>
                        <option value="2">2 - Slight disability</option>
                        <option value="3">3 - Moderate disability</option>
                        <option value="4">4 - Moderately severe disability</option>
                        <option value="5">5 - Severe disability</option>
                        <option value="6">6 - Dead</option>
                    </select>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="saveMrsScoreBtn">บันทึกผล</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const admissionId = document.getElementById('admission_id').value;
            const tableBody = document.getElementById('followupTableBody');
            const editMrsModal = new bootstrap.Modal(document.getElementById('editMrsModal'));

            // --- เพิ่มใหม่: จัดการการแสดงผลช่องระบุชื่อ รพ. ---
            const dischargeSelect = document.getElementById('dischargePlan');
            const referDiv = document.getElementById('referHospitalDiv');
            const referInput = document.getElementById('referHospitalName');

            function toggleReferInput() {
                if (dischargeSelect.value === 'refer') {
                    referDiv.style.display = 'block'; // แสดง
                    referInput.setAttribute('required', 'required'); // บังคับกรอก (ถ้าต้องการ)
                } else {
                    referDiv.style.display = 'none'; // ซ่อน
                    referInput.removeAttribute('required');
                    referInput.value = ''; // (Optional) ล้างค่าเมื่อเปลี่ยนเป็นกลับบ้าน
                }
            }

            // เรียกทำงานตอนเปลี่ยนค่า
            dischargeSelect.addEventListener('change', toggleReferInput);

            // เรียกทำงานตอนโหลดหน้าเว็บ (กรณีมีข้อมูลเก่าบันทึกไว้)
            toggleReferInput();

            // --- 1. บันทึก Section 1 (Discharge Info) ---
            document.getElementById('saveDischargeBtn').addEventListener('click', function() {
                const formData = new FormData(document.getElementById('dischargeInfoForm'));

                fetch('save_followup_discharge.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'บันทึกสำเร็จ',
                                timer: 1000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
            });

            // --- 2. โหลดข้อมูลตาราง (Function) ---
            function loadTable() {
                const formData = new FormData();
                formData.append('action', 'get_list');
                formData.append('admission_id', admissionId);

                fetch('api_followup.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(res => {
                        tableBody.innerHTML = '';
                        if (res.data && res.data.length > 0) {
                            res.data.forEach(row => {
                                let statusBadge = '<span class="badge bg-warning text-dark">รอนัด</span>';
                                let actionBtns = `
                                <button class="btn btn-primary btn-sm btn-edit" data-id="${row.id}">
                                    <i class="bi bi-pencil-fill"></i> บันทึก
                                </button>
                                <button class="btn btn-outline-danger btn-sm btn-noshow ms-1" data-id="${row.id}">
                                    ไม่มา
                                </button>
                            `;

                                if (row.status === 'attended') {
                                    statusBadge = '<span class="badge bg-success">มาตามนัด</span>';
                                    actionBtns = '<span class="text-success"><i class="bi bi-check-lg"></i> บันทึกแล้ว</span>';
                                } else if (row.status === 'no_show') {
                                    statusBadge = '<span class="badge bg-danger">ไม่มา</span>';
                                    actionBtns = '<span class="text-danger">Missed</span>';
                                }

                                // --- จุดที่แก้: เปลี่ยนช่องวันที่เป็น Input Date ---
                                // ถ้าสถานะยังไม่ lock (เช่น ยังเป็น pending) ให้แก้ได้
                                // แต่เพื่อความสะดวก ผมเปิดให้แก้ได้ตลอด ถ้าอยากล็อคให้เติม disabled ในเงื่อนไข status
                                let dateInput = `
                                <input type="date" class="form-control form-control-sm border-primary text-primary fw-bold change-date-input" 
                                    value="${row.scheduled_date}" 
                                    data-id="${row.id}" 
                                    style="max-width: 160px; cursor: pointer;">
                                `;

                                tableBody.innerHTML += `
                                <tr>
                                    <td><strong>${row.followup_label}</strong></td>
                                    <td>${dateInput}</td>
                                    <td>${statusBadge}</td>
                                    <td>${row.mrs_score !== null ? row.mrs_score : '-'}</td>
                                    <td>${actionBtns}</td>
                                </tr>
                            `;
                            });
                        } else {
                            tableBody.innerHTML = '<tr><td colspan="5" class="text-center">ยังไม่มีรายการนัดหมาย (กรุณากดปุ่มสร้างนัด)</td></tr>';
                        }
                    });
            }

            // โหลดตารางเมื่อเปิดหน้า
            if (admissionId) loadTable();

            // --- 3. สร้างนัดอัตโนมัติ ---
            document.getElementById('autoCreateAppointments').addEventListener('click', function() {
                const startDate = document.getElementById('dischargeDate').value;
                if (!startDate) {
                    Swal.fire('กรุณาระบุวันที่นัดครั้งแรกก่อน', '', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'สร้างตารางนัด?',
                    text: "รายการเดิม (ถ้ามี) จะถูกลบและสร้างใหม่",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยันสร้างใหม่'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('action', 'auto_create');
                        formData.append('admission_id', admissionId);
                        formData.append('start_date', startDate);

                        fetch('api_followup.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    loadTable(); // รีโหลดตาราง
                                    Swal.fire('สร้างสำเร็จ', '', 'success');
                                }
                            });
                    }
                });
            });

            // --- 4. จัดการปุ่มในตาราง (Delegate Event) ---
            tableBody.addEventListener('click', function(e) {
                // ปุ่มบันทึก (เปิด Modal)
                if (e.target.closest('.btn-edit')) {
                    const btn = e.target.closest('.btn-edit');
                    document.getElementById('currentFollowupId').value = btn.dataset.id;
                    editMrsModal.show();
                }
                // ปุ่มไม่มา
                if (e.target.closest('.btn-noshow')) {
                    const btn = e.target.closest('.btn-noshow');
                    if (confirm('ยืนยันว่าผู้ป่วยไม่มาตามนัด?')) {
                        updateFollowup(btn.dataset.id, 'no_show', null);
                    }
                }
            });

            // --- 5. บันทึกผลจาก Modal ---
            document.getElementById('saveMrsScoreBtn').addEventListener('click', function() {
                const id = document.getElementById('currentFollowupId').value;
                const score = document.getElementById('mrsScoreSelect').value;

                if (score === "") {
                    alert("กรุณาเลือกคะแนน");
                    return;
                }

                updateFollowup(id, 'attended', score);
                editMrsModal.hide();
            });

            function updateFollowup(id, status, score) {
                const formData = new FormData();
                formData.append('action', 'update_item');
                formData.append('admission_id', admissionId);
                formData.append('followup_id', id);
                formData.append('status', status);
                if (score !== null) formData.append('mrs_score', score);

                fetch('api_followup.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            loadTable(); // รีโหลดตารางเพื่ออัปเดตสถานะ
                        }
                    });
            }

            // --- 6. ฟังก์ชัน: เปลี่ยนวันที่นัดแล้วบันทึกทันที (Auto-save Date) ---
            // ใส่ไว้ต่อท้ายสุด ภายใน document.addEventListener('DOMContentLoaded', ...) หรือต่อจาก loadTable ก็ได้
            tableBody.addEventListener('change', function(e) {
                if (e.target.classList.contains('change-date-input')) {
                    const input = e.target;
                    const newDate = input.value;
                    const id = input.dataset.id; // ดึง ID ของนัดหมาย

                    if (!newDate) {
                        Swal.fire('Error', 'วันที่ไม่ถูกต้อง', 'error');
                        return;
                    }

                    // เปลี่ยนสีขอบเป็นสีเหลือง เพื่อบอกว่ากำลังบันทึก
                    input.style.borderColor = '#ffc107';

                    const formData = new FormData();
                    formData.append('action', 'update_date'); // สั่ง Action update_date
                    formData.append('followup_id', id); // ส่ง ID นัดหมาย
                    formData.append('new_date', newDate); // ส่งวันที่ใหม่

                    fetch('api_followup.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                // บันทึกสำเร็จ: เปลี่ยนขอบเป็นสีเขียว
                                input.style.borderColor = '#198754';

                                // แจ้งเตือนเล็กๆ มุมขวาบน
                                const Toast = Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 1500,
                                    didOpen: (toast) => {
                                        toast.onmouseenter = Swal.stopTimer;
                                        toast.onmouseleave = Swal.resumeTimer;
                                    }
                                });
                                Toast.fire({
                                    icon: 'success',
                                    title: 'เปลี่ยนวันนัดเรียบร้อย'
                                });
                            } else {
                                // บันทึกไม่สำเร็จ
                                Swal.fire('Error', data.message || 'บันทึกไม่สำเร็จ', 'error');
                                input.style.borderColor = 'red';
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            input.style.borderColor = 'red';
                        });
                }
            });
        });
    </script>
     <!-- เวลา -->
     <script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".timepicker", {
            enableTime: true,       // เปิดโหมดเวลา
            noCalendar: true,       // ไม่เอาปฏิทิน (เอาแต่นาฬิกา)
            dateFormat: "H:i",      // รูปแบบ 24 ชั่วโมง (เช่น 14:30)
            time_24hr: true,        // บังคับ 24 ชั่วโมงแน่นอน
            allowInput: true        // อนุญาตให้พิมพ์ตัวเลขเองได้ด้วย
        });
    });
</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clockpicker/0.0.7/bootstrap-clockpicker.min.js"></script>
</body>

</html>