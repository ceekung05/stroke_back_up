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
$row = []; // ตัวแปรเก็บข้อมูล

if ($admission_id) {
    $sql = "SELECT * FROM tbl_or_procedure WHERE admission_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
}

// --- ฟังก์ชันช่วยแสดงผล ---
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
    <title>3. OR Procedure - ระบบ Stroke Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <a href="form.php?admission_id=<?= $admission_id ?>">
            <i class="bi bi-person-lines-fill"></i> 1. ข้อมูลทั่วไป
        </a>
        <a href="diagnosis_form.php?admission_id=<?= $admission_id ?>">
            <i class="bi bi-hospital"></i> 2. ER
        </a>
        <a href="OR_Procedure_Form.php?admission_id=<?= $admission_id ?>" class="active">
            <i class="bi bi-scissors"></i> 3. OR Procedure
        </a>
        <a href="ward.php?admission_id=<?= $admission_id ?>">
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
                <i class="bi bi-scissors"></i>
            </div>
            <div class="title-container">
                <h1>3. OR Procedure</h1>
                <p class="subtitle mb-0">บันทึกข้อมูลการผ่าตัดและหัตถการห้องปฏิบัติการ</p>
            </div>
        </div>

        <form id="orForm" onsubmit="return false;">
            <input type="hidden" name="admission_id" value="<?= $admission_id ?>">

            <div class="card-form">
                <div class="section-title" style="margin-top:0;">
                    <i class="bi bi-check2-square"></i> 1. ประเภทหัตถการ (Procedure Type)
                </div>
                <div class="p-3 bg-light rounded border">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="procType" id="procTypeMT" value="mt" <?= chk('procedure_type', 'mt') ?>>
                        <label class="form-check-label fs-5 fw-bold text-primary" for="procTypeMT">
                            1. Mechanical Thrombectomy (สำหรับ Ischemic Stroke)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="procType" id="procTypeHemo" value="hemo" <?= chk('procedure_type', 'hemo') ?>>
                        <label class="form-check-label fs-5 fw-bold text-danger" for="procTypeHemo">
                            2. Neurosurgery (สำหรับ Hemorrhagic Stroke)
                        </label>
                    </div>
                </div>
            </div>

            <div id="mtProcedure" class="<?= (isset($row['procedure_type']) && $row['procedure_type'] == 'mt') ? '' : 'd-none' ?>">

                <div class="card-form" style="border-top: 5px solid var(--primary-color);">
                    <div class="section-title text-primary" style="margin-top:0;">
                        <i class="bi bi-stopwatch"></i> A.1 ลำดับเวลา (Timeline)
                    </div>
                    <div class="row g-4">
                        <div class="col-md-4 border-end">
                            <label class="form-label fw-bold">1. Anesthesia Time</label>
                            <div class="input-group input-group-sm">
                                <input type="date" class="form-control" id="anesthesia_time_mt" name="mt_anesthesia_date" value="<?= dt('mt_anesthesia_datetime', 'd') ?>">
                                <input type="time" class="form-control" name="mt_anesthesia_time" value="<?= dt('mt_anesthesia_datetime', 't') ?>">
                            </div>
                        </div>
                        <div class="col-md-4 border-end">
                            <label class="form-label fw-bold">2. Puncture Time</label>
                            <div class="input-group input-group-sm">
                                <input type="date" class="form-control" id="puncture_time_mt" name="mt_puncture_date" value="<?= dt('mt_puncture_datetime', 'd') ?>">
                                <input type="time" class="form-control" name="mt_puncture_time" value="<?= dt('mt_puncture_datetime', 't') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">3. Recanalization Time</label>
                            <div class="input-group input-group-sm">
                                <input type="date" class="form-control" id="recanalization_time" name="mt_recanalization_date" value="<?= dt('mt_recanalization_datetime', 'd') ?>">
                                <input type="time" class="form-control" name="mt_recanalization_time" value="<?= dt('mt_recanalization_datetime', 't') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-form">
                    <div class="section-title" style="margin-top:0;">
                        <i class="bi bi-clipboard-data"></i> A.2 ผลลัพธ์และเทคนิค (Result & Technique)
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3 mb-3">
                            <label for="occlusionvessel" class="form-label fw-bold text-muted">Occlusion Vessel</label>
                            <select class="form-select" id="occlusionvessel" name="mt_occlusion_vessel">
                                <option value="" disabled <?= sel('mt_occlusion_vessel', '') ?>>-- เลือกตำแหน่ง --</option>
                                <option value="Left ICA" <?= sel('mt_occlusion_vessel', 'Left ICA') ?>>Cervical ICA left</option>
                                <option value="Right ICA" <?= sel('mt_occlusion_vessel', 'Right ICA') ?>>Cervical ICA Right</option>
                                <option value="Left M1 of MCA" <?= sel('mt_occlusion_vessel', 'Left M1 of MCA') ?>>Left M1 of MCA</option>
                                <option value="Right M1 of MCA" <?= sel('mt_occlusion_vessel', 'Right M1 of MCA') ?>>Right M1 of MCA</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="procedureTechnique" class="form-label fw-bold text-muted">Technique</label>
                            <select class="form-select" id="procedureTechnique" name="mt_procedure_technique">
                                <option value="" disabled <?= sel('mt_procedure_technique', '') ?>>-- เลือกวิธีการ --</option>
                                <option value="aspiration alone" <?= sel('mt_procedure_technique', 'aspiration alone') ?>>aspiration alone</option>
                                <option value="stent alone" <?= sel('mt_procedure_technique', 'stent alone') ?>>stent alone</option>
                                <option value="Solumbra" <?= sel('mt_procedure_technique', 'Solumbra') ?>>Solumbra</option>
                                <option value="combined" <?= sel('mt_procedure_technique', 'combined') ?>>combined</option>
                                <option value="primary stent" <?= sel('mt_procedure_technique', 'primary stent') ?>>primary stent</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="pass_count" class="form-label fw-bold text-muted">Pass Count (เปิดกี่ครั้ง)</label>
                            <select class="form-select" id="pass_count" name="mt_pass_count">
                                <option value="" disabled <?= sel('mt_pass_count', '') ?>>-- เลือกจำนวน --</option>
                                <?php for ($i = 1; $i <= 10; $i++) echo "<option value='$i' " . sel('mt_pass_count', $i) . ">$i ครั้ง</option>"; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="ticiScore" class="form-label fw-bold text-primary">TICI Score (ผลลัพธ์)</label>
                            <select class="form-select border-primary fw-bold text-primary" id="ticiScore" name="mt_tici_score">
                                <option value="" disabled <?= sel('mt_tici_score', '') ?>>-- เลือกผลลัพธ์ --</option>
                                <option value="0" <?= sel('mt_tici_score', '0') ?>>0 - No perfusion</option>
                                <option value="1" <?= sel('mt_tici_score', '1') ?>>1 - Minimal perfusion</option>
                                <option value="2a" <?= sel('mt_tici_score', '2a') ?>>2a - Partial ( < 50%)</option>
                                <option value="2b" <?= sel('mt_tici_score', '2b') ?>>2b - Partial ( > 50%)</option>
                                <option value="3" <?= sel('mt_tici_score', '3') ?>>3 - Complete perfusion</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-form">
                    <div class="section-title" style="margin-top:0;">
                        <i class="bi bi-capsule"></i> A.3 ยาระหว่างหัตถการ (Medication)
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="med_integrilin" name="mt_med_integrilin" <?= chk('mt_med_integrilin') ?>>
                                <label class="form-check-label fw-bold" for="med_integrilin">Integrilin</label>
                            </div>
                            <div class="ps-4">
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text">Bolus</span>
                                    <input type="number" step="0.01" class="form-control" name="mt_integrilin_bolus" value="<?= val('mt_integrilin_bolus') ?>">
                                    <span class="input-group-text">ml</span>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Drip rate</span>
                                    <input type="number" step="0.01" class="form-control" name="mt_integrilin_drip" value="<?= val('mt_integrilin_drip') ?>">
                                    <span class="input-group-text">ml/hr</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="med_nimodipine" name="mt_med_nimodipine" <?= chk('mt_med_nimodipine') ?>>
                                <label class="form-check-label fw-bold" for="med_nimodipine">Nimodipine</label>
                            </div>
                            <div class="ps-4">
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text">Bolus</span>
                                    <input type="number" step="0.01" class="form-control" name="mt_nimodipine_bolus" value="<?= val('mt_nimodipine_bolus') ?>">
                                    <span class="input-group-text">ml</span>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Drip rate</span>
                                    <input type="number" step="0.01" class="form-control" name="mt_nimodipine_drip" value="<?= val('mt_nimodipine_drip') ?>">
                                    <span class="input-group-text">ml/hr</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-form">
                    <div class="section-title" style="margin-top:0;">
                        <i class="bi bi-radioactive"></i> A.4 Radiation & Imaging
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3 mb-3">
                            <label for="xrayDose" class="form-label fw-bold">Dose X-ray (mGy)</label>
                            <input type="number" step="0.01" class="form-control" id="xrayDose" name="mt_xray_dose" value="<?= val('mt_xray_dose') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="flu-time" class="form-label fw-bold">Flu time (min)</label>
                            <input type="number" step="0.01" class="form-control" id="flu-time" name="mt_flu_time" value="<?= val('mt_flu_time') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">ConeBeam CT: Detection of intracranial hemorrhage</label>
                            <div class="d-flex align-items-center gap-3">
                                <div>
                                    <input class="form-check-input" type="radio" name="mt_cone_beam_ct" id="coneBeam_yes" value="yes" <?= chk('mt_cone_beam_ct', 1) ?>>
                                    <label class="form-check-label" for="coneBeam_yes"> Yes </label>
                                </div>
                                <div>
                                    <input class="form-check-input" type="radio" name="mt_cone_beam_ct" id="coneBeam_no" value="no" <?= chk('mt_cone_beam_ct', 0) ?>>
                                    <label class="form-check-label" for="coneBeam_no"> No </label>
                                </div>
                            </div>
                            <div id="coneBeam_yes_details" class="mt-2 <?= (isset($row['mt_cone_beam_ct']) && $row['mt_cone_beam_ct'] == 1) ? '' : 'd-none' ?>">
                                <input type="text" class="form-control form-control-sm" name="mt_cone_beam_ct_details" value="<?= val('mt_cone_beam_ct_details') ?>" placeholder="ระบุรายละเอียดความผิดปกติ...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="hemoProcedure" class="<?= (isset($row['procedure_type']) && $row['procedure_type'] == 'hemo') ? '' : 'd-none' ?>">
                <div class="card-form card-alert" style="border-left: 5px solid var(--accent-color);">
                    <div class="section-title text-danger" style="margin-top:0;">
                        <i class="bi bi-bandaid"></i> B. Neurosurgery (Hemorrhagic)
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="hemoLocation" class="form-label fw-bold">Location (ตำแหน่งเลือดออก)</label>
                            <input type="text" class="form-control" id="hemoLocation" name="hemo_location" value="<?= val('hemo_location') ?>" placeholder="ระบุตำแหน่ง...">
                        </div>
                        <div class="col-md-6">
                            <label for="hemoCC" class="form-label fw-bold">Hemorrhage Volume (CC)</label>
                            <input type="number" step="0.01" class="form-control" id="hemoCC" name="hemo_volume_cc" value="<?= val('hemo_volume_cc') ?>" placeholder="0.00">
                        </div>
                    </div>

                    <h6 class="fw-bold text-secondary mb-2">หัตถการที่ทำ (Procedure performed)</h6>
                    <div class="p-3 bg-white rounded border d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="procCranio" name="hemo_proc_craniotomy" value="1" <?= chk('hemo_proc_craniotomy') ?>>
                            <label class="form-check-label" for="procCranio">Craniotomy</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="procCraniectomy" name="hemo_proc_craniectomy" value="1" <?= chk('hemo_proc_craniectomy') ?>>
                            <label class="form-check-label" for="procCraniectomy">Craniectomy</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="procVentriculostomy" name="hemo_proc_ventriculostomy" value="1" <?= chk('hemo_proc_ventriculostomy') ?>>
                            <label class="form-check-label" for="procVentriculostomy">Ventriculostomy</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-form">
                <div class="section-title" style="margin-top:0;">
                    <i class="bi bi-exclamation-triangle"></i> ภาวะแทรกซ้อน (Complications)
                </div>
                <div class="col-md-6">
                    <select class="form-select" id="complicationLog" name="complication_details">
                        <option value="" disabled <?= sel('complication_details', '') ?>>...เลือกภาวะแทรกซ้อน...</option>
                        <option value="ไม่มีภาวะแทรกซ้อน" <?= sel('complication_details', 'ไม่มีภาวะแทรกซ้อน') ?>>ไม่มีภาวะแทรกซ้อน</option>
                        <option value="มีภาวะเลือดออกในสมอง" <?= sel('complication_details', 'มีภาวะเลือดออกในสมอง') ?>>มีภาวะเลือดออกในสมอง</option>
                        <option value="การบาดเจ็บต่อหลอดเลือด เช่น ทะลุ ฉีดขาด" <?= sel('complication_details', 'การบาดเจ็บต่อหลอดเลือด เช่น ทะลุ ฉีดขาด') ?>>การบาดเจ็บต่อหลอดเลือด เช่น ทะลุ ฉีกขาด</option>
                        <option value="มีการอุดตันของหลอดเลือดซ้ำ" <?= sel('complication_details', 'มีการอุดตันของหลอดเลือดช้ำ') ?>>มีการอุดตันของหลอดเลือดซ้ำ</option>
                        <option value="หลอดเลือดมีการหดเกร็ง" <?= sel('complication_details', 'หลอดเลือดมีการหดเกร็ง') ?>>หลอดเลือดมีการหดเกร็ง</option>
                    </select>
                </div>
            </div>

            <div class="text-center mt-5 mb-5">
                <button type="button" class="btn btn-primary btn-lg px-5 py-3 shadow" id="saveOrBtn" style="border-radius: 50px;">
                    <i class="bi bi-save-fill me-2"></i> บันทึกข้อมูล OR
                </button>
                <a href="ward.php?admission_id=<?= $admission_id ?>"
                    id="nextStepBtn"
                    class="btn btn-success btn-lg px-5 ms-2 py-3 shadow <?= empty($row) ? 'd-none' : '' ?>"
                    style="border-radius: 50px;">
                    ไปยังหน้า 4 (Ward) <i class="bi bi-arrow-right-circle-fill ms-2"></i>
                </a>
            </div>

        </form>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const radioMT = document.getElementById('procTypeMT');
            const radioHemo = document.getElementById('procTypeHemo');
            const mtProcedure = document.getElementById('mtProcedure');
            const hemoProcedure = document.getElementById('hemoProcedure');

            radioMT.addEventListener('change', () => {
                if (radioMT.checked) {
                    mtProcedure.classList.remove('d-none');
                    hemoProcedure.classList.add('d-none');
                }
            });

            radioHemo.addEventListener('change', () => {
                if (radioHemo.checked) {
                    mtProcedure.classList.add('d-none');
                    hemoProcedure.classList.remove('d-none');
                }
            });

            const coneBeamYes = document.getElementById('coneBeam_yes');
            const coneBeamNo = document.getElementById('coneBeam_no');
            const coneBeamYesDetails = document.getElementById('coneBeam_yes_details');

            coneBeamYes.addEventListener('change', () => {
                if (coneBeamYes.checked) {
                    coneBeamYesDetails.classList.remove('d-none');
                }
            });

            coneBeamNo.addEventListener('change', () => {
                if (coneBeamNo.checked) {
                    coneBeamYesDetails.classList.add('d-none');
                }
            });

            // --- ส่วนบันทึกข้อมูล (AJAX) ---
            const saveButton = document.getElementById('saveOrBtn');
            const nextButton = document.getElementById('nextStepBtn');
            const orForm = document.getElementById('orForm');

            if (saveButton) {
                saveButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'ยืนยันการบันทึก',
                        text: "ต้องการบันทึกข้อมูล OR ใช่หรือไม่?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'ใช่, บันทึกเลย'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // แสดง Loading
                            Swal.fire({
                                title: 'กำลังบันทึก...',
                                didOpen: () => Swal.showLoading()
                            });

                            const formData = new FormData(orForm);

                            fetch('save_or.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'สำเร็จ!',
                                            text: 'บันทึกข้อมูล OR เรียบร้อยแล้ว',
                                            timer: 1500,
                                            showConfirmButton: false
                                        });

                                        // เปลี่ยนปุ่ม
                                        saveButton.classList.replace('btn-primary', 'btn-secondary');
                                        saveButton.innerHTML = '<i class="bi bi-check-lg"></i> บันทึกแล้ว';

                                        // โชว์ปุ่มไปหน้า Ward
                                        nextButton.classList.remove('d-none');
                                        nextButton.href = data.redirect_url;
                                    } else {
                                        Swal.fire('Error', data.message, 'error');
                                    }
                                })
                                .catch(err => {
                                    console.error(err);
                                    Swal.fire('Error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
                                });
                        }
                    });
                });
            }
        });
    </script>
</body>

</html>