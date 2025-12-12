<?php
session_start();
require_once 'connectdb.php'; // เชื่อมต่อฐานข้อมูล

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user_data'];

// --- ส่วนดึงข้อมูลเก่า (Edit Mode) ---
$admission_id = $_GET['admission_id'] ?? '';
$row = []; 

// 1. ดึงข้อมูลสรุป (Summary) จาก tbl_ward
if ($admission_id) {
    $sql = "SELECT * FROM tbl_ward WHERE admission_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
}

// 2. ดึงข้อมูล Monitoring (เพื่อเอาไปวนลูปแสดงในตาราง)
$monitor_rows = [];
if ($admission_id) {
    $sql_mon = "SELECT * FROM tbl_ward_monitoring WHERE admission_id = ? ORDER BY record_datetime ASC";
    $stmt_mon = $conn->prepare($sql_mon);
    $stmt_mon->bind_param("i", $admission_id);
    $stmt_mon->execute();
    $res_mon = $stmt_mon->get_result();
    while($m = $res_mon->fetch_assoc()) {
        $monitor_rows[] = $m;
    }
}

// --- ฟังก์ชันช่วยแสดงผล ---
function val($field) { global $row; return htmlspecialchars($row[$field] ?? ''); }
function chk($field, $value = 1) { global $row; return (isset($row[$field]) && $row[$field] == $value) ? 'checked' : ''; }
function sel($field, $value) { global $row; return (isset($row[$field]) && $row[$field] == $value) ? 'selected' : ''; }
function dt($field, $type) { 
    global $row; 
    if (empty($row[$field])) return '';
    $dt = explode(' ', $row[$field]);
    if ($type == 'd') return $dt[0]; // วันที่
    if ($type == 't') return substr($dt[1], 0, 5); // เวลา
    return '';
}
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>4. Ward - ระบบ Stroke Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <a href="form.php?admission_id=<?= $admission_id ?>">
            <i class="bi bi-person-lines-fill"></i> 1. ข้อมูลทั่วไป
        </a>
        <a href="diagnosis_form.php?admission_id=<?= $admission_id ?>">
            <i class="bi bi-hospital"></i> 2. ER
        </a>
        <a href="OR_Procedure_Form.php?admission_id=<?= $admission_id ?>">
            <i class="bi bi-scissors"></i> 3. OR Procedure
        </a>
        <a href="ward.php?admission_id=<?= $admission_id ?>" class="active">
            <i class="bi bi-building-check"></i> 4. Ward
        </a>
        <a href="follow.php?admission_id=<?= $admission_id ?>">
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
                <i class="bi bi-building-check"></i>
            </div>
            <div class="title-container">
                <h1>4. Ward Monitoring</h1>
                <p class="subtitle mb-0">หน้าจอหอผู้ป่วย (Flowsheet)</p>
            </div>
        </div>

        <div class="section-title">
            <i class="bi bi-graph-up"></i> 1. การเฝ้าระวัง (Monitoring)
        </div>
        <div class="mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEntryModal">
                <i class="bi bi-plus-circle"></i> เพิ่มบันทึกการเฝ้าระวัง
            </button>
        </div>
        
        <table class="table table-striped table-hover table-bordered">
            <thead class="table-light">
                <tr>
                    <th>วันที่/เวลา</th>
                    <th>SBP(mmHg)</th>
                    <th>DBP(mmHg)</th>
                    <th>NIHSS</th>
                    <th>GCS</th>
                </tr>
            </thead>
            <tbody id="monitoringTableBody">
                <?php if (count($monitor_rows) > 0): ?>
                    <?php foreach ($monitor_rows as $m): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($m['record_datetime'])) ?></td>
                            <td><?= $m['sbp'] ?></td>
                            <td><?= $m['dbp'] ?></td>
                            <td><?= $m['nihss'] ?></td>
                            <td><?= htmlspecialchars($m['gcs']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">ยังไม่มีข้อมูล กดปุ่มเพิ่มบันทึกเพื่อเริ่มเก็บข้อมูล</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <form id="wardSummaryForm" onsubmit="return false;">
            <input type="hidden" name="admission_id" value="<?php echo $admission_id; ?>">

            <div class="section-title">
                <i class="bi bi-clipboard2-data"></i> 2. การตรวจติดตาม
            </div>
            <div class="row g-3">
                <div class="col-md-3 mb-3">
                    <label for="ctFirstDay" class="form-label">ส่งตรวจ CT brain</label>
                    <input type="date" class="form-control" id="ctFirstDay" name="ct_date" value="<?= dt('followup_ct_datetime', 'd') ?>">
                    <input type="time" class="form-control mt-2" name="ct_time" value="<?= dt('followup_ct_datetime', 't') ?>">
                </div>
                <div class="col-3 mb-3">
                    <label class="form-label fw-bold" for="ct_result_ward">ผล:</label>
                    <input type="text" class="form-control" id="ct_result_ward" name="ct_result" value="<?= val('followup_ct_result') ?>">
                </div>
            </div>

            <div class="section-title">
                <i class="bi bi-person-check"></i> 3. อาการก่อนจำหน่าย
            </div>
            <div class="row mb-4">
                <div class="col-md-2">
                    <label for="discharge_check_date">ประจำวันที่</label>
                    <input type="date" class="form-control" id="discharge_check_date" name="dc_assess_date" value="<?= dt('discharge_assess_datetime', 'd') ?>">
                </div>
                <div class="col-md-2 mt-auto">
                    <input type="time" class="form-control" name="dc_assess_time" value="<?= dt('discharge_assess_datetime', 't') ?>">
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="mrsDischarge" class="form-label">mRS (ณ วันจำหน่าย)</label>
                    <select class="form-select" id="mrsDischarge" name="mrsDischarge">
                        <option value="" disabled <?= sel('discharge_mrs', '') ?>>-- กรุณาเลือก --</option>
                        <?php for($i=0;$i<=6;$i++) echo "<option value='$i' ".sel('discharge_mrs', $i).">$i</option>"; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="barthel" class="form-label">Barthel Index</label>
                    <input type="number" class="form-control" id="barthel" name="barthel" value="<?= val('discharge_barthel') ?>">
                </div>
                <div class="col-md-3">
                    <label for="hrs" class="form-label">HRS</label>
                    <input type="number" class="form-control" id="hrs" name="hrs" value="<?= val('discharge_hrs') ?>">
                </div>
            </div>

            <div class="section-title">
                <i class="bi bi-person-check"></i> 4. สภาพจำหน่าย
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">การวางแผนจำหน่าย</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="discharge_planning" id="plan_came" value="came" <?= chk('discharge_plan_status', 'came') ?>>
                        <label class="form-check-label" for="plan_came">มา</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="discharge_planning" id="plan_not_came" value="not_came" <?= chk('discharge_plan_status', 'not_came') ?>>
                        <label class="form-check-label" for="plan_not_came">ไม่มา</label>
                    </div>
                    
                    <div class="mt-3" id="discharge_date_field" style="<?= (isset($row['discharge_plan_status']) && $row['discharge_plan_status'] == 'came') ? '' : 'display: none;' ?>">
                        <label for="discharge_date_input" class="form-label">วันที่:</label>
                        <input type="date" class="form-control" id="discharge_date_input" name="discharge_date" value="<?= val('discharge_date') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">สถานะจำหน่าย</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="discharge_status" id="status_recovery" value="recovery" <?= chk('discharge_status', 'recovery') ?>>
                        <label class="form-check-label" for="status_recovery">complete recovery</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="discharge_status" id="status_improve" value="improve" <?= chk('discharge_status', 'improve') ?>>
                        <label class="form-check-label" for="status_improve">improve</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="discharge_status" id="status_disability" value="disability" <?= chk('discharge_status', 'disability') ?>>
                        <label class="form-check-label" for="status_disability">Disability</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="discharge_status" id="status_refer" value="refer" <?= chk('discharge_status', 'refer') ?>>
                        <label class="form-check-label" for="status_refer">Refer back</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="discharge_status" id="status_against" value="against" <?= chk('discharge_status', 'against') ?>>
                        <label class="form-check-label" for="status_against">against advice</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="discharge_status" id="status_death" value="death" <?= chk('discharge_status', 'death') ?>>
                        <label class="form-check-label" for="status_death">Death</label>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5 mb-5">
                <button type="button" class="btn btn-primary btn-lg px-5" id="saveWardSummaryBtn">
                    <i class="bi bi-save-fill me-2"></i> บันทึกข้อมูล Ward
                </button>
                <a href="follow.php?admission_id=<?= $admission_id ?>" id="nextStepBtn" class="btn btn-success btn-lg px-5 d-none ms-2">
                    ไปยังหน้า 5 (Follow-up) <i class="bi bi-arrow-right-circle-fill ms-2"></i>
                </a>
            </div>
        </form>
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
                        <input type="hidden" name="admission_id" value="<?php echo $admission_id; ?>">
                        
                        <div class="mb-3">
                            <label for="modalDateTime" class="form-label">วันที่/เวลา (Date/Time)</label>
                            <input type="datetime-local" class="form-control" id="modalDateTime" name="record_datetime">
                        </div>
                        <div class="mb-3">
                            <label for="modalSBP" class="form-label">SBP (mmHg)</label>
                            <input type="number" class="form-control" id="modalSBP" name="sbp">
                        </div>
                        <div class="mb-3">
                            <label for="modalDBP" class="form-label">DBP(mmHg)</label>
                            <input type="number" class="form-control" id="modalDBP" name="dbp">
                        </div>
                        <div class="mb-3">
                            <label for="modalNIHSS" class="form-label">NIHSS (ประเมินซ้ำ)</label>
                            <input type="number" class="form-control" id="modalNIHSS" name="nihss">
                        </div>
                        <div class="mb-3">
                            <label for="modalGCS" class="form-label">GCS (E_M_V_)</label>
                            <input type="text" class="form-control" id="modalGCS" name="gcs" placeholder="เช่น E4M6V5 (15)">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-primary" id="saveWardEntryBtn">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // 1. Logic ซ่อน/แสดง วันที่จำหน่าย
            const dischargeRadios = document.querySelectorAll('input[name="discharge_planning"]');
            const dischargeDateField = document.getElementById('discharge_date_field');
            const dischargeDateInput = document.getElementById('discharge_date_input');
            
            function updateDischargeField() {
                const selectedValue = document.querySelector('input[name="discharge_planning"]:checked');
                if (selectedValue && selectedValue.value === 'came') {
                    dischargeDateField.style.display = 'block';
                } else {
                    dischargeDateField.style.display = 'none';
                    dischargeDateInput.value = ''; 
                }
            }
            dischargeRadios.forEach(radio => {
                radio.addEventListener('change', updateDischargeField);
            });
            // updateDischargeField(); // ไม่ต้องเรียก เพราะ PHP จัดการ style ให้แล้ว

            // 2. Logic บันทึก Modal (Monitoring)
            const saveEntryBtn = document.getElementById('saveWardEntryBtn');
            const entryForm = document.getElementById('wardEntryForm');
            const addEntryModal = new bootstrap.Modal(document.getElementById('addEntryModal'));
            const tableBody = document.getElementById('monitoringTableBody');

            if(saveEntryBtn) {
                saveEntryBtn.addEventListener('click', function() {
                    const formData = new FormData(entryForm);
                    if(!formData.get('record_datetime')) { Swal.fire('กรุณาระบุเวลา', '', 'warning'); return; }

                    fetch('save_ward_monitoring.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if(data.status === 'success') {
                            Swal.fire({ title: 'บันทึกสำเร็จ', icon: 'success', timer: 1000, showConfirmButton: false });
                            addEntryModal.hide();
                            entryForm.reset();
                            // Reload หน้าเว็บเพื่อแสดงข้อมูลใหม่ในตาราง
                            location.reload(); 
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
                });
            }

            // 3. Logic บันทึก Summary
            const saveSummaryBtn = document.getElementById('saveWardSummaryBtn');
            const nextButton = document.getElementById('nextStepBtn');
            const summaryForm = document.getElementById('wardSummaryForm');

            // โชว์ปุ่ม Next ถ้ามี ID อยู่แล้ว
            const admissionId = document.querySelector('input[name="admission_id"]').value;
            if(admissionId) {
                nextButton.classList.remove('d-none');
            }

            if(saveSummaryBtn) {
                saveSummaryBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    Swal.showLoading();
                    const formData = new FormData(summaryForm);

                    fetch('save_ward_summary.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if(data.status === 'success') {
                            Swal.fire({ title: 'สำเร็จ!', icon: 'success', timer: 1500, showConfirmButton: false });
                            saveSummaryBtn.classList.replace('btn-primary', 'btn-secondary');
                            saveSummaryBtn.innerHTML = '<i class="bi bi-check-lg"></i> บันทึกแล้ว';
                            nextButton.classList.remove('d-none');
                            nextButton.href = data.redirect_url;
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>