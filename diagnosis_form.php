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

if ($admission_id) {
    // ดึงข้อมูลจาก tbl_er
    $sql = "SELECT * FROM tbl_er WHERE admission_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
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
    <title>2. ER - ระบบ Stroke Care</title>
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
            
            <div class="card-form">
                <div class="section-title" style="margin-top:0;">
                    <i class="bi bi-ambulance"></i> 1. เวลาในการส่งต่อ (Transfer Timeline)
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="arrivalTime" class="form-label">วันที่ส่งต่อ (Departure Date)</label>
                        <input type="date" class="form-control" id="arrivalTime" name="arrivalTime" value="<?= dt('transfer_departure_datetime', 'd') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="arrivalTime_time" class="form-label">เวลาที่รถออก (Time)</label>
                        <input type="time" class="form-control" id="arrivalTime_time" name="arrivalTime_time" value="<?= dt('transfer_departure_datetime', 't') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="arrivalTime_datetime" class="form-label">วันที่ถึง รพ. ปลายทาง (Arrival Date)</label>
                        <input type="date" class="form-control" id="arrivalTime_datetime" name="arrivalTime_datetime" value="<?= dt('transfer_arrival_datetime', 'd') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="arrivalTime_time_destination" class="form-label">เวลาถึง รพ. (Time)</label>
                        <input type="time" class="form-control" id="arrivalTime_time_destination" name="arrivalTime_time_destination" value="<?= dt('transfer_arrival_datetime', 't') ?>">
                    </div>
                </div>
            </div>

            <div class="card-form">
                <div class="section-title" style="margin-top:0;">
                    <i class="bi bi-clock-history"></i> 2. ลำดับเวลาการตรวจรักษา (Clinical Timeline)
                </div>
                
                <h6 class="fw-bold text-secondary mb-2">2.1 การปรึกษาแพทย์ประสาทวิทยา (Neurologist)</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label for="consult_neuro_date" class="form-label">วันที่ส่งปรึกษา</label>
                        <input type="date" id="consult_neuro_date" name="consult_neuro_date" class="form-control" value="<?= dt('consult_neuro_datetime', 'd') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="consult_neuro_time_input" class="form-label">เวลา</label>
                        <input type="time" id="consult_neuro_time_input" name="consult_neuro_time_input" class="form-control" value="<?= dt('consult_neuro_datetime', 't') ?>">
                    </div>
                </div>
                
                <hr class="text-muted opacity-25">

                <h6 class="fw-bold text-secondary mb-2">2.2 เวลาทำ Imaging (CT / CTA / MRI)</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4 border-end">
                        <label class="form-label fw-bold text-primary">CT non-contrast</label>
                        <div class="input-group input-group-sm mb-2">
                            <input type="date" class="form-control" id="ctncDate" name="ctncDate" value="<?= dt('ctnc_datetime', 'd') ?>">
                            <input type="time" class="form-control" id="ctncTime_input" name="ctncTime_input" value="<?= dt('ctnc_datetime', 't') ?>">
                        </div>
                    </div>
                    <div class="col-md-4 border-end">
                        <label class="form-label fw-bold text-primary">CTA</label>
                        <div class="input-group input-group-sm mb-2">
                            <input type="date" class="form-control" id="ctaTime" name="ctaTime" value="<?= dt('cta_datetime', 'd') ?>">
                            <input type="time" class="form-control" id="ctaTime_input" name="ctaTime_input" value="<?= dt('cta_datetime', 't') ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-primary">MRI</label>
                        <div class="input-group input-group-sm mb-2">
                            <input type="date" class="form-control" id="mriTime" name="mriTime" value="<?= dt('mri_datetime', 'd') ?>">
                            <input type="time" class="form-control" id="mriTime_input" name="mriTime_input" value="<?= dt('mri_datetime', 't') ?>">
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
                        <input type="time" class="form-control" id="consult_intervention_time_input" name="consult_intervention_time_input" value="<?= dt('consult_intervention_datetime', 't') ?>">
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
                        <div class="col-md-8">
                            <div class="btn-group w-100" role="group">
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
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light">เวลาเริ่มยา</span>
                                <input type="time" class="form-control" id="tpaTime" name="tpaTime" value="<?= val('tpa_start_time') ?>">
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
                                <input type="time" class="form-control" name="anesthesiaTime_time" value="<?= dt('anesthesia_set_datetime', 't') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="punctureTime" class="form-label text-muted small">Activate Team</label>
                            <div class="input-group input-group-sm">
                                <input type="date" class="form-control" id="punctureTime" name="punctureTime_date" value="<?= dt('activate_team_datetime', 'd') ?>">
                                <input type="time" class="form-control" name="punctureTime_time" value="<?= dt('activate_team_datetime', 't') ?>">
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
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="consultNS" name="consultNS" value="1" <?= chk('consult_neurosurgeon') ?>>
                        <label class="form-check-label fw-bold text-danger fs-5" for="consultNS">
                            <i class="bi bi-telephone-forward me-2"></i> ปรึกษาศัลยแพทย์ระบบประสาท (Consult Neurosurgeon)
                        </label>
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
            // 1. Logic Show/Hide Ischemic/Hemorrhagic
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

            // 2. Logic บันทึกข้อมูล (AJAX)
            const saveButton = document.getElementById('saveErBtn');
            const nextButton = document.getElementById('nextStepBtn');
            const erForm = document.getElementById('erForm');

            // เช็คว่ามี admission_id หรือยัง เพื่อโชว์ปุ่ม Next
            const admissionId = document.querySelector('input[name="admission_id"]').value;
            if (admissionId) {
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

                            fetch('save_er.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'สำเร็จ!',
                                        text: 'บันทึกข้อมูล ER เรียบร้อยแล้ว',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });

                                    saveButton.classList.replace('btn-primary', 'btn-secondary');
                                    saveButton.innerHTML = '<i class="bi bi-check-lg"></i> บันทึกแล้ว';
                                    
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