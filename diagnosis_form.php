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
$hospital_arrival_datetime = ""; 

if ($admission_id) {
    $sql = "SELECT * FROM tbl_er WHERE admission_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $sql_adm = "SELECT hospital_arrival_datetime FROM tbl_stroke_admission WHERE id = ?";
    $stmt_adm = $conn->prepare($sql_adm);
    $stmt_adm->bind_param("i", $admission_id);
    $stmt_adm->execute();
    $res_adm = $stmt_adm->get_result();
    $row_adm = $res_adm->fetch_assoc();
    if($row_adm) {
        $hospital_arrival_datetime = $row_adm['hospital_arrival_datetime'];
    }
}

function val($field) { global $row; return htmlspecialchars($row[$field] ?? ''); }
function chk($field, $value = 1) { global $row; return (isset($row[$field]) && $row[$field] == $value) ? 'checked' : ''; }
function sel($field, $value) { global $row; return (isset($row[$field]) && $row[$field] == $value) ? 'selected' : ''; }
function dt($field, $type) { 
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
    <title>2. ER - ระบบ Stroke Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="style.css">
    
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
        <hr class="sidebar-divider"><a href="dashboard.php" >
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="index.php">
            <i class="bi bi-list-task"></i> กลับไปหน้า Patient List
        </a>
        <hr class="sidebar-divider">
        <a href="form.php?admission_id=<?= $admission_id ?>">
            <i class="bi bi-person-lines-fill"></i> 1. ข้อมูลทั่วไป
        </a>
        <a href="diagnosis_form.php?admission_id=<?= $admission_id ?>" class="active">
            <i class="bi bi-hospital"></i> 2. ER
        </a>
        <a href="OR_Procedure_Form.php?admission_id=<?= $admission_id ?>">
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
                <i class="bi bi-hospital"></i>
            </div>
            <div class="title-container">
                <h1>2. ER Assessment</h1>
                <p class="subtitle mb-0">การวินิจฉัยและตัดสินใจที่ห้องฉุกเฉิน</p>
            </div>
        </div>

        <form id="erForm" onsubmit="return false;">
            <input type="hidden" name="admission_id" value="<?= $admission_id ?>">
            <input type="hidden" id="hospital_arrival_db" value="<?= $hospital_arrival_datetime ?>">
            
            <div class="card-form">
                <div class="section-title" style="margin-top:0;">
                    <i class="bi bi-clock-history"></i> 2. ลำดับเวลาการตรวจรักษา (Clinical Timeline)
                </div>
                
                <div class="alert alert-info py-2 mb-3">
                    <i class="bi bi-info-circle-fill"></i> เวลาที่ผู้ป่วยมาถึงโรงพยาบาล (Hospital Arrival): 
                    <strong><?= $hospital_arrival_datetime ? date('d/m/Y H:i', strtotime($hospital_arrival_datetime)) : 'ยังไม่ระบุ (กรุณาระบุในหน้า 1)' ?></strong>
                </div>

                <h6 class="fw-bold text-secondary mb-2">2.1 เวลาที่แพทย์ตรวจ</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label for="consult_neuro_date" class="form-label">วันที่ส่งปรึกษา</label>
                        <input type="date" id="consult_neuro_date" name="consult_neuro_date" class="form-control" value="<?= dt('consult_neuro_datetime', 'd') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="consult_neuro_time_input" class="form-label">เวลา</label>
                        <input  type="text" class="form-control timepicker"  placeholder="คลิกเพื่อเลือกเวลา" id="consult_neuro_time_input" name="consult_neuro_time_input"  value="<?= dt('consult_neuro_datetime', 't') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-primary">Door to Consult Neurologist (นาที)</label>
                        <div class="input-group">
                             <span class="input-group-text bg-primary bg-opacity-10 text-primary"><i class="bi bi-calculator"></i></span>
                             <input type="text" class="form-control fw-bold text-primary bg-primary bg-opacity-10" id="calc_door_to_doctor" name="time_door_to_doctor_min" readonly placeholder="..." value="<?= val('time_door_to_doctor_min') ?>">
                        </div>
                    </div>
                </div>
                
                <hr class="text-muted opacity-25">

                <h6 class="fw-bold text-secondary mb-2">2.2 เวลาทำ Imaging (CT / CTA / MRI)</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4 border-end">
                        <label class="form-label fw-bold text-primary">CT non-contrast</label>
                        <div class="input-group input-group-sm mb-2">
                            <input type="date" class="form-control" id="ctncDate" name="ctncDate" value="<?= dt('ctnc_datetime', 'd') ?>">
                            <input type="text" class="form-control timepicker" placeholder="เวลา" id="ctncTime_input" name="ctncTime_input" value="<?= dt('ctnc_datetime', 't') ?>">
                        </div>
                        <div class="bg-primary bg-opacity-10 p-2 rounded">
                            <label class="form-label small fw-bold text-primary mb-1">Door to CT (นาที)</label>
                            <input type="text" class="form-control form-control-sm fw-bold text-primary" id="calc_door_to_ct" name="time_door_to_ct_min" readonly placeholder="..." value="<?= val('time_door_to_ct_min') ?>">
                        </div>
                    </div>

                    <div class="col-md-4 border-end">
                        <label class="form-label fw-bold text-primary">CTA</label>
                        <div class="input-group input-group-sm mb-2">
                            <input type="date" class="form-control" id="ctaTime" name="ctaTime" value="<?= dt('cta_datetime', 'd') ?>">
                            <input type="text" class="form-control timepicker" placeholder="เวลา" id="ctaTime_input" name="ctaTime_input" value="<?= dt('cta_datetime', 't') ?>">
                        </div>
                        <div class="bg-primary bg-opacity-10 p-2 rounded">
                            <label class="form-label small fw-bold text-primary mb-1">Door to CTA (นาที)</label>
                            <input type="text" class="form-control form-control-sm fw-bold text-primary" id="calc_door_to_cta" name="time_door_to_cta_min" readonly placeholder="..." value="<?= val('time_door_to_cta_min') ?>">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-primary">MRI</label>
                        <div class="input-group input-group-sm mb-2">
                            <input type="date" class="form-control" id="mriTime" name="mriTime" value="<?= dt('mri_datetime', 'd') ?>">
                            <input type="text" class="form-control timepicker" placeholder="เวลา" id="mriTime_input" name="mriTime_input" value="<?= dt('mri_datetime', 't') ?>">
                        </div>
                    </div>
                </div>

                <hr class="text-muted opacity-25">

                <h6 class="fw-bold text-secondary mb-2">2.3 Neuro-Interventionist</h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="consult_intervention_time" class="form-label">วันที่ปรึกษา</label>
                        <input type="date" id="consult_intervention_time" name="consult_intervention_time" class="form-control" value="<?= dt('consult_intervention_datetime', 'd') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="consult_intervention_time_input" class="form-label">เวลา</label>
                        <input type="text" class="form-control timepicker" placeholder="คลิกเพื่อเลือกใส่เวลา" id="consult_intervention_time_input" name="consult_intervention_time_input" value="<?= dt('consult_intervention_datetime', 't') ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-primary">Door to Consult Interventionist (นาที)</label>
                        <div class="input-group">
                             <span class="input-group-text bg-primary bg-opacity-10 text-primary"><i class="bi bi-calculator"></i></span>
                             <input type="text" class="form-control fw-bold text-primary bg-primary bg-opacity-10" id="calc_door_to_intervention" name="time_door_to_intervention_min" readonly placeholder="..." value="<?= val('time_door_to_intervention_min') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-form">
                <div class="section-title" style="margin-top:0;">
                    <i class="bi bi-file-earmark-medical"></i> 3. ผลการตรวจ CT/CTA (Imaging Results)
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="aspect" class="form-label fw-bold">ASPECT Score (0-10)</label>
                        <select class="form-select" id="aspect" name="aspect">
                            <option value="" disabled <?= sel('aspect_score', '') ?>>-- Select Score --</option>
                            <?php for($i=0; $i<=10; $i++) echo "<option value='$i' ".sel('aspect_score', $i).">$i</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="collateral" class="form-label fw-bold">Collateral Score (0-5)</label>
                        <select class="form-select" id="collateral" name="collateral">
                            <option value="" disabled <?= sel('collateral_score', '') ?>>-- Select Score --</option>
                            <?php for($i=0; $i<=5; $i++) echo "<option value='$i' ".sel('collateral_score', $i).">$i</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="occlusionLocation" class="form-label fw-bold">Occlusion Site</label>
                        <select class="form-select" id="occlusionLocation" name="occlusionLocation">
                            <option value="" <?= sel('occlusion_site', '') ?>>-- เลือกตำแหน่ง --</option>
                            <option value="Left ICA" <?= sel('occlusion_site', 'Left ICA') ?>>Cervical ICA left</option>
                            <option value="Right ICA" <?= sel('occlusion_site', 'Right ICA') ?>>Cervical ICA Right</option>
                            <option value="Intracranial left ICA" <?= sel('occlusion_site', 'Intracranial left ICA') ?>>Intracranial ICA left</option>
                            <option value="Intracranial Right ICA" <?= sel('occlusion_site', 'Intracranial Right ICA') ?>>Intracranial ICA Right</option>
                            <option value="Left M1 of MCA" <?= sel('occlusion_site', 'Left M1 of MCA') ?>>Left M1 of MCA</option>
                            <option value="Right M1 of MCA" <?= sel('occlusion_site', 'Right M1 of MCA') ?>>Right M1 of MCA</option>
                            <option value="Left M2 of MCA" <?= sel('occlusion_site', 'Left M2 of MCA') ?>>Left M2 of MCA</option>
                            <option value="Right M2 of MCA" <?= sel('occlusion_site', 'Right M2 of MCA') ?>>Right M2 of MCA</option>
                            <option value="Left Beyond M2 of MCA" <?= sel('occlusion_site', 'Left Beyond M2 of MCA') ?>>Left Beyond M2 of MCA</option>
                            <option value="Right Beyond M2 of MCA" <?= sel('occlusion_site', 'Right Beyond M2 of MCA') ?>>Right Beyond M2 of MCA</option>
                            <option value="Left ACA" <?= sel('occlusion_site', 'Left ACA') ?>>Left ACA</option>
                            <option value="Right ACA" <?= sel('occlusion_site', 'Right ACA') ?>>Right ACA</option>
                            <option value="Left PCA" <?= sel('occlusion_site', 'Left PCA') ?>>Left PCA</option>
                            <option value="Right PCA" <?= sel('occlusion_site', 'Right PCA') ?>>Right PCA</option>
                            <option value="left Vertebral artery" <?= sel('occlusion_site', 'left Vertebral artery') ?>>left Vertebral artery</option>
                            <option value="Right Vertebral artery" <?= sel('occlusion_site', 'Right Vertebral artery') ?>>Right Vertebral artery</option>
                            <option value="Basilar" <?= sel('occlusion_site', 'Basilar') ?>>Basilar</option>
                        </select>
                    </div>
                </div>
                
                <div class="p-4 bg-light rounded border">
                    <label class="form-label fw-bold mb-3 d-block"><i class="bi bi-diagram-3"></i> สรุปผล CT (Diagnosis Type):</label>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ctResult" id="ctResultIschemic" value="ischemic" <?= chk('ct_result', 'ischemic') ?>>
                            <label class="form-check-label fs-5 fw-bold text-primary" for="ctResultIschemic">
                                Ischemic Stroke
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ctResult" id="ctResultHemorrhagic" value="hemorrhagic" <?= chk('ct_result', 'hemorrhagic') ?>>
                            <label class="form-check-label fs-5 fw-bold text-danger" for="ctResultHemorrhagic">
                                Hemorrhagic Stroke
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-title mt-4">
                <i class="bi bi-signpost-split"></i> การรักษา (Treatment Pathway)
            </div>

            <div id="ischemicPathway" class="card-form <?= (isset($row['ct_result']) && $row['ct_result'] == 'ischemic') ? '' : 'd-none' ?>" style="border-top: 5px solid var(--primary-color);">
                <div class="section-title text-primary" style="margin-top:0;">
                    <i class="bi bi-activity"></i> A. แนวทาง Ischemic Stroke
                </div>
                
                <div class="mb-4 p-3 bg-white border rounded shadow-sm">
                    <label class="form-label fw-bold mb-2">1. ยาละลายลิ่มเลือด (Fibrinolytic)</label>
                    <div class="row g-3 align-items-center">
                        <div class="col-md-12">
                            <div class="btn-group w-100 mb-3" role="group">
                                <input type="radio" class="btn-check" name="fibrinolytic_type" id="fib_rtpa" value="rtpa" <?= chk('fibrinolytic_type', 'rtpa') ?>>
                                <label class="btn btn-outline-primary" for="fib_rtpa">rt-PA</label>

                                <input type="radio" class="btn-check" name="fibrinolytic_type" id="fib_sk" value="sk" <?= chk('fibrinolytic_type', 'sk') ?>>
                                <label class="btn btn-outline-primary" for="fib_sk">SK</label>

                                <input type="radio" class="btn-check" name="fibrinolytic_type" id="fib_tnk" value="tnk" <?= chk('fibrinolytic_type', 'tnk') ?>>
                                <label class="btn btn-outline-primary" for="fib_tnk">TNK</label>

                                <input type="radio" class="btn-check" name="fibrinolytic_type" id="fib_no" value="no" <?= chk('fibrinolytic_type', 'no') ?>>
                                <label class="btn btn-outline-secondary" for="fib_no">NO (ไม่ให้ยา)</label>
                            </div>
                        </div>
                        
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-light">เริ่มยา (Start)</span>
                                <input type="date" class="form-control" id="tpaDate" name="tpaDate" value="<?= dt('tpa_datetime', 'd') ?>">
                                <input type="text" class="form-control timepicker" placeholder="เวลา" id="tpaTime" name="tpaTime" value="<?= dt('tpa_datetime', 't') ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text bg-success bg-opacity-25 fw-bold text-success">Door to Needle</span>
                                <input type="text" class="form-control fw-bold text-success bg-success bg-opacity-10" id="calc_door_to_needle" name="time_door_to_needle_min" readonly placeholder="นาที" value="<?= val('time_door_to_needle_min') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-white border rounded shadow-sm">
                    <label class="form-label fw-bold mb-2">2. การเตรียมทำหัตถการ (Preparation)</label>
                    <div class="row g-3">
                        <div class="col-md-4 border-end">
                            <label for="anesthesiaTime" class="form-label text-muted small">Set ดมยา (Anesthesia Set)</label>
                            <div class="input-group input-group-sm">
                                <input type="date" class="form-control" id="anesthesiaTime" name="anesthesiaTime_date" value="<?= dt('anesthesia_set_datetime', 'd') ?>">
                                <input type="text" class="form-control timepicker" placeholder="คลิกเพื่อเลือกใส่เวลา" name="anesthesiaTime_time" value="<?= dt('anesthesia_set_datetime', 't') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="punctureTime" class="form-label text-muted small">Activate Team</label>
                            <div class="input-group input-group-sm">
                                <input type="date" class="form-control" id="punctureTime" name="punctureTime_date" value="<?= dt('activate_team_datetime', 'd') ?>">
                                <input type="text" class="form-control timepicker" placeholder="คลิกเพื่อเลือกใส่เวลา" name="punctureTime_time" value="<?= dt('activate_team_datetime', 't') ?>" placeholder="คลิกเพื่อเลือกใส่เงลา">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="hemorrhagicPathway" class="card-form <?= (isset($row['ct_result']) && $row['ct_result'] == 'hemorrhagic') ? '' : 'd-none' ?>" style="border-top: 5px solid var(--accent-color);">
                <div class="section-title text-danger" style="margin-top:0;">
                    <i class="bi bi-bandaid"></i> B. แนวทาง Hemorrhagic Stroke
                </div>
                <div class="p-4 bg-danger bg-opacity-10 rounded border border-danger">
                    <label class="form-label fw-bold text-danger fs-5 mb-2">
                        <i class="bi bi-telephone-forward me-2"></i> ปรึกษาศัลยแพทย์ระบบประสาท (Consult Neurosurgeon)
                    </label>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label text-muted small">วันที่ปรึกษา</label>
                            <input type="date" class="form-control" name="consultNS_date" value="<?= dt('consult_neurosurgeon_datetime', 'd') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">เวลา</label>
                            <input type="text" class="form-control timepicker" name="consultNS_time" value="<?= dt('consult_neurosurgeon_datetime', 't') ?>" placeholder="--:--">
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5 mb-5">
                <button type="button" class="btn btn-primary btn-lg px-5 py-3 shadow" id="saveErBtn" style="border-radius: 50px;">
                    <i class="bi bi-save-fill me-2"></i> บันทึกข้อมูล ER
                </button>
                <a href="OR_Procedure_Form.php?admission_id=<?= $admission_id ?>" id="nextStepBtn" class="btn btn-success btn-lg px-5 ms-2 py-3 shadow d-none" style="border-radius: 50px;">
                    ไปยังหน้า 3 (OR) <i class="bi bi-arrow-right-circle-fill ms-2"></i>
                </a>
            </div>
        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --------------------------------------------------------
            // 1. ฟังก์ชันคำนวณเวลา (Calculator Logic)
            // --------------------------------------------------------
            const hospitalArrivalVal = document.getElementById('hospital_arrival_db').value;
            
            const outDoorDoctor = document.getElementById('calc_door_to_doctor');
            const outDoorCT = document.getElementById('calc_door_to_ct');
            const outDoorCTA = document.getElementById('calc_door_to_cta');
            const outDoorIntervention = document.getElementById('calc_door_to_intervention');
            // [ใหม่] Output Door to Needle
            const outDoorNeedle = document.getElementById('calc_door_to_needle');

            // เตรียมค่า Arrival Date Part (YYYY-MM-DD) ไว้ใช้เติมอัตโนมัติ
            let defaultDate = '';
            if(hospitalArrivalVal) {
                defaultDate = hospitalArrivalVal.split(' ')[0]; // ดึงเฉพาะวันที่
            }

            function calculateMinutes(startStr, endStr) {
                if(!startStr || !endStr) return '';
                const start = new Date(startStr.replace(' ', 'T')); 
                const end = new Date(endStr);
                if (isNaN(start.getTime()) || isNaN(end.getTime())) return ''; 
                const diffMs = end - start; 
                return Math.floor(diffMs / 60000); 
            }

            window.updateErCalculations = function() {
                if(!hospitalArrivalVal) return;
                const arrivalDateTime = hospitalArrivalVal.replace(' ', 'T'); 

                // A. Door to Consult Neurologist
                const docDateInput = document.getElementById('consult_neuro_date');
                const docTimeInput = document.getElementById('consult_neuro_time_input');
                if(docTimeInput.value && !docDateInput.value && defaultDate) { docDateInput.value = defaultDate; }

                if(docDateInput.value && docTimeInput.value) {
                    const minDoc = calculateMinutes(arrivalDateTime, `${docDateInput.value}T${docTimeInput.value}`);
                    outDoorDoctor.value = (minDoc !== '' && !isNaN(minDoc)) ? minDoc : '';
                } else { outDoorDoctor.value = ''; }

                // B. Door to CT
                const ctDateInput = document.getElementById('ctncDate');
                const ctTimeInput = document.getElementById('ctncTime_input');
                if(ctTimeInput.value && !ctDateInput.value && defaultDate) { ctDateInput.value = defaultDate; }

                if(ctDateInput.value && ctTimeInput.value) {
                    const minCT = calculateMinutes(arrivalDateTime, `${ctDateInput.value}T${ctTimeInput.value}`);
                    outDoorCT.value = (minCT !== '' && !isNaN(minCT)) ? minCT : '';
                } else { outDoorCT.value = ''; }

                // C. Door to CTA
                const ctaDateInput = document.getElementById('ctaTime');
                const ctaTimeInput = document.getElementById('ctaTime_input');
                if(ctaTimeInput.value && !ctaDateInput.value && defaultDate) { ctaDateInput.value = defaultDate; }

                if(ctaDateInput.value && ctaTimeInput.value) {
                    const minCTA = calculateMinutes(arrivalDateTime, `${ctaDateInput.value}T${ctaTimeInput.value}`);
                    outDoorCTA.value = (minCTA !== '' && !isNaN(minCTA)) ? minCTA : '';
                } else { outDoorCTA.value = ''; }

                // D. Door to Interventionist
                const interDateInput = document.getElementById('consult_intervention_time');
                const interTimeInput = document.getElementById('consult_intervention_time_input');
                if(interTimeInput.value && !interDateInput.value && defaultDate) { interDateInput.value = defaultDate; }

                if(interDateInput.value && interTimeInput.value) {
                    const minInter = calculateMinutes(arrivalDateTime, `${interDateInput.value}T${interTimeInput.value}`);
                    outDoorIntervention.value = (minInter !== '' && !isNaN(minInter)) ? minInter : '';
                } else { outDoorIntervention.value = ''; }

                // E. [ใหม่] Door to Needle Time
                const tpaDateInput = document.getElementById('tpaDate');
                const tpaTimeInput = document.getElementById('tpaTime');
                // Auto Fill Date
                if(tpaTimeInput.value && !tpaDateInput.value && defaultDate) { tpaDateInput.value = defaultDate; }

                if(tpaDateInput.value && tpaTimeInput.value) {
                    const minNeedle = calculateMinutes(arrivalDateTime, `${tpaDateInput.value}T${tpaTimeInput.value}`);
                    outDoorNeedle.value = (minNeedle !== '' && !isNaN(minNeedle)) ? minNeedle : '';
                } else { outDoorNeedle.value = ''; }
            };

            // --------------------------------------------------------
            // 2. ตั้งค่า Flatpickr + สั่งคำนวณทันที
            // --------------------------------------------------------
            flatpickr(".timepicker", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                allowInput: true,
                onClose: function(selectedDates, dateStr, instance) {
                    updateErCalculations(); 
                }
            });

            // --------------------------------------------------------
            // 3. ตัวดักจับเหตุการณ์ (Event Listeners)
            // --------------------------------------------------------
            const inputsCalc = [
                document.getElementById('consult_neuro_date'),
                document.getElementById('ctncDate'),
                document.getElementById('ctaTime'),
                document.getElementById('consult_neuro_time_input'),
                document.getElementById('ctncTime_input'),
                document.getElementById('ctaTime_input'),
                
                document.getElementById('consult_intervention_time'),
                document.getElementById('consult_intervention_time_input'),
                
                // [ใหม่]
                document.getElementById('tpaDate'),
                document.getElementById('tpaTime')
            ];

            inputsCalc.forEach(el => {
                if(el) {
                    el.addEventListener('change', updateErCalculations);
                    el.addEventListener('input', updateErCalculations); 
                }
            });

            // --------------------------------------------------------
            // 4. Logic อื่นๆ
            // --------------------------------------------------------
            const radioIschemic = document.getElementById('ctResultIschemic');
            const radioHemorrhagic = document.getElementById('ctResultHemorrhagic');
            const ischemicPathway = document.getElementById('ischemicPathway');
            const hemorrhagicPathway = document.getElementById('hemorrhagicPathway');

            if(radioIschemic && radioHemorrhagic) {
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
            }

            const saveButton = document.getElementById('saveErBtn');
            const nextButton = document.getElementById('nextStepBtn');
            const erForm = document.getElementById('erForm');
            const admissionIdInput = document.querySelector('input[name="admission_id"]');
            
            if (admissionIdInput && admissionIdInput.value && nextButton) {
                nextButton.classList.remove('d-none');
            }

            if(saveButton) {
                saveButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'ยืนยันการบันทึก',
                        text: "ต้องการบันทึกข้อมูล ER ใช่หรือไม่?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'ใช่, บันทึกเลย'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({ title: 'กำลังบันทึก...', didOpen: () => Swal.showLoading() });
                            const formData = new FormData(erForm);
                            fetch('save_er.php', { method: 'POST', body: formData })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: 'บันทึกข้อมูลเรียบร้อย', timer: 1500, showConfirmButton: false });
                                    saveButton.classList.replace('btn-primary', 'btn-secondary');
                                    saveButton.innerHTML = '<i class="bi bi-check-lg"></i> บันทึกแล้ว';
                                    if(nextButton) {
                                        nextButton.classList.remove('d-none');
                                        nextButton.href = data.redirect_url;
                                    }
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

            // เรียกคำนวณครั้งแรก
            updateErCalculations();
        });
    </script>
</body>
</html>