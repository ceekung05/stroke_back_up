<?php
session_start();
require_once 'connectdb.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user_data'];

// --- 1. ตรวจสอบโหมด (เพิ่มใหม่ หรือ แก้ไข) ---
$admission_id = $_GET['admission_id'] ?? '';
$row = []; // ตัวแปรเก็บข้อมูลสำหรับแสดงผล

if ($admission_id) {
    // กรณีแก้ไข: ดึงข้อมูล Admission ผสมกับข้อมูล Patient
    $sql = "SELECT adm.*, 
                   pat.flname, pat.id_card, pat.birthdate, pat.age, pat.gender, 
                   pat.blood_type, pat.address_full, pat.other_id_type, pat.other_id_number, pat.treatment_scheme
            FROM tbl_stroke_admission adm
            LEFT JOIN tbl_patient pat ON adm.patient_hn = pat.hn
            WHERE adm.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
}

// --- 2. ฟังก์ชันช่วยแสดงผล (Helper Functions) ---

// ดึงค่าทั่วไป (Text)
function val($field)
{
    global $row;
    return htmlspecialchars($row[$field] ?? '');
}

// เช็ค Checkbox / Radio (ถ้าค่าใน DB ตรงกับ $value ให้ return 'checked')
function chk($field, $value = 1)
{
    global $row;
    return (isset($row[$field]) && $row[$field] == $value) ? 'checked' : '';
}

// เช็ค Select Option (ถ้าค่าใน DB ตรงกับ $value ให้ return 'selected')
function sel($field, $value)
{
    global $row;
    return (isset($row[$field]) && $row[$field] == $value) ? 'selected' : '';
}

// แยกวันที่และเวลาจาก DateTime (YYYY-MM-DD HH:MM:SS)
function dt($field, $type)
{
    global $row;
    if (empty($row[$field]) || $row[$field] == '0000-00-00 00:00:00') return '';

    $parts = explode(' ', $row[$field]);
    if ($type == 'd') return $parts[0] ?? ''; // คืนค่าวันที่
    if ($type == 't') return substr($parts[1] ?? '', 0, 5); // คืนค่าเวลา (ตัดวินาทีออก)
    return '';
}
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

        <a href="form.php<?= $admission_id ? '?admission_id=' . $admission_id : '' ?>" class="active">
            <i class="bi bi-person-lines-fill"></i> 1. ข้อมูลทั่วไป
        </a>
        <a href="<?= $admission_id ? 'diagnosis_form.php?admission_id=' . $admission_id : '#' ?>" class="<?= !$admission_id ? 'disabled-link' : '' ?>">
            <i class="bi bi-hospital"></i> 2. ER
        </a>
        <a href="<?= $admission_id ? 'OR_Procedure_Form.php?admission_id=' . $admission_id : '#' ?>" class="<?= !$admission_id ? 'disabled-link' : '' ?>">
            <i class="bi bi-scissors"></i> 3. OR Procedure
        </a>
        <a href="<?= $admission_id ? 'ward.php?admission_id=' . $admission_id : '#' ?>" class="<?= !$admission_id ? 'disabled-link' : '' ?>">
            <i class="bi bi-building-check"></i> 4. Ward
        </a>
        <a href="<?= $admission_id ? 'follow.php?admission_id=' . $admission_id : '#' ?>" class="<?= !$admission_id ? 'disabled-link' : '' ?>">
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
                <p class="subtitle mb-0"><?= $admission_id ? 'แก้ไขข้อมูลผู้ป่วย' : 'เพิ่มผู้ป่วยรายใหม่' ?></p>
            </div>
        </div>

        <form id="mainAdmissionForm" onsubmit="return false;">
            <input type="hidden" name="admission_id" value="<?= $admission_id ?>">

            <div class="section-title">
                <i class="bi bi-search"></i> 1. ค้นหาผู้ป่วย
            </div>
            <div class="row g-3">
                <div class="col-md-5">
                    <label for="hn_input" class="form-label"><strong>กรอกเลข HN</strong></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="hn_input" name="hn" placeholder="กรอกเลข HN..." value="<?= val('patient_hn') ?>">
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
                        <input type="text" name="id_card" id="id_card" class="form-control mt-2" value="<?= val('id_card') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">ชื่อ-สกุล</label>
                        <input type="text" class="form-control" id="display_name" name="flname" value="<?= val('flname') ?>" placeholder="...">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">วันเกิด</label>

                        <input type="text" class="form-control" id="display_date" name="birthdate_show"
                            value="<?= val('birthdate') ?>" placeholder="วว/ดด/ปปปป" readonly>

                        <input type="hidden" id="birthdate_db" name="birthdate" value="<?= dt('birthdate', 'd') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">ที่อยู่</label>
                        <input type="text" class="form-control" id="display_address" name="address_full" value="<?= val('address_full') ?>" placeholder="...">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">กรุ๊ปเลือด</label>
                        <input type="enum" class="form-control" id="display_blood_type" name="blood_type" value="<?= val('blood_type') ?>" placeholder="...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">อายุ</label>
                        <input type="text" class="form-control" id="display_age" name="age" value="<?= val('age') ?>" placeholder="...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">เพศ</label>
                        <input type="text" class="form-control" id="display_sex" name="gender" value="<?= val('gender') ?>" placeholder="...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="card_type" class="form-label mb-2">ประเภทบัตรอื่นๆ</label>
                        <select name="card_type" id="card_type" class="form-control">
                            <option value="" <?= sel('other_id_type', '') ?>>-- ไม่ระบุ --</option>
                            <option value="Alien" <?= sel('other_id_type', 'Alien') ?>>ต่างด่าว</option>
                            <option value="Passport" <?= sel('other_id_type', 'Passport') ?>>Passport</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3 details-box" id="alien-details" style="<?= val('other_id_type') == 'Alien' ? 'display:block;' : 'display: none;' ?>">
                        <label for="alien_number" class="form-label mb-2">บัตรเลขที่ (ต่างด้าว)</label>
                        <input type="text" id="alien_number" name="alien_number" class="form-control" value="<?= val('other_id_number') ?>">
                    </div>

                    <div class="col-md-3 mb-3 details-box" id="passport-details" style="<?= val('other_id_type') == 'Passport' ? 'display:block;' : 'display: none;' ?>">
                        <label for="passport_number" class="form-label mb-2">บัตรเลขที่ (Passport)</label>
                        <input type="text" id="passport_number" name="passport_number" class="form-control" value="<?= val('other_id_number') ?>">
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="" class="form-label mb-2">สิทธิการรักษา</label>
                        <div class="d-flex align-items-center gap-2">
                            <div class="btn-group" role="group" aria-label="Radio toggle button group">
                                <input type="radio" class="btn-check" name="btnTreatmentRight" id="btnradio1" value="health_insurance" <?= chk('treatment_scheme', 'health_insurance') ?>>
                                <label class="btn btn-outline-secondary" for="btnradio1">ประกันสุขภาพ</label>

                                <input type="radio" class="btn-check" name="btnTreatmentRight" id="btnradio2" value="social_security" <?= chk('treatment_scheme', 'social_security') ?>>
                                <label class="btn btn-outline-secondary" for="btnradio2">ประกันสังคม</label>

                                <input type="radio" class="btn-check" name="btnTreatmentRight" id="btnradio3" value="affiliation" <?= chk('treatment_scheme', 'affiliation') ?>>
                                <label class="btn btn-outline-secondary" for="btnradio3">ต้นสังกัด</label>

                                <input type="radio" class="btn-check" name="btnTreatmentRight" id="btnradio4" value="self_pay" <?= chk('treatment_scheme', 'self_pay') ?>>
                                <label class="btn btn-outline-secondary" for="btnradio4">จ่ายเงินเอง</label>

                                <input type="radio" class="btn-check" name="btnTreatmentRight" id="btnradio5" value="t99" <?= chk('treatment_scheme', 't99') ?>>
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
                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="comorbid_ht" value="1" id="comorbid_ht" <?= chk('is_ht') ?>><label class="form-check-label" for="comorbid_ht">HT</label></div>
                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="comorbid_dm" value="1" id="comorbid_dm" <?= chk('is_dm') ?>><label class="form-check-label" for="comorbid_dm">DM</label></div>
                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="old_cva" value="1" id="old_cva" <?= chk('is_old_cva') ?>><label class="form-check-label" for="old_cva">OLD CVA</label></div>
                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="comorbid_mi" value="1" id="comorbid_mi" <?= chk('is_mi') ?>><label class="form-check-label" for="comorbid_mi">MI</label></div>
                </div>
                <div class="col-md-4">
                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="comorbid_af" value="1" id="comorbid_af" <?= chk('is_af') ?>><label class="form-check-label" for="comorbid_af">AF</label></div>
                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="comorbid_dlp" value="1" id="comorbid_dlp" <?= chk('is_dlp') ?>><label class="form-check-label" for="comorbid_dlp">DLP</label></div>
                </div>
                <div class="col-md-4">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="comorbid_other_check" value="1" id="comorbid_other_check" <?= !empty($row['is_other_text']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="comorbid_other_check">OTHER</label>
                    </div>
                    <input type="text" class="form-control" name="comorbid_other_text" id="comorbid_other_text" placeholder="ระบุโรคประจำตัวอื่นๆ..." value="<?= val('is_other_text') ?>" style="<?= !empty($row['is_other_text']) ? 'display:block;' : 'display: none;' ?>">
                </div>
            </div>

            <div class="section-title">
                <i class="bi bi-person-x"></i> 4. สารเสพติด (Addictive Substance)
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="comorbid_alcohol" value="1" id="comorbid_alcohol" <?= chk('addict_alcohol') ?>><label class="form-check-label" for="comorbid_alcohol">ALCOHOL</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="comorbid_smoking" value="1" id="comorbid_smoking" <?= chk('addict_smoking') ?>>
                        <label class="form-check-label" for="comorbid_smoking">SMOKING</label>
                    </div>
                </div>
            </div>

            <div class="section-title">
                <i class="bi bi-door-open"></i> 5. ประเภทการมาของคนไข้ (Arrival Type)
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input arrival-trigger" type="radio" name="arrival_type" id="arrival_refer" value="refer" <?= chk('arrival_type', 'refer') ?>>
                    <label class="form-check-label" for="arrival_refer"> Refer </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input arrival-trigger" type="radio" name="arrival_type" id="arrival_ems" value="ems" <?= chk('arrival_type', 'ems') ?>>
                    <label class="form-check-label" for="arrival_ems"> EMS (1669) </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input arrival-trigger" type="radio" name="arrival_type" id="arrival_walk_in" value="walk_in" <?= chk('arrival_type', 'walk_in') ?>>
                    <label class="form-check-label" for="arrival_walk_in"> Walk in </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input arrival-trigger" type="radio" name="arrival_type" id="arrival_ipd" value="ipd" <?= chk('arrival_type', 'ipd') ?>>
                    <label class="form-check-label" for="arrival_ipd"> IPD (ย้ายจากตึกใน) </label>
                </div>
            </div>

            <div class="col-md-5 mt-3 arrival-field" id="refer_from_field" style="<?= chk('arrival_type', 'refer') ? 'display:block' : 'display:none' ?>">
                <div class="col-md-6">
                    <label for="refer_from_text" class="form-label">Refer จาก (ระบุโรงพยาบาล):</label>
                    <input type="text" class="form-control" name="refer_from_text" id="refer_from_text" placeholder="ระบุชื่อโรงพยาบาล..." value="<?= val('refer_from_hospital') ?>">
                </div>
                <div class="row mt-2">
                    <div class="col-md-5">
                        <label for="refer_arrival_time" class="form-label ">วันที่ที่ผป.มาถึง</label>
                        <input type="date" class="form-control" name="refer_arrival_date" value="<?= dt('refer_arrival_datetime', 'd') ?>">
                    </div>
                    <div class="col-md-5 mt-auto">
                        <input type="time" class="form-control" name="refer_arrival_time" value="<?= dt('refer_arrival_datetime', 't') ?>">
                    </div>
                </div>
            </div>

            <div class="col-md-5 mt-3 arrival-field" id="ems_time_field" style="<?= chk('arrival_type', 'ems') ? 'display:block' : 'display:none' ?>">
                <div class="row">
                    <div class="col-md-6">
                        <label for="first_medical_contact" class="form-label mt-2">First Medical contact</label>
                        <input type="date" class="form-control" id="first_medical_contact" name="first_medical_contact_date" value="<?= dt('ems_first_medical_contact', 'd') ?>">
                    </div>
                    <div class="col-md-6 mt-auto">
                        <input type="time" class="form-control" name="first_medical_contact_time" value="<?= dt('ems_first_medical_contact', 't') ?>">
                    </div>
                </div>
            </div>

            <div class="col-md-5 mt-3 arrival-field" id="walkin_time_field" style="<?= chk('arrival_type', 'walk_in') ? 'display:block' : 'display:none' ?>">
                <div class="row">
                    <div class="col-md-6">
                        <label for="er_arrival_time" class="form-label mt-2">เวลาที่มาถึงห้องฉุกเฉิน</label>
                        <input type="date" class="form-control" id="er_arrival_date" name="er_arrival_date" value="<?= dt('walk_in_datetime', 'd') ?>">
                    </div>
                    <div class="col-md-6 mt-auto">
                        <input type="time" class="form-control" name="er_arrival_time" value="<?= dt('walk_in_datetime', 't') ?>">
                    </div>
                </div>
            </div>

            <div class="col-md-5 mt-3 arrival-field" id="ipd_time_field" style="<?= chk('arrival_type', 'ipd') ? 'display:block' : 'display:none' ?>">
                <div class="col-md-6">
                    <label for="ipd_ward" class="form-label mt-2 ">หอผู้ป่วย</label>
                    <select name="ipd_ward" id="ipd_ward" class="form-select mb-2">
                        <option value="" selected disabled class="text-center">--ระบุหอผู้ป่วย--</option>
                        <option value="กุมารเวชกรรม 1" <?= sel('ipd_ward_name', 'กุมารเวชกรรม 1') ?>>กุมารเวชกรรม 1</option>
                        <option value="กุมารเวชกรรม 2" <?= sel('ipd_ward_name', 'กุมารเวชกรรม 2') ?>>กุมารเวชกรรม 2</option>
                        <option value="โรคติดเชื้อในเด็ก" <?= sel('ipd_ward_name', 'โรคติดเชื้อในเด็ก') ?>>โรคติดเชื้อในเด็ก</option>
                        <option value="กึ่งวิกฤตทารกแรกเกิด" <?= sel('ipd_ward_name', 'กึ่งวิกฤตทารกแรกเกิด') ?>>กึ่งวิกฤตทารกแรกเกิด</option>
                        <option value="อายุรกรรมหญิง 1" <?= sel('ipd_ward_name', 'อายุรกรรมหญิง 1') ?>>อายุรกรรมหญิง 1</option>
                        <option value="อายุรกรรมหญิง 2" <?= sel('ipd_ward_name', 'อายุรกรรมหญิง 2') ?>>อายุรกรรมหญิง 2</option>
                        <option value="อายุรกรรมชาย 1" <?= sel('ipd_ward_name', 'อายุรกรรมชาย 1') ?>>อายุรกรรมชาย 1</option>
                        <option value="อายุรกรรมชาย 2" <?= sel('ipd_ward_name', 'อายุรกรรมชาย 2') ?>>อายุรกรรมชาย 2</option>
                        <option value="อายุรกรรมชาย 3" <?= sel('ipd_ward_name', 'อายุรกรรมชาย 3') ?>>อายุรกรรมชาย 3</option>
                        <option value="หน่วยโลหิตวิทยา" <?= sel('ipd_ward_name', 'หน่วยโลหิตวิทยา') ?>>หน่วยโลหิตวิทยา</option>
                        <option value="ศูนย์ปลูกถ่ายไขกระดูก" <?= sel('ipd_ward_name', 'ศูนย์ปลูกถ่ายไขกระดูก') ?>>ศูนย์ปลูกถ่ายไขกระดูก</option>
                        <option value="ศัลยโรคหลอดเลือดสมอง" <?= sel('ipd_ward_name', 'ศัลยโรคหลอดเลือดสมอง') ?>>ศัลยโรคหลอดเลือดสมอง</option>
                        <option value="อายุรกรรมหญิง 3" <?= sel('ipd_ward_name', 'อายุรกรรมหญิง 3') ?>>อายุรกรรมหญิง 3</option>
                        <option value="อายุรกรรมหญิง 5" <?= sel('ipd_ward_name', 'อายุรกรรมหญิง 5') ?>>อายุรกรรมหญิง 5</option>
                        <option value="หอผู้ป่วยจักษุ" <?= sel('ipd_ward_name', 'หอผู้ป่วยจักษุ') ?>>หอผู้ป่วยจักษุ</option>
                        <option value="อายุรกรรมชาย 3" <?= sel('ipd_ward_name', 'อายุรกรรมชาย 3') ?>>อายุรกรรมชาย 3</option>
                        <option value="พิเศษประกันสังคม ชั้น 5" <?= sel('ipd_ward_name', 'พิเศษประกันสังคม ชั้น 5') ?>>พิเศษประกันสังคม ชั้น 5</option>
                        <option value="จักษุโสตศอนาสิก (รวมสระอาพาธ)" <?= sel('ipd_ward_name', 'จักษุโสตศอนาสิก (รวมสระอาพาธ)') ?>>จักษุโสตศอนาสิก (รวมสระอาพาธ)</option>
                        <option value="ผู้ป่วยนอก" <?= sel('ipd_ward_name', 'ผู้ป่วยนอก') ?>>ผู้ป่วยนอก</option>
                        <option value="อายุรกรรมชาย 4" <?= sel('ipd_ward_name', 'อายุรกรรมชาย 4') ?>>อายุรกรรมชาย 4</option>
                        <option value="พิเศษชั้น 10" <?= sel('ipd_ward_name', 'พิเศษชั้น 10') ?>>พิเศษชั้น 10</option>
                        <option value="พิเศษชั้น 10 รวม" <?= sel('ipd_ward_name', 'พิเศษชั้น 10 รวม') ?>>พิเศษชั้น 10 รวม</option>
                        <option value="พิเศษชั้น 10 เดี่ยว" <?= sel('ipd_ward_name', 'พิเศษชั้น 10 เดี่ยว') ?>>พิเศษชั้น 10 เดี่ยว</option>
                        <option value="ศัลยกรรมทรวงอกและอุบัติเหตุ" <?= sel('ipd_ward_name', 'ศัลยกรรมทรวงอกและอุบัติเหตุ') ?>>ศัลยกรรมทรวงอกและอุบัติเหตุ</option>
                        <option value="ศัลยกรรมทรวงอกและชาย" <?= sel('ipd_ward_name', 'ศัลยกรรมทรวงอกและชาย') ?>>ศัลยกรรมทรวงอกและชาย</option>
                        <option value="ศัลยกรรมชาย 1" <?= sel('ipd_ward_name', 'ศัลยกรรมชาย 1') ?>>ศัลยกรรมชาย 1</option>
                        <option value="ศัลยกรรมชาย 2" <?= sel('ipd_ward_name', 'ศัลยกรรมชาย 2') ?>>ศัลยกรรมชาย 2</option>
                        <option value="ศัลยกรรมหญิง 1" <?= sel('ipd_ward_name', 'ศัลยกรรมหญิง 1') ?>>ศัลยกรรมหญิง 1</option>
                        <option value="ศัลยกรรมการบาดเจ็บ (1)" <?= sel('ipd_ward_name', 'ศัลยกรรมการบาดเจ็บ (1)') ?>>ศัลยกรรมการบาดเจ็บ (1)</option>
                        <option value="ศัลยกรรมการบาดเจ็บ (2)" <?= sel('ipd_ward_name', 'ศัลยกรรมการบาดเจ็บ (2)') ?>>ศัลยกรรมการบาดเจ็บ (2)</option>
                        <option value="BURN UNIT" <?= sel('ipd_ward_name', 'BURN UNIT') ?>>BURN UNIT</option>
                        <option value="ศัลยกรรมประสาท" <?= sel('ipd_ward_name', 'ศัลยกรรมประสาท') ?>>ศัลยกรรมประสาท</option>
                        <option value="พิเศษชั้น 6" <?= sel('ipd_ward_name', 'พิเศษชั้น 6') ?>>พิเศษชั้น 6</option>
                        <option value="พิเศษชั้น 6-1" <?= sel('ipd_ward_name', 'พิเศษชั้น 6-1') ?>>พิเศษชั้น 6-1</option>
                        <option value="พิเศษชั้น 6-2" <?= sel('ipd_ward_name', 'พิเศษชั้น 6-2') ?>>พิเศษชั้น 6-2</option>
                        <option value="พิเศษชั้น 9" <?= sel('ipd_ward_name', 'พิเศษชั้น 9') ?>>พิเศษชั้น 9</option>
                        <option value="พิเศษชั้น 9 รวม" <?= sel('ipd_ward_name', 'พิเศษชั้น 9 รวม') ?>>พิเศษชั้น 9 รวม</option>
                        <option value="พิเศษชั้น 9 เดี่ยว" <?= sel('ipd_ward_name', 'พิเศษชั้น 9 เดี่ยว') ?>>พิเศษชั้น 9 เดี่ยว</option>
                        <option value="พิเศษชั้น 8" <?= sel('ipd_ward_name', 'พิเศษชั้น 8') ?>>พิเศษชั้น 8</option>
                        <option value="พิเศษชั้น 8 รวม" <?= sel('ipd_ward_name', 'พิเศษชั้น 8 รวม') ?>>พิเศษชั้น 8 รวม</option>
                        <option value="พิเศษชั้น 8 เดี่ยว" <?= sel('ipd_ward_name', 'พิเศษชั้น 8 เดี่ยว') ?>>พิเศษชั้น 8 เดี่ยว</option>
                        <option value="AIIR" <?= sel('ipd_ward_name', 'AIIR') ?>>AIIR</option>
                        <option value="CCU" <?= sel('ipd_ward_name', 'CCU') ?>>CCU</option>
                        <option value="SICU" <?= sel('ipd_ward_name', 'SICU') ?>>SICU</option>
                        <option value="MICU-1" <?= sel('ipd_ward_name', 'MICU-1') ?>>MICU-1</option>
                        <option value="MICU-2" <?= sel('ipd_ward_name', 'MICU-2') ?>>MICU-2</option>
                        <option value="MICU-3" <?= sel('ipd_ward_name', 'MICU-3') ?>>MICU-3</option>
                        <option value="NSICU" <?= sel('ipd_ward_name', 'NSICU') ?>>NSICU</option>
                        <option value="NICU (Neonatal ICU)" <?= sel('ipd_ward_name', 'NICU (Neonatal ICU)') ?>>NICU (Neonatal ICU)</option>
                        <option value="PICU" <?= sel('ipd_ward_name', 'PICU') ?>>PICU</option>
                        <option value="หาดใหญ่-นามหม่อม" <?= sel('ipd_ward_name', 'หาดใหญ่-นามหม่อม ') ?>>หาดใหญ่-นามหม่อม</option>
                        <option value="หาดใหญ่-นามหม่อม 3" <?= sel('ipd_ward_name', 'หาดใหญ่-นามหม่อม 3') ?>>หาดใหญ่-นามหม่อม 3</option>
                        <option value="หาดใหญ่-นามหม่อม 4" <?= sel('ipd_ward_name', 'หาดใหญ่-นามหม่อม 4') ?>>หาดใหญ่-นามหม่อม 4</option>
                        <option value="หาดใหญ่-นามหม่อม 5" <?= sel('ipd_ward_name', 'หาดใหญ่-นามหม่อม 5') ?>>หาดใหญ่-นามหม่อม 5</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">เวลาเริ่มมีอาการในหอผู้ป่วย</label>
                        <input type="date" class="form-control" id="ipd_onset_date" name="ipd_onset_date" value="<?= dt('ipd_onset_datetime', 'd') ?>">
                    </div>
                    <div class="col-md-6 mt-auto">
                        <input type="time" class="form-control" name="ipd_onset_time" value="<?= dt('ipd_onset_datetime', 't') ?>">
                    </div>
                </div>
            </div>

            <div class="section-title">
                <i class="bi bi-capsule"></i> 6. ยาประจำตัว (Regular medication)
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="med_anti_platelet" value="1" id="med_anti_platelet" <?= chk('med_anti_platelet') ?>>
                        <label class="form-check-label" for="med_anti_platelet">Anti-platelet (ยาต้านเกล็ดเลือด)</label>
                        <div class="card-body mt-2">
                            <fieldset>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="med_asa" name="med_asa" <?= chk('med_asa') ?>>
                                    <label class="form-check-label" for="med_asa"><strong>ASA (Aspirin)</strong></label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="med_clopidogrel" name="med_clopidogrel" <?= chk('med_clopidogrel') ?>>
                                    <label class="form-check-label" for="med_clopidogrel"><strong>Clopidogrel</strong></label>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="med_anti_coagulant" value="1" id="med_anti_coagulant" <?= chk('med_anti_coagulant') ?>>
                        <label class="form-check-label" for="med_anti_coagulant">Anti-coagulant</label>
                        <div class="card-body mt-2">
                            <fieldset>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="med_warfarin" name="med_warfarin" <?= chk('med_warfarin') ?>>
                                    <label class="form-check-label" for="med_warfarin"><strong>Warfarin</strong></label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="med_noac" name="med_noac" <?= chk('med_noac') ?>>
                                    <label class="form-check-label" for="med_noac"><strong>NOAC</strong></label>
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
                        <option value="" disabled <?= sel('pre_morbid_mrs', '') ?>>---- กรุณาเลือก ----</option>
                        <?php for ($i = 0; $i <= 6; $i++) echo "<option value='$i' " . sel('pre_morbid_mrs', $i) . ">$i</option>"; ?>
                    </select>
                </div>
            </div>

            <div class="section-title">
                <i class="bi bi-card-checklist"></i> 8. ส่วนประเมินแรกรับ (Triage Assessment)
            </div>
            <div class="card-body">
                <p class="fs-5">ผู้ป่วยเข้าเกณฑ์ Stroke Fast Track หรือไม่?</p>
                <div class="d-flex align-items-center gap-2">
                    <input type="radio" class="btn-check" name="fast_track_status" id="fast_track_yes" value="yes" autocomplete="off" required <?= chk('fast_track_status', 'yes') ?>>
                    <label class="btn btn-outline-secondary btn-toggle-custom" for="fast_track_yes"> Yes </label>

                    <input type="radio" class="btn-check" name="fast_track_status" id="fast_track_no" value="no" autocomplete="off" required <?= chk('fast_track_status', 'no') ?>>
                    <label class="btn btn-outline-secondary btn-toggle-custom" for="fast_track_no"> No </label>
                </div>
            </div>
            <h5 class="mt-4">อาการสำคัญ (Symptoms - F.A.S.T.)</h5>
            <div class="mb-3">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="sympDroop" value="1" name="sympDroop" <?= chk('symp_face') ?>><label class="form-check-label" for="sympDroop">F(Face)</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="sympWeakness" value="1" name="sympWeakness" <?= chk('symp_arm') ?>><label class="form-check-label" for="sympWeakness">A(Arm)</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="sympSpeech" value="1" name="sympSpeech" <?= chk('symp_speech') ?>><label class="form-check-label" for="sympSpeech">S(Speech)</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="symp_v" name="symp_v" value="1" <?= chk('symp_vision') ?>><label class="form-check-label" for="symp_v">V(Vision)</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="symp_a2" name="symp_a2" value="1" <?= chk('symp_aphasia') ?>><label class="form-check-label" for="symp_a2">A(Aphasia)</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="symp_n" name="symp_n" value="1" <?= chk('symp_neglect') ?>><label class="form-check-label" for="symp_n">N(Neglect)</label>
                </div>
            </div>

            <h5 class="mt-4">การประเมินแรกรับ (Scores)</h5>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">GCS</label>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span>E</span>
                        <?php for ($i = 1; $i <= 4; $i++) echo "<input type='radio' class='btn-check' name='gcs_e' id='gcs_e$i' value='$i' " . chk('gcs_e', $i) . "><label class='btn btn-outline-secondary btn-gcs' for='gcs_e$i'>$i</label>"; ?>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span>V</span>
                        <?php for ($i = 1; $i <= 5; $i++) echo "<input type='radio' class='btn-check' name='gcs_v' id='gcs_v$i' value='$i' " . chk('gcs_v', $i) . "><label class='btn btn-outline-secondary btn-gcs' for='gcs_v$i'>$i</label>"; ?>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span>M</span>
                        <?php for ($i = 1; $i <= 6; $i++) echo "<input type='radio' class='btn-check' name='gcs_m' id='gcs_m$i' value='$i' " . chk('gcs_m', $i) . "><label class='btn btn-outline-secondary btn-gcs' for='gcs_m$i'>$i</label>"; ?>
                    </div>
                </div>
                <div class="col-md-1">
                    <label for="nihss" class="form-label">NIHSS</label>
                    <input type="number" class="form-control" id="nihss" placeholder="(0-42)" min="0" max="42" name="nihss" required value="<?= val('nihss_score') ?>">
                </div>
            </div>

            <h5 class="mt-2">เวลา (Time)</h5>
            <div class="row g-3 mb-3">
                <div class="col-md-2">
                    <label class="form-label">เวลาที่เริ่มมีอาการ</label>
                    <input type="date" class="form-control" name="onsetTime_onset_date" value="<?= dt('onset_datetime', 'd') ?>">
                    <input type="time" class="form-control mt-2" name="onsetTime_onset_time" value="<?= dt('onset_datetime', 't') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">เวลาที่รถออกจากต้นทาง</label>
                    <input type="date" class="form-control" name="departureTime_date" value="<?= dt('departure_datetime', 'd') ?>">
                    <input type="time" class="form-control mt-2" name="departureTime_time" value="<?= dt('departure_datetime', 't') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">เวลาที่ถึง รพ.</label>
                    <input type="date" class="form-control" name="arrivalTime_date" value="<?= dt('hospital_arrival_datetime', 'd') ?>">
                    <input type="time" class="form-control mt-2" name="arrivalTime_time" value="<?= dt('hospital_arrival_datetime', 't') ?>">
                </div>
            </div>

            <div class="text-center mt-5 mb-3">
                <button type="button" class="btn btn-primary btn-lg px-5" id="saveMainFormBtn">
                    <i class="bi bi-save-fill me-2"></i> บันทึกข้อมูล
                </button>

                <a href="<?= $admission_id ? 'diagnosis_form.php?admission_id=' . $admission_id : '#' ?>"
                    id="nextStepBtn"
                    class="btn btn-success btn-lg px-5 ms-2 <?= $admission_id ? '' : 'd-none' ?>">
                    ไปยังหน้า 2 (ER) <i class="bi bi-arrow-right-circle-fill ms-2"></i>
                </a>
            </div>

        </form>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

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
                // ซ่อนทั้งหมดก่อน
                arrivalFields.forEach(field => field.style.display = 'none');

                const selectedRadio = document.querySelector('input[name="arrival_type"]:checked');
                if (!selectedRadio) return;

                const selectedValue = selectedRadio.value;
                if (selectedValue === 'refer') document.getElementById('refer_from_field').style.display = 'block';
                else if (selectedValue === 'ems') document.getElementById('ems_time_field').style.display = 'block';
                else if (selectedValue === 'walk_in') document.getElementById('walkin_time_field').style.display = 'block';
                else if (selectedValue === 'ipd') document.getElementById('ipd_time_field').style.display = 'block';
            }

            radioButtons.forEach(radio => radio.addEventListener('change', updateArrivalFields));
            // เรียกทำงานครั้งแรก (กรณีเป็นการ Edit และมีค่าเดิมอยู่แล้ว)
            // updateArrivalFields();  <-- บรรทัดนี้ไม่ต้อง PHP จัดการ style display ให้แล้ว
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fetchButton = document.getElementById('fetch_patient_btn');
            const hnInput = document.getElementById('hn_input');

            // ตัวแปร input ที่จะนำข้อมูลไปใส่
            const displayCid = document.getElementById('id_card');
            const displayName = document.getElementById('display_name');
            const displayAge = document.getElementById('display_age');
            const displaySex = document.getElementById('display_sex');
            const displayAd = document.getElementById('display_address');
            const displayDate = document.getElementById('display_date');
            const displayBT = document.getElementById('display_blood_type');

            if (fetchButton) {
                fetchButton.addEventListener('click', function() {
                    const hnValue = hnInput.value.trim();
                    if (!hnValue) {
                        Swal.fire('Warning', 'กรุณาระบุ HN', 'warning');
                        return;
                    }

                    Swal.fire({
                        title: 'กำลังค้นหา...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    const formData = new FormData();
                    formData.append('hn', hnValue);

                    fetch('api_patient.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            // ปิด Loading
                            Swal.close();

                            if (data && Array.isArray(data) && data.length > 0) {
                                const p = data[0]; // ข้อมูลผู้ป่วย
                                Swal.fire({
                                    icon: 'success',
                                    title: 'พบข้อมูล!',
                                    timer: 1000,
                                    showConfirmButton: false
                                });

                                // นำข้อมูลใส่ Input
                                displayName.value = p.flname || '';
                                displayCid.value = p.id_card || '';
                                displayAge.value = p.age_year || '';
                                displaySex.value = p.sex || '';
                                displayAd.value = p.hname || '';
                                displayDate.value = p.bdate || '';
                                displayBT.value = p.bgname || '';

                                // 2. แปลงเป็น YYYY-MM-DD ลงในช่องที่ซ่อนอยู่ (birthdate_db)
                                // 2. [สำคัญ] แปลงเป็น YYYY-MM-DD เพื่อยัดใส่ช่องซ่อน (Hidden Input)
                                if (p.bdate) {
                                    // เรียกฟังก์ชันแปลง (ดูฟังก์ชันด้านล่าง)
                                    document.getElementById('birthdate_db').value = convertThaiDateToMySQL(p.bdate);
                                } else {
                                    document.getElementById('birthdate_db').value = '';
                                }
                            } else {
                                Swal.fire('Error', 'ไม่พบข้อมูลผู้ป่วย', 'error');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Error', 'การเชื่อมต่อขัดข้อง', 'error');
                        });
                });
            }

            // ฟังก์ชันแปลง "31/01/2567" -> "2024-01-31"
            function convertThaiDateToMySQL(thaiDateString) {
                if (!thaiDateString) return "";

                // สมมติว่า API ส่งมาเป็น "31/01/2567" (คั่นด้วย /)
                const parts = thaiDateString.split('/');

                if (parts.length === 3) {
                    const day = parts[0];
                    const month = parts[1];
                    const yearThai = parseInt(parts[2]);

                    // ลบ 543 เพื่อเป็น ค.ศ.
                    const yearEng = yearThai - 543;

                    // คืนค่า format ที่ Database ชอบ
                    return `${yearEng}-${month}-${day}`;
                }

                // กันเหนียว: ถ้า format ไม่ตรงที่คิดไว้ ให้ส่งค่าเดิมกลับไป
                return thaiDateString;
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const saveButton = document.getElementById('saveMainFormBtn');
            const nextButton = document.getElementById('nextStepBtn');
            const mainForm = document.getElementById('mainAdmissionForm');

            if (saveButton) {
                saveButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'ยืนยันการบันทึก',
                        text: "ต้องการบันทึกข้อมูลใช่หรือไม่?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'ใช่, บันทึกเลย'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.showLoading(); // โชว์ loading

                            const formData = new FormData(mainForm);

                            fetch('save_admission.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'บันทึกเรียบร้อย!',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });

                                        // ปรับปุ่ม
                                        saveButton.innerHTML = '<i class="bi bi-check-lg"></i> บันทึกแล้ว';
                                        saveButton.classList.replace('btn-primary', 'btn-secondary');

                                        // เปิดปุ่ม Next
                                        nextButton.classList.remove('d-none');
                                        nextButton.href = data.redirect_url;

                                        // (Optional) ถ้าเป็นการเพิ่มใหม่ อาจจะ redirect ไปเลยก็ได้
                                        // window.location.href = data.redirect_url;

                                    } else {
                                        Swal.fire('Error', data.message, 'error');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    Swal.fire('Error', 'เกิดข้อผิดพลาดในการบันทึก', 'error');
                                });
                        }
                    });
                });
            }
        });
    </script>
</body>

</html>