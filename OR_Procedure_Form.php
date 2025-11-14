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
    <title>3. OR Procedure - ระบบ Stroke Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"> 
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
        <a href="OR_Procedure_Form.php" class="active">
            <i class="bi bi-scissors"></i> 3. OR Procedure
        </a>
        <a href="ward.php">
            <i class="bi bi-building-check"></i> 4. Ward
        </a>
        <a href="follow.php">
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
                <h1>3. OR Procedure Form</h1>
                <p class="subtitle mb-0">ฟอร์มบันทึกการผ่าตัด/หัตถการ</p>
            </div>
        </div>

        <form>
            <div class="section-title">
                <i class="bi bi-check2-square"></i> ประเภทหัตถการ
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="procType" id="procTypeMT" value="mt">
                <label class="form-check-label fs-5" for="procTypeMT">
                    1. Mechanical Thrombectomy (สำหรับ Ischemic Stroke)
                </label>
            </div>
            <hr class="my-2">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="procType" id="procTypeHemo" value="hemo">
                <label class="form-check-label fs-5" for="procTypeHemo">
                    2. Neurosurgery (สำหรับ Hemorrhagic Stroke)
                </label>
            </div>

            <div id="mtProcedure" class="d-none">
                <div class="section-title text-primary">
                    <i class="bi bi-activity"></i> A. Mechanical Thrombectomy (MT)
                </div>
                <div class="row g-3">
                    <div class="row">
                        <div class="col-md-2 ">
                            <label for="anesthesia_time_mt" class="form-label fw-bold">Anesthesia Time</label>
                            <input type="date" class="form-control " id="anesthesia_time_mt" placeholder="วัน/เดือน/ปี">
                        </div>
                        <div class="col-md-2 mt-auto">
                            <input type="time" class="form-control" name="" id="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <label for="puncture_time_mt" class="form-label fw-bold">Puncture Time</label>
                            <input type="date" class="form-control " id="puncture_time_mt" placeholder="วัน/เดือน/ปี">
                        </div>
                        <div class="col-md-2 mt-auto">
                            <input type="time" class="form-control" name="" id="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 ">
                            <label for="recanalization_time" class="form-label fw-bold">Recalnaligation Time</label>
                            <input type="date" class="form-control " id="recanalization_time" placeholder="วัน/เดือน/ปี">
                        </div>
                        <div class="col-md-2 mt-auto">
                            <input type="time" class="form-control" name="" id="">
                        </div>
                    </div>
                </div>
                <hr>
                <h5>Result</h5>
                <div class="row g-3">
                    <div class="col-md-2 mb-3">
                        <label for="occlusionvessel" class="form-label">occlusionvessel</label>
                        <select class="form-select" id="occlusionvessel">
                            <option selected>-- เลือกตำแหน่ง --</option>

                            <option value="Left ICA">Cervical ICA left</option>
                            <option value="Right ICA">Cervical ICA Right</option>
                            <option value="Intracranial left ICA">Intracranial ICA left</option>
                            <option value="Intracranial Right ICA">Intracranial ICA Right</option>
                            <option value="Left M1 of MCA">Left M1 of MCA</option>
                            <option value="Right M1 of MCA">Right M1 of MCA</option>
                            <option value="Left M2 of MCA">Left M2 of MCA</option>
                            <option value="Right M2 of MCA">Right M2 of MCA</option>
                            <option value="Right Beyond M2 of MCA">Left Beyond M2 of MCA</option>
                            <option value="Right Beyond M2 of MCA">Right Beyond M2 of MCA</option>
                            <option value="Left ACA">Left ACA</option>
                            <option value="Right ACA">Right ACA</option>
                            <option value="Left PCA">Left PCA</option>
                            <option value="Right PCA">Right PCA</option>
                            <option value="left Vertebral artery">left Vertebral artery </option>
                            <option value="Right Vertebral artery">Right Vertebral artery</option>
                            <option value="Basilar">Basilar </option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="ticiScore" class="form-label mt-auto">TICI Score (ผลลัพธ์การเปิดเส้นเลือด)</label>
                        <select class="form-select" id="ticiScore">
                            <option selected>-- เลือกผลลัพธ์ --</option>
                            <option value="0">0 - No perfusion</option>
                            <option value="1">1 - Minimal perfusion</option>
                            <option value="2a">2a - Partial ( < 50%)</option>
                            <option value="2b">2b - Partial ( > 50%)</option>
                            <option value="3">3 - Complete perfusion</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="procedureTechnique" class="form-label fw-bold">2. Procedure Technique</label>
                        <select class="form-select" id="procedureTechnique">
                            <option value="" selected>-- เลือกวิธีการ --</option>
                            <option value="">aspiration alone</option>
                            <option value="">stent alone</option>
                            <option value="">Solumbra</option>
                            <option value="">combined</option>
                            <option value="">primary stent</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="pass_count" class="form-label fw-bold">เปิดกี่ครั้ง</label>
                        <select class="form-select" id="pass_count">
                            <option selected>-- เลือกจำนวน --</option>
                            <option value="1">1 ครั้ง</option>
                            <option value="2">2 ครั้ง</option>
                            <option value="3">3 ครั้ง</option>
                            <option value="4">4 ครั้ง</option>
                            <option value="5">5 ครั้ง</option>
                            <option value="6">6 ครั้ง</option>
                            <option value="7">7 ครั้ง</option>
                            <option value="8">8 ครั้ง</option>
                            <option value="9">9 ครั้ง</option>
                            <option value="10">10 ครั้ง</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <h5>Medication</h5>
                    <fieldset>
                        <legend class="h6">Peri-Procedure</legend>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="1" id="med_integrilin" name="med_integrilin">
                            <label class="form-check-label fw-bold" for="med_integrilin">
                                Integrilin
                            </label>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">bolus</span>
                                    <input type="number" class="form-control" name="integrilin_bolus" placeholder="0">
                                    <span class="input-group-text">ml</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">drip rate</span>
                                    <input type="number" class="form-control" name="integrilin_drip" placeholder="0">
                                    <span class="input-group-text">ml/hr</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="1" id="med_nimodipine" name="med_nimodipine">
                            <label class="form-check-label fw-bold" for="med_nimodipine">
                                Nimodipine
                            </label>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">bolus</span>
                                    <input type="number" class="form-control" name="nimodipine_bolus" placeholder="0">
                                    <span class="input-group-text">ml</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">drip rate</span>
                                    <input type="number" class="form-control" name="nimodipine_drip" placeholder="0">
                                    <span class="input-group-text">ml/hr</span>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="mb-3">
                    <h5>Procedure time</h5>
                    <div class="row g-3">
                        <div class="col-md-3 mb-3">
                            <label for="xrayDose" class="form-label">Dose X-ray (mGy)</label>
                            <input type="text" class="form-control" id="xrayDose">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="flu-time" class="form-label">Flu time (min)</label>
                            <input type="text" class="form-control" id="flu-time">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="" class="form-lable">coneBeam CT:Detection of intracranial hemorchange or subarachnoid contrast staining</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="cone_beam_ct" id="coneBeam_yes" value="yes">
                                    <label class="form-check-label" for="coneBeam_yes"> Yes </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="cone_beam_ct" id="coneBeam_no" value="no">
                                    <label class="form-check-label" for="coneBeam_no"> No </label>
                                </div>
                            </div>
                            <div id="coneBeam_yes_details" class="d-none mt-2 col-md-4">
                                <input type="text" class="form-control" name="cone_beam_ct_details" placeholder="ระบุข้อความ">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="hemoProcedure" class="d-none">
                <div class="section-title text-danger">
                    <i class="bi bi-bandaid"></i> B. Neurosurgery (Hemorrhagic)
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="hemoLocation" class="form-label">Location (ตำแหน่งเลือดออก)</label>
                        <input type="text" class="form-control" id="hemoLocation">
                    </div>
                    <div class="col-md-4">
                        <label for="hemoCC" class="form-label">Hemorrhage (CC) (ปริมาตรเลือด)</label>
                        <input type="number" class="form-control" id="hemoCC">
                    </div>

                </div>
                <label class="form-label fw-bold">หัตถการที่ทำ</label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="procCranio">
                        <label class="form-check-label" for="procCranio">Craniotomy</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="procCraniectomy">
                        <label class="form-check-label" for="procCraniectomy">craniectomy</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="procVentriculostomy">
                        <label class="form-check-label" for="procVentriculostomy">ventriculostomy</label>
                    </div>
                </div>
            </div>

            <div class="section-title">
                <i class="bi bi-exclamation-triangle"></i> ภาวะแทรกซ้อน (Complications)
            </div>
            <div class="mb-3 col-md-3">
                <label for="complicationLog" class="form-label ">บันทึกภาวะแทรกซ้อน</label>
                <select class="form-select" id="complicationLog">
                    <option selected>...เลือกภาวะแทรกซ้อน...</option>
                    <option value="">มีภาวะเลือดออกในสมอง</option>
                    <option value="">การบาดเจ็บต่อหลอดเลือด เช่น ทะลุ ฉีดขาด</option>
                    <option value="">มีการอุดตันของหลอดเลือดช้ำ</option>
                    <option value="">หลอดเลือดมีการหดเกร็ง</option>
                    <option value="">ไม่มีภาวะแทรกซ้อน</option>
                </select>
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
        });
    </script>
</body>

</html>