<?php
session_start();
require_once 'connectdb.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user_data'];

$admission_id = $_GET['admission_id'] ?? '';
$row = [];

// 1. ดึงข้อมูลสรุป
if ($admission_id) {
    $sql = "SELECT * FROM tbl_ward WHERE admission_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
}

// 2. ดึงข้อมูล Monitoring
$monitor_rows = [];
if ($admission_id) {
    $sql_mon = "SELECT * FROM tbl_ward_monitoring WHERE admission_id = ? ORDER BY record_datetime ASC";
    $stmt_mon = $conn->prepare($sql_mon);
    $stmt_mon->bind_param("i", $admission_id);
    $stmt_mon->execute();
    $res_mon = $stmt_mon->get_result();
    while ($m = $res_mon->fetch_assoc()) {
        $monitor_rows[] = $m;
    }
}

function val($field)
{
    global $row;
    return htmlspecialchars($row[$field] ?? '');
}
function chk($field, $value = 1)
{
    global $row;
    return (isset($row[$field]) && $row[$field] == $value) ? 'checked' : '';
}
function sel($field, $value)
{
    global $row;
    return (isset($row[$field]) && $row[$field] == $value) ? 'selected' : '';
}
function dt($field, $type)
{
    global $row;
    if (empty($row[$field])) return '';
    $dt = explode(' ', $row[$field]);
    if ($type == 'd') return $dt[0];
    if ($type == 't') return substr($dt[1], 0, 5);
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <i class="bi bi-heart-pulse-fill"></i>
            <span>Stroke Care</span>
        </div>
        <hr class="sidebar-divider"><a href="dashboard.php">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
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
                <p class="subtitle mb-0">หน้าจอเฝ้าระวังและสรุปผลก่อนจำหน่าย (Flowsheet)</p>
            </div>
        </div>

        <div class="card-form">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="section-title mb-0 border-0 p-0">
                    <i class="bi bi-graph-up-arrow text-primary"></i> 1. การเฝ้าระวัง (Monitoring Log)
                </div>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addEntryModal">
                    <i class="bi bi-plus-lg me-1"></i> เพิ่มบันทึก
                </button>
            </div>

            <div class="table-responsive rounded-4 border">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 25%;">วันที่/เวลา (Date/Time)</th>
                            <th class="text-center">SBP (mmHg)</th>
                            <th class="text-center">DBP (mmHg)</th>
                            <th class="text-center">NIHSS</th>
                            <th class="text-center">GCS</th>
                            <th class="text-end" style="width: 10%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="monitoringTableBody">
                        <?php if (count($monitor_rows) > 0): ?>
                            <?php foreach ($monitor_rows as $m): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-container bg-light text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-size: 1rem;">
                                                <i class="bi bi-calendar-event"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= date('d/m/Y', strtotime($m['record_datetime'])) ?></div>
                                                <div class="small text-muted"><?= date('H:i', strtotime($m['record_datetime'])) ?> น.</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-dark fs-5"><?= $m['sbp'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-secondary fs-5"><?= $m['dbp'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-soft badge-soft-warning">
                                            <?= $m['nihss'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-soft badge-soft-primary">
                                            <?= htmlspecialchars($m['gcs']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-light text-muted border-0" title="แก้ไข">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="opacity-50 mb-2">
                                        <i class="bi bi-clipboard-data" style="font-size: 3rem; color: #cbd5e1;"></i>
                                    </div>
                                    <h6 class="text-muted fw-bold">ยังไม่มีข้อมูลการเฝ้าระวัง</h6>
                                    <p class="small text-muted mb-3">กดปุ่ม "เพิ่มบันทึก" เพื่อเริ่มติดตามอาการผู้ป่วย</p>
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addEntryModal">
                                        <i class="bi bi-plus-lg me-1"></i> เพิ่มข้อมูลแรก
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <form id="wardSummaryForm" onsubmit="return false;">
            <input type="hidden" name="admission_id" value="<?php echo $admission_id; ?>">

            <div class="card-form">
                <div class="section-title" style="margin-top:0;">
                    <i class="bi bi-clipboard2-pulse-fill text-info"></i> 2. การตรวจติดตาม (Investigation)
                </div>

                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="input-box-card h-100">
                            <label class="form-label text-muted small fw-bold mb-2 text-uppercase">
                                <i class="bi bi-calendar-check me-1 text-info"></i> ส่งตรวจ CT brain (Follow up)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 rounded-start-4 ps-3">
                                    <i class="bi bi-calendar3 text-info"></i>
                                </span>
                                <input type="date" class="form-control border-start-0" id="ctFirstDay" name="ct_date"
                                    value="<?= dt('followup_ct_datetime', 'd') ?>">

                                <span class="input-group-text bg-white border-start-0 border-end-0">
                                    <i class="bi bi-clock text-info"></i>
                                </span>
                                <input type="text" class="form-control timepicker border-start-0 rounded-end-4 text-center fw-bold text-info"
                                    placeholder="เวลา" name="ct_time" value="<?= dt('followup_ct_datetime', 't') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="input-box-card h-100">
                            <label class="form-label text-muted small fw-bold mb-2 text-uppercase">
                                <i class="bi bi-file-earmark-medical-fill me-1 text-primary"></i> ผลตรวจ (Result)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 rounded-start-4 ps-3">
                                    <i class="bi bi-search text-primary"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 rounded-end-4 fw-bold text-dark"
                                    id="ct_result_ward" name="ct_result"
                                    value="<?= val('followup_ct_result') ?>" placeholder="ระบุผล CT...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-form">
                <div class="section-title" style="margin-top:0;">
                    <i class="bi bi-person-check-fill text-primary"></i> 3. ประเมินอาการก่อนจำหน่าย (Discharge Assessment)
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="input-box-card h-100">
                            <label class="form-label text-muted small fw-bold mb-2">
                                <i class="bi bi-calendar-check me-1 text-primary"></i> วันที่ประเมิน (Assessment Date)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 rounded-start-4 ps-3">
                                    <i class="bi bi-calendar3 text-primary"></i>
                                </span>
                                <input type="date" class="form-control border-start-0" id="discharge_check_date" name="dc_assess_date"
                                    value="<?= dt('discharge_assess_datetime', 'd') ?>">

                                <span class="input-group-text bg-white border-start-0 border-end-0">
                                    <i class="bi bi-clock text-primary"></i>
                                </span>
                                <input type="text" class="form-control timepicker border-start-0 rounded-end-4 text-center fw-bold text-primary"
                                    placeholder="เวลา" name="dc_assess_time" value="<?= dt('discharge_assess_datetime', 't') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="input-box-card h-100 px-4 d-flex flex-column justify-content-center">
                            <label class="form-label text-muted small fw-bold mb-1 text-uppercase">
                                <i class="bi bi-calculator me-1 text-secondary"></i> Barthel Index
                            </label>
                            <div class="input-group input-group-lg border-bottom border-2 border-secondary border-opacity-25">
                                <input type="number" class="form-control fw-bold text-dark border-0 p-0 fs-3"
                                    id="barthel" name="barthel" value="<?= val('discharge_barthel') ?>" placeholder="0">
                                <span class="input-group-text bg-transparent border-0 text-muted small">Score</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="p-4 rounded-4 border border-2 border-light shadow-sm bg-white">
                            <label class="form-label fw-bold mb-3 text-secondary d-flex align-items-center">
                                <span class="icon-container bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; font-size: 1.2rem;">
                                    <i class="bi bi-sort-numeric-down"></i>
                                </span>
                                mRS (ณ วันจำหน่าย)
                            </label>

                            <div class="d-flex flex-wrap gap-2">
                                <?php for ($i = 0; $i <= 6; $i++): ?>
                                    <input type="radio" class="btn-check mrs-<?= $i ?>" name="mrsDischarge" id="mrs_dc_<?= $i ?>" value="<?= $i ?>" <?= chk('discharge_mrs', $i) ?>>
                                    <label class="mrs-option shadow-sm" for="mrs_dc_<?= $i ?>" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                        <?= $i ?>
                                    </label>
                                <?php endfor; ?>
                            </div>
                            <div class="mt-3 small text-muted d-flex align-items-center">
                                <span class="badge bg-success bg-opacity-25 text-success me-2">0 = ปกติ</span>
                                <i class="bi bi-arrow-right text-muted me-2"></i>
                                <span class="badge bg-danger bg-opacity-25 text-danger">6 = เสียชีวิต</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-form">
                <div class="section-title" style="margin-top:0;">
                    <i class="bi bi-door-open-fill text-primary"></i> 4. สรุปสถานะจำหน่าย (Discharge Summary)
                </div>

                <div class="row g-4">
                    <div class="row g-4">
    <div class="col-md-6 border-end">
        <h6 class="fw-bold text-secondary mb-3">
            <i class="bi bi-list-check me-1"></i> 4.1 ประเภทการจำหน่าย
        </h6>
        
        <div class="row g-3">
            <div class="col-6">
                <input type="radio" class="btn-check" name="discharge_type" id="type_approval" value="approval" <?= chk('discharge_type', 'approval') ?>>
                <label class="selection-chip-card chip-success h-100 w-100 d-flex flex-column align-items-center justify-content-center py-4 text-center" for="type_approval">
                    <i class="bi bi-check-circle-fill fs-2 mb-2"></i> 
                    <div class="fw-bold">With Approval</div>
                    <div class="small opacity-75">(แพทย์อนุญาต)</div>
                </label>
            </div>

            <div class="col-6">
                <input type="radio" class="btn-check" name="discharge_type" id="type_refer" value="refer" <?= chk('discharge_type', 'refer') ?>>
                <label class="selection-chip-card chip-primary h-100 w-100 d-flex flex-column align-items-center justify-content-center py-4 text-center" for="type_refer">
                    <i class="bi bi-arrow-right-circle-fill fs-2 mb-2"></i> 
                    <div class="fw-bold">Refer back</div>
                    <div class="small opacity-75">(ส่งต่อ)</div>
                </label>
            </div>

            <div class="col-6">
                <input type="radio" class="btn-check" name="discharge_type" id="type_against" value="against" <?= chk('discharge_type', 'against') ?>>
                <label class="selection-chip-card chip-warning h-100 w-100 d-flex flex-column align-items-center justify-content-center py-4 text-center" for="type_against">
                    <i class="bi bi-exclamation-triangle-fill fs-2 mb-2"></i> 
                    <div class="fw-bold">Against Advice</div>
                    <div class="small opacity-75">(ไม่สมัครใจอยู่)</div>
                </label>
            </div>

            <div class="col-6">
                <input type="radio" class="btn-check" name="discharge_type" id="type_death" value="death" <?= chk('discharge_type', 'death') ?>>
                <label class="selection-chip-card chip-danger h-100 w-100 d-flex flex-column align-items-center justify-content-center py-4 text-center" for="type_death">
                    <i class="bi bi-x-circle-fill fs-2 mb-2"></i> 
                    <div class="fw-bold">Death</div>
                    <div class="small opacity-75">(เสียชีวิต)</div>
                </label>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <h6 class="fw-bold text-secondary mb-3">
            <i class="bi bi-heart-pulse me-1"></i> 4.2 สถานะอาการ
        </h6>
        <div class="d-flex flex-column gap-3 h-100">
            <input type="radio" class="btn-check" name="discharge_status" id="status_recovery" value="recovery" <?= chk('discharge_status', 'recovery') ?>>
            <label class="selection-chip-card chip-success w-100 d-flex align-items-center p-3" for="status_recovery">
                <div class="rounded-circle bg-white bg-opacity-50 d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 45px; height: 45px;">
                    <i class="bi bi-emoji-smile-fill fs-4"></i>
                </div>
                <div class="text-start">
                    <div class="fw-bold">Complete Recovery</div>
                    <div class="small opacity-75">หายดี / ปกติ</div>
                </div>
            </label>

            <input type="radio" class="btn-check" name="discharge_status" id="status_improve" value="improve" <?= chk('discharge_status', 'improve') ?>>
            <label class="selection-chip-card chip-primary w-100 d-flex align-items-center p-3" for="status_improve">
                <div class="rounded-circle bg-white bg-opacity-50 d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 45px; height: 45px;">
                    <i class="bi bi-graph-up-arrow fs-4"></i>
                </div>
                <div class="text-start">
                    <div class="fw-bold">Improved</div>
                    <div class="small opacity-75">อาการดีขึ้น</div>
                </div>
            </label>

            <input type="radio" class="btn-check" name="discharge_status" id="status_not_improve" value="not_improved" <?= chk('discharge_status', 'not_improved') ?>>
            <label class="selection-chip-card chip-danger w-100 d-flex align-items-center p-3" for="status_not_improve">
                <div class="rounded-circle bg-white bg-opacity-50 d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 45px; height: 45px;">
                    <i class="bi bi-graph-down-arrow fs-4"></i>
                </div>
                <div class="text-start">
                    <div class="fw-bold">Not Improved</div>
                    <div class="small opacity-75">อาการไม่ดีขึ้น / ทรุดลง</div>
                </div>
            </label>
        </div>
    </div>
</div>

                    <div class="col-md-4">
                        <h6 class="fw-bold text-secondary mb-3">
                            <i class="bi bi-calendar-event me-1"></i> 4.3 การวางแผนจำหน่าย
                        </h6>

                        <div class="bg-white rounded-4 p-3 border shadow-sm">
                            <label class="form-label small fw-bold text-muted mb-2">สถานะการนัดหมาย (Appointment Status)</label>

                            <div class="btn-group w-100 mb-3" role="group">
                                <input type="radio" class="btn-check" name="discharge_planning" id="plan_came" value="came"
                                    <?= chk('discharge_plan_status', 'came') ?>
                                    onchange="document.getElementById('discharge_date_wrapper').classList.remove('d-none')">
                                <label class="btn btn-toggle-status btn-status-success py-2" for="plan_came">
                                    <i class="bi bi-check-circle-fill me-1"></i> มาตามนัด
                                </label>

                                <input type="radio" class="btn-check" name="discharge_planning" id="plan_not_came" value="not_came"
                                    <?= chk('discharge_plan_status', 'not_came') ?>
                                    onchange="document.getElementById('discharge_date_wrapper').classList.add('d-none')">
                                <label class="btn btn-toggle-status btn-status-danger py-2" for="plan_not_came">
                                    <i class="bi bi-x-circle-fill me-1"></i> ผิดนัด/ไม่มา
                                </label>
                            </div>

                            <div id="discharge_date_wrapper" class="bg-light rounded-3 p-2 border <?= (isset($row['discharge_plan_status']) && $row['discharge_plan_status'] == 'came') ? '' : 'd-none' ?>">
                                <label class="form-label small fw-bold text-success mb-1">
                                    <i class="bi bi-calendar-check me-1"></i> วันที่จำหน่ายจริง (Actual Date)
                                </label>
                                <input type="date" class="form-control border-success border-opacity-25 text-success fw-bold bg-white"
                                    id="discharge_date_input" name="discharge_date" value="<?= val('discharge_date') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5 mb-5">
                <button type="button" class="btn btn-primary btn-lg px-5 py-3 shadow" id="saveWardSummaryBtn" style="border-radius: 50px;">
                    <i class="bi bi-save-fill me-2"></i> บันทึกข้อมูล Ward
                </button>
                <a href="follow.php?admission_id=<?= $admission_id ?>" id="nextStepBtn" class="btn btn-success btn-lg px-5 ms-2 py-3 shadow d-none" style="border-radius: 50px;">
                    ไปยังหน้า 5 (Follow-up) <i class="bi bi-arrow-right-circle-fill ms-2"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="modal fade" id="addEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-file-earmark-plus-fill me-2"></i> เพิ่มบันทึกการเฝ้าระวัง
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 bg-light bg-opacity-10">
                <form id="wardEntryForm">
                    <input type="hidden" name="admission_id" value="<?php echo $admission_id; ?>">

                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary small text-uppercase">วันที่/เวลา (Date/Time)</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0 bg-white rounded-start-4 ps-3">
                                <i class="bi bi-calendar-event text-primary"></i>
                            </span>
                            <input type="datetime-local" class="form-control border-start-0 rounded-end-4" 
                                   id="modalDateTime" name="record_datetime" value="<?= date('Y-m-d\TH:i') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="p-3 bg-white border rounded-4 text-center h-100">
                                <label class="form-label fw-bold text-muted small mb-1">SBP</label>
                                <input type="number" class="form-control text-center fw-bold text-dark fs-4 border-0 p-0" 
                                       id="modalSBP" name="sbp" placeholder="0">
                                <span class="small text-muted">mmHg</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-white border rounded-4 text-center h-100">
                                <label class="form-label fw-bold text-muted small mb-1">DBP</label>
                                <input type="number" class="form-control text-center fw-bold text-dark fs-4 border-0 p-0" 
                                       id="modalDBP" name="dbp" placeholder="0">
                                <span class="small text-muted">mmHg</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-4 h-100">
                                <label class="form-label fw-bold text-warning text-dark small mb-1">NIHSS</label>
                                <input type="number" class="form-control fw-bold text-dark bg-transparent border-0 p-0" 
                                       id="modalNIHSS" name="nihss" placeholder="Score">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-info bg-opacity-10 border border-info border-opacity-25 rounded-4 h-100">
                                <label class="form-label fw-bold text-info text-dark small mb-1">GCS</label>
                                <input type="text" class="form-control fw-bold text-dark bg-transparent border-0 p-0" 
                                       id="modalGCS" name="gcs" placeholder="E_V_M_">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer border-top-0 px-4 pb-4 pt-0 bg-transparent">
                <button type="button" class="btn btn-light text-muted rounded-pill px-4 me-auto" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary rounded-pill px-5 shadow-sm" id="saveWardEntryBtn">
                    <i class="bi bi-save2 me-1"></i> บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // 1. Logic Show/Hide Date
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

            // 2. Logic บันทึก Monitoring (Modal)
            const saveEntryBtn = document.getElementById('saveWardEntryBtn');
            const entryForm = document.getElementById('wardEntryForm');
            const addEntryModal = new bootstrap.Modal(document.getElementById('addEntryModal'));

            if (saveEntryBtn) {
                saveEntryBtn.addEventListener('click', function() {
                    const formData = new FormData(entryForm);
                    if (!formData.get('record_datetime')) {
                        Swal.fire('กรุณาระบุเวลา', '', 'warning');
                        return;
                    }

                    fetch('save_ward_monitoring.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    title: 'บันทึกสำเร็จ',
                                    icon: 'success',
                                    timer: 1000,
                                    showConfirmButton: false
                                });
                                addEntryModal.hide();
                                entryForm.reset();
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
            const admissionId = document.querySelector('input[name="admission_id"]').value;

            if (admissionId) {
                nextButton.classList.remove('d-none');
            }

            if (saveSummaryBtn) {
                saveSummaryBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    Swal.showLoading();
                    const formData = new FormData(summaryForm);

                    fetch('save_ward_summary.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    title: 'สำเร็จ!',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
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

            // 4. Timepicker
            flatpickr(".timepicker", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                allowInput: true
            });
        });
    </script>
</body>

</html>