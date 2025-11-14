<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user_data'];
// (ตรรกะสำหรับดึงข้อมูลผู้ป่วยด้วย $_GET['hn'] ควรอยู่ตรงนี้)
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>1. ข้อมูลทั่วไป - ระบบ Stroke Care</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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

        <a href="form.php" class="active">
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
                <i class="bi bi-person-lines-fill"></i>
            </div>
            <div class="title-container">
                <h1>1. ฟอร์มบันทึกข้อมูลแรกรับ</h1>
                <p class="subtitle mb-0">Admission Form</p>
            </div>
        </div>

        <form action="save_admission_data.php" method="POST" id="mainAdmissionForm">

            <div class="section-title">
                <i class="bi bi-search"></i> 1. ค้นหาผู้ป่วย
            </div>
            <div class="row g-3">
                <div class="col-md-5">
                    <label for="hn_input" class="form-label"><strong>กรอกเลข HN</strong></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="hn_input" name="hn" placeholder="กรอกเลข HN...">
                        <button class="btn btn-primary" type="button" id="fetch_patient_btn">
                            <i class="bi bi-search"></i> ค้นหาข้อมูล
                        </button>
                    </div>
                </div>
            </div>

            <div id="patient_info_section">
                <div class="section-title">
                    <i class="bi bi-person-badge"></i> 2. ข้อมูลผู้ป่วย
                </div>
                <div class="row g-3">
                    <div class="col-md-4 mb-3">
                        <label class="form-lable ">เลขบัตรประจำตัวประชาชน</label>
                        <input type="text" name="" id="" class="form-control mt-2">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">ชื่อ-สกุล</label>
                        <input type="text" class="form-control" id="display_name" value="" placeholder="...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">อายุ</label>
                        <input type="text" class="form-control" id="display_age" value="" placeholder="...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">เพศ</label>
                        <input type="text" class="form-control" id="display_sex" value="" placeholder="...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="card_type" class="form-label mb-2">ประเภทบัตรอื่นๆ</label>
                        <select name="card_type" id="card_type" class="form-control">
                            <option value="" selected>-- ไม่ระบุ --</option>
                            <option value="Alien">ต่างด่าว</option>
                            <option value="Passport">Passport</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3 details-box" id="alien-details" style="display: none;">
                        <label for="alien_number" class="form-label mb-2">บัตรเลขที่ (ต่างด้าว)</label>
                        <input type="text" id="alien_number" name="alien_number" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3 details-box" id="passport-details" style="display: none;">
                        <label for="passport_number" class="form-label mb-2">บัตรเลขที่ (Passport)</label>
                        <input type="text" id="passport_number" name="passport_number" class="form-control">
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="" class="form-label mb-2">สิทธิการรักษา</label>
                        <div class="d-flex align-items-center gap-2">
                            <div class="btn-group" role="group" aria-label="Radio toggle button group">

                                <input type="radio" class="btn-check" name="btnTreatmentRight" id="btnradio1" autocomplete="off" value="health_insurance" checked>
                                <label class="btn btn-outline-secondary" for="btnradio1">ประกันสุขภาพ</label>

                                <input type="radio" class="btn-check" name="btnTreatmentRight" id="btnradio2" autocomplete="off" value="social_security">
                                <label class="btn btn-outline-secondary" for="btnradio2">ประกันสังคม</label>

                                <input type="radio" class="btn-check" name="btnTreatmentRight" id="btnradio3" autocomplete="off" value="affiliation">
                                <label class="btn btn-outline-secondary" for="btnradio3">ต้นสังกัด</label>

                                <input type="radio" class="btn-check" name="btnTreatmentRight" id="btnradio4" autocomplete="off" value="self_pay">
                                <label class="btn btn-outline-secondary" for="btnradio4">จ่ายเงินเอง</label>

                                <input type="radio" class="btn-check" name="btnTreatmentRight" id="btnradio5" autocomplete="off" value="t99">
                                <label class="btn btn-outline-secondary" for="btnradio5">ท.99</label>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-title">
                <i class="bi bi-clipboard-plus"></i> 3. โรคประจำตัว
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="comorbid_ht" value="1" id="comorbid_ht"><label class="form-check-label" for="comorbid_ht">HT</label></div>
                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="comorbid_dm" value="1" id="comorbid_dm"><label class="form-check-label" for="comorbid_dm">DM</label></div>
                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="old_cva" value="1" id="old_cva"><label class="form-check-label" for="old_cva">OLD CVA</label></div>
                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="comorbid_mi" value="1" id="comorbid_mi"><label class="form-check-label" for="comorbid_mi">MI</label></div>

                </div>
                <div class="col-md-4">
                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="comorbid_af" value="1" id="comorbid_af"><label class="form-check-label" for="comorbid_af">AF</label></div>
                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="comorbid_dlp" value="1" id="comorbid_dlp"><label class="form-check-label" for="comorbid_dlp">DLP</label></div>
                </div>
                <div class="col-md-4">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="comorbid_other_check" value="1" id="comorbid_other_check">
                        <label class="form-check-label" for="comorbid_other_check">OTHER</label>
                    </div>
                    <input type="text" class="form-control" name="comorbid_other_text" id="comorbid_other_text" placeholder="ระบุโรคประจำตัวอื่นๆ..." style="display: none;">
                </div>

            </div>

            <div class="section-title">
                <i class="bi bi-person-x"></i> 4. สารเสพติด (Addictive Substance)
            </div>
            <div class="row g-3">
                <div class="col-md-4">

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="comorbid_alcohol" value="1" id="comorbid_alcohol"><label class="form-check-label" for="comorbid_alcohol">ALCOHOL</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="comorbid_smoking" value="1" id="comorbid_smoking">
                        <label class="form-check-label" for="comorbid_smoking">SMOKING</label>
                    </div>
                </div>
            </div>

            <div class="section-title">
                <i class="bi bi-door-open"></i> 5. ประเภทการมาของคนไข้ (Arrival Type)
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input arrival-trigger" type="radio" name="arrival_type" id="arrival_refer" value="refer">
                    <label class="form-check-label" for="arrival_refer"> Refer </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input arrival-trigger" type="radio" name="arrival_type" id="arrival_ems" value="ems">
                    <label class="form-check-label" for="arrival_ems"> EMS (1669) </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input arrival-trigger" type="radio" name="arrival_type" id="arrival_walk_in" value="walk_in" checked>
                    <label class="form-check-label" for="arrival_walk_in"> Walk in </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input arrival-trigger" type="radio" name="arrival_type" id="arrival_ipd" value="ipd">
                    <label class="form-check-label" for="arrival_ipd"> IPD (ย้ายจากตึกใน) </label>
                </div>
            </div>

            <div class="col-md-5 mt-3 arrival-field" id="refer_from_field" style="display: none;">
                <div class="col-md-6">
                    <label for="refer_from_text" class="form-label">Refer จาก (ระบุโรงพยาบาล):</label>
                    <input type="text" class="form-control " name="refer_from_text" id="refer_from_text" placeholder="ระบุชื่อโรงพยาบาล...">
                </div>
                <div class="row mt-2">
                    <div class="col-md-5">
                        <label for="refer_arrival_time" class="form-label ">วันที่ที่ผป.มาถึง รพ/รพท ต้นทาง</label>
                        <input type="date" class="form-control thai-date" name="refer_arrival_date"> </div>
                    <div class="col-md-5 mt-auto">
                        <input type="time" class="form-control" name="refer_arrival_time">
                    </div>
                </div>
            </div>
            <div class="col-md-5 mt-3 arrival-field" id="ems_time_field" style="display: none;">
                <div class="row">
                    <div class="col-md-6">
                        <label for="first_medical_contact" class="form-label mt-2">First Medical contact (เวลาที่รับโทรศัพท์)</label>
                        <input type="date" class="form-control thai-date" id="first_medical_contact" name="first_medical_contact_date"> </div>
                    <div class="col-md-6 mt-auto">
                        <input type="time" class="form-control" name="first_medical_contact_time"> </div>
                </div>
            </div>
            <div class="col-md-5 mt-3 arrival-field" id="walkin_time_field" style="display: none;">
                <div class="row">
                    <div class="col-md-6">
                        <label for="er_arrival_time" class="form-label mt-2">เวลาที่มาถึงห้องฉุกเฉิน</label>
                        <input type="date" class="form-control thai-date" id="er_arrival_date" name="er_arrival_date"> </div>
                    <div class="col-md-6 mt-auto">
                        <input type="time" class="form-control" name="er_arrival_time">
                    </div>
                </div>

            </div>

            <div class="col-md-5 mt-3 arrival-field" id="ipd_time_field" style="display: none;">
                <div class="col-md-6">
                    <label for="ipd_ward" class="form-label mt-2 ">เวลาที่เริ่มมีอาการในหอผู้ป่วย</label>
                    <select name="ipd_ward" id="ipd_ward" class="form-select mb-2">
                        <option value="" selected disabled class="text-center">--ระบุหอผู้ป่วย--</option>
                        <option value="กุมารเวชกรรม 1">กุมารเวชกรรม 1</option>
                        <option value="กุมารเวชกรรม 2">กุมารเวชกรรม 2</option>
                        <option value="โรคติดเชื้อในเด็ก">โรคติดเชื้อในเด็ก</option>
                        <option value="กึ่งวิกฤตทารกแรกเกิด">กึ่งวิกฤตทารกแรกเกิด</option>
                        <option value="อายุรกรรมหญิง 1">อายุรกรรมหญิง 1</option>
                        <option value="อายุรกรรมหญิง 2">อายุรกรรมหญิง 2</option>
                        <option value="อายุรกรรมชาย 1">อายุรกรรมชาย 1</option>
                        <option value="อายุรกรรมชาย 2">อายุรกรรมชาย 2</option>
                        <option value="อายุรกรรมชาย 3">อายุรกรรมชาย 3</option>
                        <option value="หน่วยโลหิตวิทยา">หน่วยโลหิตวิทยา</option>
                        <option value="ศูนย์ปลูกถ่ายไขกระดูก">ศูนย์ปลูกถ่ายไขกระดูก</option>
                        <option value="ศัลยโรคหลอดเลือดสมอง">ศัลยโรคหลอดเลือดสมอง</option>
                        <option value="อายุรกรรมหญิง 3">อายุรกรรมหญิง 3</option>
                        <option value="อายุรกรรมหญิง 5">อายุรกรรมหญิง 5</option>
                        <option value="หอผู้ป่วยจักษุ">หอผู้ป่วยจักษุ</option>
                        <option value="อายุรกรรมชาย 3">อายุรกรรมชาย 3</option>
                        <option value="พิเศษประกันสังคม ชั้น 5">พิเศษประกันสังคม ชั้น 5</option>
                        <option value="จักษุโสตศอนาสิก (รวมสระอาพาธ)">จักษุโสตศอนาสิก (รวมสระอาพาธ)</option>
                        <option value="ผู้ป่วยนอก">ผู้ป่วยนอก</option>
                        <option value="อายุรกรรมชาย 4">อายุรกรรมชาย 4</option>
                        <option value="พิเศษชั้น 10">พิเศษชั้น 10</option>
                        <option value="พิเศษชั้น 10 รวม">พิเศษชั้น 10 รวม</option>
                        <option value="พิเศษชั้น 10 เดี่ยว">พิเศษชั้น 10 เดี่ยว</option>
                        <option value="ศัลยกรรมทรวงอกและอุบัติเหตุ">ศัลยกรรมทรวงอกและอุบัติเหตุ</option>
                        <option value="ศัลยกรรมทรวงอกและชาย">ศัลยกรรมทรวงอกและชาย</option>
                        <option value="ศัลยกรรมชาย 1">ศัลยกรรมชาย 1</option>
                        <option value="ศัลยกรรมชาย 2">ศัลยกรรมชาย 2</option>
                        <option value="ศัลยกรรมหญิง 1">ศัลยกรรมหญิง 1</option>
                        <option value="ศัลยกรรมการบาดเจ็บ (1)">ศัลยกรรมการบาดเจ็บ (1)</option>
                        <option value="ศัลยกรรมการบาดเจ็บ (2)">ศัลยกรรมการบาดเจ็บ (2)</option>
                        <option value="BURN UNIT">BURN UNIT</option>
                        <option value="ศัลยกรรมประสาท">ศัลยกรรมประสาท</option>
                        <option value="พิเศษชั้น 6">พิเศษชั้น 6</option>
                        <option value="พิเศษชั้น 6-1">พิเศษชั้น 6-1</option>
                        <option value="พิเศษชั้น 6-2">พิเศษชั้น 6-2</option>
                        <option value="พิเศษชั้น 9">พิเศษชั้น 9</option>
                        <option value="พิเศษชั้น 9 รวม">พิเศษชั้น 9 รวม</option>
                        <option value="พิเศษชั้น 9 เดี่ยว">พิเศษชั้น 9 เดี่ยว</option>
                        <option value="พิเศษชั้น 8">พิเศษชั้น 8</option>
                        <option value="พิเศษชั้น 8 รวม">พิเศษชั้น 8 รวม</option>
                        <option value="พิเศษชั้น 8 เดี่ยว">พิเศษชั้น 8 เดี่ยว</option>
                        <option value="AIIR">AIIR</option>
                        <option value="CCU">CCU</option>
                        <option value="SICU">SICU</option>
                        <option value="MICU-1">MICU-1</option>
                        <option value="MICU-2">MICU-2</option>
                        <option value="MICU-3">MICU-3</option>
                        <option value="NSICU">NSICU</option>
                        <option value="NICU (Neonatal ICU)">NICU (Neonatal ICU)</option>
                        <option value="PICU">PICU</option>
                        <option value="หาดใหญ่-นามหม่อม">หาดใหญ่-นามหม่อม</option>
                        <option value="หาดใหญ่-นามหม่อม 3">หาดใหญ่-นามหม่อม 3</option>
                        <option value="หาดใหญ่-นามหม่อม 4">หาดใหญ่-นามหม่อม 4</option>
                        <option value="หาดใหญ่-นามหม่อม 5">หาดใหญ่-นามหม่อม 5</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <input type="date" class="form-control thai-date" id="ipd_onset_date" name="ipd_onset_date"> </div>
                    <div class="col-md-6 mt-auto">
                        <input type="time" class="form-control" name="ipd_onset_time"> </div>
                </div>

            </div>


            <div class="section-title">
                <i class="bi bi-capsule"></i> 6. ยาประจำตัว (Regular medication)
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="med_anti_platelet" value="1" id="med_anti_platelet">
                        <label class="form-check-label" for="med_anti_platelet">Anti-platelet (ยาต้านเกล็ดเลือด)</label>
                        <div class="card-body mt-2">
                            <fieldset>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="med_asa" name="med_asa">
                                    <label class="form-check-label" for="med_asa">
                                        <strong>ASA (Aspirin)</strong>
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="med_clopidogrel" name="med_clopidogrel">
                                    <label class="form-check-label" for="med_clopidogrel">
                                        <strong>Clopidogrel (Copidogel / Plavix / Suluntra)</strong>
                                    </label>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="med_anti_coagulant" value="1" id="med_anti_coagulant">
                        <label class="form-check-label" for="med_anti_coagulant">Anti-coagulant (ยาต้านการแข็งตัวของเลือด)</label>
                        <div class="card-body mt-2">
                            <fieldset>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="med_warfarin" name="med_warfarin">
                                    <label class="form-check-label" for="med_warfarin">
                                        <strong>Warfarin</strong>
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="med_noac" name="med_noac">
                                    <label class="form-check-label" for="med_noac">
                                        <strong>NOAC</strong>
                                    </label>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-title">
                <i class="bi bi-star-half"></i> 7. MRS Score (ก่อนเกิดอาการ)
            </div>
            <div class="row g-3">
                <div class="col-md-2 ">
                    <label for="mrs_score" class="form-label ">เลือกคะแนน MRS (0-6) <span class="required-mark">*</span></label>
                    <select class="form-select" id="mrs_score" name="mrs_score" required>
                        <option value="" selected disabled class="text-center">---- กรุณาเลือก ----</option>
                        <option value="0">0 - No symptoms</option>
                        <option value="1">1 - No significant disability</option>
                        <option value="2">2 - Slight disability</option>
                        <option value="3">3 - Moderate disability</option>
                        <option value="4">4 - Moderately severe disability</option>
                        <option value="5">5 - Severe disability</loption>
                        <option value="6">6 - Dead</option>
                    </select>
                </div>
            </div>

            <div class="section-title">
                <i class="bi bi-card-checklist"></i> 8. ส่วนประเมินแรกรับ (Triage Assessment)
            </div>
            <div class="card-body">
                <p class="fs-5">ผู้ป่วยเข้าเกณฑ์ Stroke Fast Track หรือไม่?</p>
                <div class="d-flex align-items-center gap-2">
                    <input type="radio" class="btn-check" name="fast_track_status" id="fast_track_yes" value="yes" autocomplete="off" required>
                    <label class="btn btn-outline-secondary btn-toggle-custom" for="fast_track_yes"> Yes </label>
                    <input type="radio" class="btn-check" name="fast_track_status" id="fast_track_no" value="no" autocomplete="off" required>
                    <label class="btn btn-outline-secondary btn-toggle-custom" for="fast_track_no"> No </label>
                </div>
            </div>
            <h5 class="mt-4">อาการสำคัญ (Symptoms - F.A.S.T.)</h5>
            <div class="mb-3">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="sympDroop" value="face"><label class="form-check-label" for="sympDroop">F(Face) ใบหน้าเบี้ยว และปากเบี้ยว</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="sympWeakness" value="arm"><label class="form-check-label" for="sympWeakness">A(Arm) แขน-ขา อ่อนแรง ชาครึ่งซีก</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="sympSpeech" value="speech"><label class="form-check-label" for="sympSpeech">S(Speech) พูดไม่ชัด พูดติดขัด</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="symp_v" name="symp_v" value="1">
                    <label class="form-check-label" for="symp_v">V(Vision)</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="symp_a2" name="symp_a2" value="1">
                    <label class="form-check-label" for="symp_a2">A(Aphasia)</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="symp_n" name="symp_n" value="1">
                    <label class="form-check-label" for="symp_n">N(Neglect)</label>
                </div>
            </div>
            <h5 class="mt-4">การประเมินแรกรับ (Scores)</h5>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="gcs" class="form-label">GCS</label>
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <span>E</span>
                        <input type="radio" class="btn-check" name="gcs_e" id="gcs_e1" value="1" autocomplete="off"><label class="btn btn-outline-secondary btn-gcs d-flex align-items-center justify-content-center" for="gcs_e1">1</label>
                        <input type="radio" class="btn-check" name="gcs_e" id="gcs_e2" value="2" autocomplete="off"><label class="btn btn-outline-secondary btn-gcs d-flex align-items-center justify-content-center" for="gcs_e2">2</label>
                        <input type="radio" class="btn-check" name="gcs_e" id="gcs_e3" value="3" autocomplete="off"><label class="btn btn-outline-secondary btn-gcs d-flex align-items-center justify-content-center" for="gcs_e3">3</label>
                        <input type="radio" class="btn-check" name="gcs_e" id="gcs_e4" value="4" autocomplete="off"><label class="btn btn-outline-secondary btn-gcs d-flex align-items-center justify-content-center" for="gcs_e4">4</label>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <span>V</span>
                        <input type="radio" class="btn-check" name="gcs_v" id="gcs_v1" value="1" autocomplete="off"><label class="btn btn-outline-secondary btn-gcs d-flex align-items-center justify-content-center" for="gcs_v1">1</label>
                        <input type="radio" class="btn-check" name="gcs_v" id="gcs_v2" value="2" autocomplete="off"><label class="btn btn-outline-secondary btn-gcs d-flex align-items-center justify-content-center" for="gcs_v2">2</label>
                        <input type="radio" class="btn-check" name="gcs_v" id="gcs_v3" value="3" autocomplete="off"><label class="btn btn-outline-secondary btn-gcs d-flex align-items-center justify-content-center" for="gcs_v3">3</label>
                        <input type="radio" class="btn-check" name="gcs_v" id="gcs_v4" value="4" autocomplete="off"><label class="btn btn-outline-secondary btn-gcs d-flex align-items-center justify-content-center" for="gcs_v4">4</label>
                        <input type="radio" class="btn-check" name="gcs_v" id="gcs_v5" value="5" autocomplete="off"><label class="btn btn-outline-secondary btn-gcs d-flex align-items-center justify-content-center" for="gcs_v5">5</label>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span>M</span>
                        <input type="radio" class="btn-check" name="gcs_m" id="gcs_m1" value="1" autocomplete="off"><label class="btn btn-outline-secondary btn-gcs d-flex align-items-center justify-content-center" for="gcs_m1">1</label>
                        <input type="radio" class="btn-check" name="gcs_m" id="gcs_m2" value="2" autocomplete="off"><label class="btn btn-outline-secondary btn-gcs d-flex align-items-center justify-content-center" for="gcs_m2">2</label>
                        <input type="radio" class="btn-check" name="gcs_m" id="gcs_m3" value="3" autocomplete="off"><label class="btn btn-outline-secondary btn-gcs d-flex align-items-center justify-content-center" for="gcs_m3">3</label>
                        <input type="radio" class="btn-check" name="gcs_m" id="gcs_m4" value="4" autocomplete="off"><label class="btn btn-outline-secondary btn-gcs d-flex align-items-center justify-content-center" for="gcs_m4">4</label>
                        <input type="radio" class="btn-check" name="gcs_m" id="gcs_m5" value="5" autocomplete="off"><label class="btn btn-outline-secondary btn-gcs d-flex align-items-center justify-content-center" for="gcs_m5">5</label>
                        <input type="radio" class="btn-check" name="gcs_m" id="gcs_m6" value="6" autocomplete="off"><label class="btn btn-outline-secondary btn-gcs d-flex align-items-center justify-content-center" for="gcs_m6">6</label>
                    </div>
                </div>
                <div class="col-md-1">
                    <label for="nihss" class="form-label">NIHSS </label>
                    <input type="number" class="form-control" id="nihss" placeholder="(0-42)" min="0" max="42">
                </div>
            </div>
            <h5 class="mt-2">เวลา (Time)</h5>
            <div class="row g-3 mb-3">
                <div class="col-md-2">
                    <label for="onsetTime_onset" class="form-label">เวลาที่เริ่มมีอาการ </label>
                    <input type="date" class="form-control thai-date" id="onsetTime_onset_date" name="onsetTime_onset_date"> <input type="time" name="onsetTime_onset_time" id="onsetTime_onset_time" class="form-control mt-2"> </div>
                <div class="col-md-2">
                    <label for="onsetTime" class="form-label">เวลาที่รถออกจากต้นทาง </label>
                    <input type="date" class="form-control thai-date" id="departureTime_date" name="departureTime_date"> <input type="time" name="departureTime_time" id="departureTime_time" class="form-control mt-2"> </div>
                <div class="col-md-2">
                    <label for="arrivalTime" class="form-label">เวลาที่ถึง รพ.</label>
                    <input type="date" class="form-control thai-date" id="arrivalTime_date" name="arrivalTime_date"> <input type="time" name="arrivalTime_time" id="arrivalTime_time" class="form-control mt-2"> </div>

            </div>
            <div class="text-center mt-5 mb-3">
                <button type="button" class="btn btn-primary btn-lg px-5" id="saveMainFormBtn">
                    <i class="bi bi-save-fill me-2"></i> บันทึกข้อมูล
                </button>
            </div>
        </form>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ... (ส่วนที่ 1, 2, 3 - Logic เดิมของคุณ ... ) ...
            // 1. Logic สำหรับ "โรคประจำตัวอื่นๆ"
            const otherCheckbox = document.getElementById('comorbid_other_check');
            const otherTextField = document.getElementById('comorbid_other_text');
            if (otherCheckbox) {
                otherCheckbox.addEventListener('change', function() {
                    otherTextField.style.display = this.checked ? 'block' : 'none';
                    if (!this.checked) otherTextField.value = '';
                });
            }

            // 2. Logic สำหรับ "ประเภทบัตรอื่นๆ" (Alien/Passport)
            const cardTypeSelect = document.getElementById('card_type');
            const alienDetails = document.getElementById('alien-details');
            const passportDetails = document.getElementById('passport-details');

            if (cardTypeSelect) {
                cardTypeSelect.addEventListener('change', function() {
                    const selectedValue = this.value;
                    alienDetails.style.display = 'none';
                    passportDetails.style.display = 'none';
                    if (selectedValue === 'Alien') {
                        alienDetails.style.display = 'block';
                    } else if (selectedValue === 'Passport') {
                        passportDetails.style.display = 'block';
                    }
                });
            }

            // 3. Logic สำหรับ "ประเภทการมาของคนไข้"
            const radioButtons = document.querySelectorAll('.arrival-trigger');
            const arrivalFields = document.querySelectorAll('.arrival-field');

            function updateArrivalFields() {
                arrivalFields.forEach(function(field) {
                    field.style.display = 'none';
                });

                const selectedRadio = document.querySelector('input[name="arrival_type"]:checked');
                if (!selectedRadio) return;

                const selectedValue = selectedRadio.value;

                if (selectedValue === 'refer') {
                    document.getElementById('refer_from_field').style.display = 'block';
                } else if (selectedValue === 'ems') {
                    document.getElementById('ems_time_field').style.display = 'block';
                } else if (selectedValue === 'walk_in') {
                    document.getElementById('walkin_time_field').style.display = 'block';
                } else if (selectedValue === 'ipd') {
                    document.getElementById('ipd_time_field').style.display = 'block';
                }
            }

            radioButtons.forEach(function(radio) {
                radio.addEventListener('change', updateArrivalFields);
            });
            updateArrivalFields();


            


            // 5. Logic สำหรับปุ่มบันทึก (SweetAlert)
            const saveButton = document.getElementById('saveMainFormBtn'); 
            const mainForm = document.getElementById('mainAdmissionForm');

            if (saveButton) { 
                saveButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'ยืนยันการบันทึก',
                        text: "คุณต้องการบันทึกข้อมูลผู้ป่วยรายนี้ใช่หรือไม่?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'ใช่, บันทึกเลย!',
                        cancelButtonText: 'ยกเลิก'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            mainForm.submit(); 
                        }
                    });
                });
            }

        });
    </script>
</body>

</html>