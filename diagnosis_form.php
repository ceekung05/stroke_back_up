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
    <title>2. ER - ระบบ Stroke Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">


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
        <a href="diagnosis_form.php" class="active">
            <i class="bi bi-hospital"></i> 2. ER
        </a>
        <a href="OR_Procedure_Form.php">
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
                <i class="bi bi-hospital"></i>
            </div>
            <div class="title-container">
                <h1>2. ER</h1>
                <p class="subtitle mb-0">การวินิจฉัยและตัดสินใจที่ห้องฉุกเฉิน</p>
            </div>
        </div>

        <form>
            <div class="section-title">
                <i class="bi bi-activity"></i> 1. เวลาในการส่งต่อ
            </div>

            <div class="row g-3 mb-3">

                <div class="col-md-2">
                    <label for="arrivalTime" class="form-label fw-bold mt-2">วันที่ส่งต่อ</label>
                    <input type="date" class="form-control " id="arrivalTime" name="arrivalTime" >
                </div>
                <div class="col-md-2">
                    <label for="arrivalTime" class="form-label mt-2">เวลาที่รถออก</label>
                    <input type="time" class="form-control" id="arrivalTime_time" name="arrivalTime_time">
                </div>
                <div class="col-md-2">
                    <label for="arrivalTime_datetime" class="form-label mt-2">วันที่ถึง รพ. ปลายทาง</label>
                    <input type="date" class="form-control " id="arrivalTime_datetime" name="arrivalTime_datetime" >
                </div>
                <div class="col-md-2">
                    <label for="arrivalTime_time_destination" class="form-label mt-2">เวลาถึง รพ. ปลายทาง</label>
                    <input type="time" class="form-control" id="arrivalTime_time_destination" name="arrivalTime_time_destination">
                </div>
            </div>
            <hr>
            <div class="section-title">
                2.การส่งปรึกษาแพทย์เฉพาะทางของประสาทวิทยา
            </div>
            <div class="row">
                <div class="mb-3 col-md-2">
                    <label for="consult_neuro_date" class="form-label">วันที่ที่ส่งปรึกษา</label>
                    <input type="date" id="consult_neuro_date" class="form-control mt-2 " >
                </div>
                <div class="mb-3 col-md-2">
                    <label for="consult_neuro_time_input" class="form-label">เวลาที่ส่งปรึกษา</label>
                    <input type="time" id="consult_neuro_time_input" class="form-control mt-2">
                </div>
            </div>


            <div class="section-title">
                <i class="bi bi-intersect"></i> 3. การวินิจฉัย และ Imaging
            </div>
            <div class="row ">
                <div class="col-md-2">
                    <label for="ctncDate" class="form-label">CT non contact</label>
                    <input type="date" class="form-control " id="ctncDate">
                </div>
                <div class="col-md-2 mt-auto">
                    <input type="time" class="form-control " id="ctncTime_input">
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <label for="ctaTime" class="form-label">CTA</label>
                    <input type="date" class="form-control " id="ctaTime">
                </div>
                <div class="col-md-2 mt-auto">
                    <input type="time" class="form-control " id="ctaTime_input">
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <label for="mriTime" class="form-label">MRI</label>
                    <input type="date" class="form-control " id="mriTime">
                </div>
                <div class="col-md-2 mt-auto">
                    <input type="time" class="form-control " id="mriTime_input">
                </div>
            </div>
            <div class="section-title">
                <i class="bi bi-intersect"></i> 4. การปรึกษาแพทย์
            </div>
            <div class="row">
                <div class=" col-md-2">
                    <label for="consult_intervention_time" class="form-label">Neuro-Interventionist</label>
                    <input type="date" id="consult_intervention_time" class="form-control mt-2 " placeholder="วัน/เดือน/ปี">
                </div>
                <div class="col-md-2 mt-auto">
                    <input type="time" class="form-control " id="consult_intervention_time_input">
                </div>
            </div>


            <div class="section-title">
                5. ผล CT/CTA
            </div>
            <div class="row g-3">
                <div class="col-md-2">
                    <label for="aspect" class="form-label">ASPECT (0-10)</label>
                    <select class="form-select" id="aspect">
                        <option selected disabled>--select--</option>
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                        <option value="10">10</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="collateral" class="form-label">Collateral score (0-5)</label>
                    <select class="form-select " id="collateral">
                        <option selected disabled>--select--</option>
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="occlusionLocation" class="form-label">Occlusion site (Drop down)</label>
                    <select class="form-select" id="occlusionLocation">
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
            </div>
            <hr>
            <label class="form-label fw-bold">ผล CT (Ischemic / Hemorrhagic):</label>
            <div class="p-3 bg-light border rounded">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="ctResult" id="ctResultIschemic" value="ischemic">
                    <label class="form-check-label fs-5" for="ctResultIschemic">Ischemic</label>
                </div>
                <hr class="my-2">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="ctResult" id="ctResultHemorrhagic" value="hemorrhagic">
                    <label class="form-check-label fs-5" for="ctResultHemorrhagic">Hemorrhagic</label>
                </div>
            </div>

            <div class="section-title">
                <i class="bi bi-signpost-split"></i>การรักษา (Treatment Pathway)
            </div>

            <div id="ischemicPathway" class="d-none">
                <h4 class="text-primary">A. การรักษา Ischemic Stroke</h4>
                <div class="card card-body shadow-sm">
                    <label class="form-label fw-bold">1. Fibrinolytic</label>

                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap gap-2">
                                <input type="radio" class="btn-check" name="fibrinolytic_type" id="fib_rtpa" value="rtpa" autocomplete="off">
                                <label class="btn btn-outline-secondary" for="fib_rtpa">rt-PA</label>

                                <input type="radio" class="btn-check" name="fibrinolytic_type" id="fib_sk" value="sk" autocomplete="off">
                                <label class="btn btn-outline-secondary" for="fib_sk">SK</label>

                                <input type="radio" class="btn-check" name="fibrinolytic_type" id="fib_tnk" value="tnk" autocomplete="off">
                                <label class="btn btn-outline-secondary" for="fib_tnk">TNK</label>

                                <input type="radio" class="btn-check" name="fibrinolytic_type" id="fib_no" value="no" autocomplete="off">
                                <label class="btn btn-outline-secondary" for="fib_no">NO</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="tpaTime" class="form-label">เวลาที่เริ่มให้ยา</label>
                            <input type="time" class="form-control" id="tpaTime">
                        </div>
                    </div>
                    <hr>
                    <label class="form-label fw-bold">2.การเตรียมทำหัตถการ</label>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="anesthesiaTime" class="form-label">Set ดมยา</label>
                            <input type="date" class="form-control " id="anesthesiaTime" >
                            <input type="time" name="" id="" class="form-control mt-2">
                        </div>
                        <div class="col-md-3">
                            <label for="punctureTime" class="form-label">Activate Team</label>
                            <input type="date" class="form-control " id="punctureTime" >
                            <input type="time" name="" id="" class="form-control mt-2">
                        </div>
                    </div>
                    <hr>
                </div>
            </div>

            <div id="hemorrhagicPathway" class="d-none">
                <h4 class="text-danger">B. แนวทาง Hemorrhagic Stroke</h4>
                <div class="card card-body shadow-sm">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="consultNS">
                        <label class="form-check-label" for="consultNS">ปรึกษาศัลยแพทย์ระบบประสาท</label>
                    </div>
                </div>
            </div>
        </form>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
</body>

</html>