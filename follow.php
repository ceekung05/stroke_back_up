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

function val($field) { global $row; return htmlspecialchars($row[$field] ?? ''); }
function sel($field, $value) { global $row; return (isset($row[$field]) && $row[$field] == $value) ? 'selected' : ''; }
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
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <i class="bi bi-heart-pulse-fill"></i> <span>Stroke Care</span>
        </div>
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
            <div class="icon-container"><i class="bi bi-calendar-check"></i></div>
            <div class="title-container">
                <h1>5. Discharge & Follow-up</h1>
                <p class="subtitle mb-0">หน้าจำหน่ายและติดตามผล</p>
            </div>
        </div>

        <form id="dischargeInfoForm" onsubmit="return false;">
            <input type="hidden" id="admission_id" name="admission_id" value="<?php echo $admission_id; ?>">
            
            <div class="section-title">
                <i class="bi bi-box-arrow-right"></i> 1. หลังจำหน่ายจากรพ.
            </div>
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="dischargeDate" class="form-label fw-bold">วันที่นัดครั้งแรก</label>
                    <input type="date" class="form-control" id="dischargeDate" name="first_followup_date" value="<?= val('first_followup_date') ?>">
                </div>
                <div class="col-md-3">
                    <label for="dischargePlan" class="form-label">แผนการจำหน่าย</label>
                    <select class="form-select" id="dischargePlan" name="discharge_destination">
                        <option value="home" <?= sel('discharge_destination', 'home') ?>>กลับบ้าน (Go Home)</option>
                        <option value="refer" <?= sel('discharge_destination', 'refer') ?>>ส่งต่อ (Refer)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary w-100" id="saveDischargeBtn">
                        <i class="bi bi-save"></i> บันทึกข้อมูลจำหน่าย
                    </button>
                </div>
            </div>
        </form>

        <div class="section-title mt-4">
            <i class="bi bi-calendar-event"></i> 2. ระบบนัดหมายติดตามผล
        </div>
        <div class="row g-3">
            <div class="col-md-12 mb-2">
                <div class="alert alert-info d-flex align-items-center">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <span>กดปุ่มด้านล่างเพื่อสร้างตารางนัดหมายล่วงหน้า (1, 3, 6, 12 เดือน) จากวันที่นัดครั้งแรก</span>
                </div>
                <button type="button" class="btn btn-outline-primary" id="autoCreateAppointments">
                    <i class="bi bi-magic"></i> สร้างนัดอัตโนมัติ
                </button>
            </div>
        </div>

        <table class="table table-bordered table-hover align-middle mt-3 bg-white shadow-sm rounded">
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
                <tr><td colspan="5" class="text-center text-muted">กำลังโหลดข้อมูล...</td></tr>
            </tbody>
        </table>
        
        <div class="text-center mt-5">
            <a href="index.php" class="btn btn-success btn-lg px-5">
                <i class="bi bi-check-circle-fill"></i> เสร็จสิ้นการทำงาน
            </a>
        </div>

    </div>

    <div class="modal fade" id="editMrsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">บันทึกผลการติดตาม</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="currentFollowupId">
                    <label class="form-label">คะแนน mRS:</label>
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
                    <button type="button" class="btn btn-primary" id="saveMrsScoreBtn">บันทึกผล</button>
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
            
            // --- 1. บันทึก Section 1 (Discharge Info) ---
            document.getElementById('saveDischargeBtn').addEventListener('click', function() {
                const formData = new FormData(document.getElementById('dischargeInfoForm'));
                
                fetch('save_followup_discharge.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', timer: 1000, showConfirmButton: false });
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

                fetch('api_followup.php', { method: 'POST', body: formData })
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

                            tableBody.innerHTML += `
                                <tr>
                                    <td><strong>${row.followup_label}</strong></td>
                                    <td>${row.scheduled_date}</td>
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
            if(admissionId) loadTable();

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

                        fetch('api_followup.php', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if(data.status === 'success') {
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
                    if(confirm('ยืนยันว่าผู้ป่วยไม่มาตามนัด?')) {
                        updateFollowup(btn.dataset.id, 'no_show', null);
                    }
                }
            });

            // --- 5. บันทึกผลจาก Modal ---
            document.getElementById('saveMrsScoreBtn').addEventListener('click', function() {
                const id = document.getElementById('currentFollowupId').value;
                const score = document.getElementById('mrsScoreSelect').value;
                
                if(score === "") { alert("กรุณาเลือกคะแนน"); return; }
                
                updateFollowup(id, 'attended', score);
                editMrsModal.hide();
            });

            function updateFollowup(id, status, score) {
                const formData = new FormData();
                formData.append('action', 'update_item');
                formData.append('admission_id', admissionId);
                formData.append('followup_id', id);
                formData.append('status', status);
                if(score !== null) formData.append('mrs_score', score);

                fetch('api_followup.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        loadTable(); // รีโหลดตารางเพื่ออัปเดตสถานะ
                    }
                });
            }

        });
    </script>
</body>
</html>