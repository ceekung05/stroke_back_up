<?php
session_start();
require_once 'connectdb.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// --- 1. ตรวจสอบโหมด (เพิ่มใหม่ หรือ แก้ไข) ---
$admission_id = $_GET['admission_id'] ?? '';
$row = []; // ตัวแปรเก็บข้อมูลสำหรับแสดงผล

if ($admission_id) {
    // แก้ไข: เพิ่มฟิลด์ที่อยู่ย่อยลงใน SELECT
    $sql = "SELECT adm.*, 
                   pat.flname, pat.id_card, pat.birthdate, pat.age, pat.gender, 
                   pat.blood_type, pat.address_full, pat.subdistrict, pat.district, pat.province, pat.zipcode, 
                   pat.other_id_type, pat.other_id_number, pat.treatment_scheme, pat.phone_number
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
function val($field) {
    global $row;
    return htmlspecialchars($row[$field] ?? '');
}

// เช็ค Checkbox / Radio (ถ้าค่าใน DB ตรงกับ $value ให้ return 'checked')
function chk($field, $value = 1) {
    global $row;
    return (isset($row[$field]) && $row[$field] == $value) ? 'checked' : '';
}

// เช็ค Select Option (ถ้าค่าใน DB ตรงกับ $value ให้ return 'selected')
function sel($field, $value) {
    global $row;
    return (isset($row[$field]) && $row[$field] == $value) ? 'selected' : '';
}

// แยกวันที่และเวลาจาก DateTime (YYYY-MM-DD HH:MM:SS)
function dt($field, $type) {
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
        <hr class="sidebar-divider">
        <a href="dashboard.php">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="index.php">
            <i class="bi bi-list-task"></i> กลับไปหน้า Patient List
        </a>
        <hr class="sidebar-divider">

        <a href="form.php<?= $admission_id ? '?admission_id=' . $admission_id : '' ?>" class="active">
            <i class="bi bi-person-lines-fill"></i> 1. ข้อมูลทั่วไป
        </a>
        <a href="<?= $admission_id ? 'diagnosis_form.php?admission_id=' . $admission_id : 'diagnosis_form.php' ?>" class="<?= !$admission_id ? 'disabled-link' : '' ?>">
            <i class="bi bi-hospital"></i> 2. ER
        </a>
        <a href="<?= $admission_id ? 'OR_Procedure_Form.php?admission_id=' . $admission_id : 'OR_Procedure_Form.php' ?>" class="<?= !$admission_id ? 'disabled-link' : '' ?>">
            <i class="bi bi-scissors"></i> 3. OR Procedure
        </a>
        <a href="<?= $admission_id ? 'ward.php?admission_id=' . $admission_id : 'ward.php' ?>" class="<?= !$admission_id ? 'disabled-link' : '' ?>">
            <i class="bi bi-building-check"></i> 4. Ward
        </a>
        <a href="<?= $admission_id ? 'follow.php?admission_id=' . $admission_id : 'follow.php' ?>" class="<?= !$admission_id ? 'disabled-link' : '' ?>">
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

            <!-- ส่วนที่ 1: การค้นหา (Search) -->
            <div class="card-form">
                <div class="section-title" style="margin-top:0;">
                    <i class="bi bi-search"></i> 1. ค้นหาผู้ป่วย
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">เลือกประเภทการค้นหา</label>
                        <div class="mb-2">
                            <!-- ทางเลือกที่ 1: HN -->
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="search_type" id="search_type_hn" value="hn" checked>
                                <label class="form-check-label" for="search_type_hn">ค้นหาด้วย HN</label>
                            </div>
                            <!-- ทางเลือกที่ 2: AN -->
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="search_type" id="search_type_an" value="an">
                                <label class="form-check-label" for="search_type_an">ค้นหาด้วย AN</label>
                            </div>
                        </div>

                        <div class="input-group">
                            <input type="text" class="form-control" id="search_keyword" name="search_keyword"
                                placeholder="กรอกเลข HN..." value="<?= val('patient_hn') ?>">
                            <button class="btn btn-primary" type="button" id="fetch_patient_btn">
                                <i class="bi bi-search"></i> ค้นหาข้อมูล
                            </button>
                        </div>
                    </div>
                </div>

                <hr class="text-muted opacity-25 my-4">

                <!-- ช่องกรอกข้อมูลสำคัญ (HN, AN, Date Admit) -->
                <div class="row g-3 mb-3">
                    <!-- HN -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">HN</label>
                        <!-- Hidden input เก็บค่า HN จริง เพื่อส่งไปบันทึก -->
                        <input type="hidden" id="real_hn_field" name="hn" value="<?= val('patient_hn') ?>">
                        <input type="text" class="form-control fw-bold bg-light" id="patient_hn_display" value="<?= val('patient_hn') ?>" readonly>
                    </div>

                    <!-- AN -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-primary">
                            <i class="bi bi-clipboard-data me-1"></i> AN (Admission No.)
                        </label>
                        <input type="text" class="form-control fw-bold border-primary"
                            name="patient_an"
                            id="patient_an"
                            value="<?= val('patient_an') ?>"
                            placeholder="เช่น 66/00123" required>
                    </div>

                    <!-- Date Admit -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-primary">
                            <i class="bi bi-calendar-plus-fill me-1"></i> วันที่ Admit
                        </label>
                        <input type="date" class="form-control border-primary"
                            name="date_admit"
                            id="date_admit"
                            value="<?= dt('date_admit', 'd') ?>"
                            required>
                        <div class="form-text text-muted">วันที่รับผู้ป่วยไว้นอนโรงพยาบาล</div>
                    </div>
                </div>
            </div>

            <div id="patient_info_section" class="card-form">
                <div class="section-title" style="margin-top:0;">
                    <i class="bi bi-person-badge-fill text-primary"></i> 2. ข้อมูลผู้ป่วย (Patient Information)
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label text-secondary"><i class="bi bi-person-vcard me-1"></i> เลขบัตรประชาชน</label>
                        <input type="text" name="id_card" id="id_card" class="form-control fw-bold bg-light" value="<?= val('id_card') ?>" placeholder="เลข 13 หลัก" readonly>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label text-secondary"><i class="bi bi-person-fill me-1"></i> ชื่อ-สกุล</label>
                        <input type="text" class="form-control fw-bold text-primary" id="display_name" name="flname" value="<?= val('flname') ?>" placeholder="ชื่อ-นามสกุล">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-secondary"><i class="bi bi-calendar-event me-1"></i> วันเกิด</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="display_date" name="birthdate_show"
                                value="<?= dt('birthdate', 'd') ? date('d/m/Y', strtotime(dt('birthdate', 'd') . ' +543 years')) : '' ?>"
                                placeholder="วว/ดด/ปปปป">
                            <span class="input-group-text bg-white text-muted"><i class="bi bi-calendar3"></i></span>
                        </div>
                        <input type="hidden" id="birthdate_db" name="birthdate" value="<?= dt('birthdate', 'd') ?>">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label text-secondary"><i class="bi bi-hourglass-split me-1"></i> อายุ</label>
                        <input type="text" class="form-control text-center bg-light" id="display_age" name="age" value="<?= val('age') ?>" placeholder="-" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-secondary"><i class="bi bi-gender-ambiguous me-1"></i> เพศ</label>
                        <input type="text" class="form-control text-center bg-light" id="display_sex" name="gender" value="<?= val('gender') ?>" placeholder="-">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-secondary"><i class="bi bi-droplet-fill me-1"></i> กรุ๊ปเลือด</label>
                        <input type="text" class="form-control text-center" id="display_blood_type" name="blood_type" value="<?= val('blood_type') ?>" placeholder="-">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-secondary"><i class="bi bi-telephone-fill me-1"></i> เบอร์โทรศัพท์</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?= val('phone_number') ?>" placeholder="0xx-xxxxxxx">
                    </div>

                    <div class="col-md-5">
                        <label class="form-label text-secondary"><i class="bi bi-geo-alt-fill me-1"></i> ที่อยู่</label>
                        <input type="text" class="form-control" id="display_address" name="address_full" value="<?= val('address_full') ?>" placeholder="ที่อยู่ปัจจุบัน...">
                    </div>

                    <div id="detailed_address_section" class="row g-3 mt-1">
                        <div class="col-md-3">
                            <label class="form-label text-secondary small">ตำบล/แขวง</label>
                            <input type="text" class="form-control bg-light" id="subdistrict" name="subdistrict" value="<?= val('subdistrict') ?>" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary small">อำเภอ/เขต</label>
                            <input type="text" class="form-control bg-light" id="district" name="district" value="<?= val('district') ?>" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary small">จังหวัด</label>
                            <input type="text" class="form-control bg-light" id="province" name="province" value="<?= val('province') ?>" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary small">รหัสไปรษณีย์</label>
                            <input type="text" class="form-control bg-light" id="zipcode" name="zipcode" value="<?= val('zipcode') ?>" readonly>
                        </div>
                    </div>
                </div>

                <hr class="text-muted opacity-25 my-4">

                <div class="p-4 rounded-4 border border-2 mb-4" style="background-color: #fcfaff; border-color: #f3e5f5 !important;">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="card_type" class="form-label fw-bold text-secondary">
                                <i class="bi bi-card-heading me-1 text-primary"></i> ประเภทบัตรอื่นๆ
                            </label>
                            <select name="card_type" id="card_type" class="form-select">
                                <option value="" <?= sel('other_id_type', '') ?>>-- ไม่ระบุ --</option>
                                <option value="Alien" <?= sel('other_id_type', 'Alien') ?>>ต่างด้าว</option>
                                <option value="Passport" <?= sel('other_id_type', 'Passport') ?>>Passport</option>
                            </select>
                        </div>

                        <div class="col-md-8 details-box" id="alien-details" style="<?= val('other_id_type') == 'Alien' ? 'display:block;' : 'display: none;' ?>">
                            <label for="alien_number" class="form-label text-muted">เลขที่บัตรต่างด้าว</label>
                            <input type="text" id="alien_number" name="alien_number" class="form-control" value="<?= val('other_id_number') ?>" placeholder="ระบุเลขที่บัตร...">
                        </div>

                        <div class="col-md-8 details-box" id="passport-details" style="<?= val('other_id_type') == 'Passport' ? 'display:block;' : 'display: none;' ?>">
                            <label for="passport_number" class="form-label text-muted">เลขที่ Passport</label>
                            <input type="text" id="passport_number" name="passport_number" class="form-control" value="<?= val('other_id_number') ?>" placeholder="ระบุเลข Passport...">
                        </div>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold mb-3 text-secondary">
                        <i class="bi bi-card-checklist me-2 text-primary"></i> สิทธิการรักษา (Treatment Scheme)
                    </label>

                    <div class="d-flex flex-wrap gap-3">
                        <input type="radio" class="btn-check" name="btnTreatmentRight" id="tr_insurance" value="health_insurance" <?= chk('treatment_scheme', 'health_insurance') ?>>
                        <label class="treatment-option shadow-sm" for="tr_insurance">
                            <i class="bi bi-shield-check-fill"></i>
                            <span>ประกันสุขภาพ</span>
                        </label>

                        <input type="radio" class="btn-check" name="btnTreatmentRight" id="tr_sso" value="social_security" <?= chk('treatment_scheme', 'social_security') ?>>
                        <label class="treatment-option shadow-sm" for="tr_sso">
                            <i class="bi bi-people-fill"></i>
                            <span>ประกันสังคม</span>
                        </label>

                        <input type="radio" class="btn-check" name="btnTreatmentRight" id="tr_affiliation" value="affiliation" <?= chk('treatment_scheme', 'affiliation') ?>>
                        <label class="treatment-option shadow-sm" for="tr_affiliation">
                            <i class="bi bi-building-fill"></i>
                            <span>ต้นสังกัด</span>
                        </label>

                        <input type="radio" class="btn-check" name="btnTreatmentRight" id="tr_self" value="self_pay" <?= chk('treatment_scheme', 'self_pay') ?>>
                        <label class="treatment-option shadow-sm" for="tr_self">
                            <i class="bi bi-cash-coin"></i>
                            <span>จ่ายเงินเอง</span>
                        </label>

                        <input type="radio" class="btn-check" name="btnTreatmentRight" id="tr_t99" value="t99" <?= chk('treatment_scheme', 't99') ?>>
                        <label class="treatment-option shadow-sm" for="tr_t99">
                            <i class="bi bi-hospital-fill"></i>
                            <span>ท.99</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="card-form">
                <div class="row g-4">
                    <div class="col-md-7 border-end pe-md-4">
                        <h6 class="section-title mb-3">
                            <i class="bi bi-heart-pulse-fill text-primary"></i> 3. โรคประจำตัว (Comorbidities)
                        </h6>

                        <div class="d-flex flex-wrap gap-2">
                            <input class="btn-check" type="checkbox" name="comorbid_ht" value="1" id="comorbid_ht" <?= chk('is_ht') ?>>
                            <label class="selection-chip chip-disease" for="comorbid_ht"><i class="bi bi-activity"></i> HT</label>

                            <input class="btn-check" type="checkbox" name="comorbid_dm" value="1" id="comorbid_dm" <?= chk('is_dm') ?>>
                            <label class="selection-chip chip-disease" for="comorbid_dm"><i class="bi bi-droplet-fill"></i> DM</label>

                            <input class="btn-check" type="checkbox" name="old_cva" value="1" id="old_cva" <?= chk('is_old_cva') ?>>
                            <label class="selection-chip chip-disease" for="old_cva"><i class="bi bi-person-wheelchair"></i> Old CVA</label>

                            <input class="btn-check" type="checkbox" name="comorbid_mi" value="1" id="comorbid_mi" <?= chk('is_mi') ?>>
                            <label class="selection-chip chip-disease" for="comorbid_mi"><i class="bi bi-heart-break-fill"></i> MI</label>

                            <input class="btn-check" type="checkbox" name="comorbid_af" value="1" id="comorbid_af" <?= chk('is_af') ?>>
                            <label class="selection-chip chip-disease" for="comorbid_af"><i class="bi bi-graph-up-arrow"></i> AF</label>

                            <input class="btn-check" type="checkbox" name="comorbid_dlp" value="1" id="comorbid_dlp" <?= chk('is_dlp') ?>>
                            <label class="selection-chip chip-disease" for="comorbid_dlp"><i class="bi bi-layers-fill"></i> DLP</label>

                            <input class="btn-check" type="checkbox" name="comorbid_other_check" value="1" id="comorbid_other_check" <?= !empty($row['is_other_text']) ? 'checked' : '' ?>>
                            <label class="selection-chip chip-disease" for="comorbid_other_check"><i class="bi bi-plus-circle-fill"></i> Other</label>
                        </div>

                        <div class="mt-3" id="other_disease_input" style="<?= !empty($row['is_other_text']) ? 'display:block;' : 'display: none;' ?>">
                            <input type="text" class="form-control" name="comorbid_other_text" id="comorbid_other_text"
                                placeholder="ระบุโรคประจำตัวอื่นๆ..." value="<?= val('is_other_text') ?>">
                        </div>
                    </div>

                    <div class="col-md-5 ps-md-4">
                        <h6 class="section-title mb-3">
                            <i class="bi bi-exclamation-triangle-fill text-danger"></i> 4. สารเสพติด (Addictions)
                        </h6>

                        <div class="d-flex flex-wrap gap-2">
                            <input class="btn-check" type="checkbox" name="addict_alcohol" value="1" id="comorbid_alcohol" <?= chk('addict_alcohol') ?>>
                            <label class="selection-chip chip-addict" for="comorbid_alcohol"><i class="bi bi-cup-straw"></i> Alcohol</label>

                            <input class="btn-check" type="checkbox" name="addict_smoking" value="1" id="comorbid_smoking" <?= chk('addict_smoking') ?>>
                            <label class="selection-chip chip-addict" for="comorbid_smoking"><i class="bi bi-wind"></i> Smoking</label>

                            <input class="btn-check" type="checkbox" name="comorbid_kratom" value="1" id="comorbid_kratom" <?= chk('comorbid_kratom') ?>>
                            <label class="selection-chip chip-addict" for="comorbid_kratom"><i class="bi bi-flower1"></i> กระท่อม</label>

                            <input class="btn-check" type="checkbox" name="comorbid_cannabis" value="1" id="comorbid_cannabis" <?= chk('comorbid_cannabis') ?>>
                            <label class="selection-chip chip-addict" for="comorbid_cannabis"><i class="bi bi-flower3"></i> กัญชา</label>

                            <input class="btn-check" type="checkbox" name="comorbid_crystal_meth" value="1" id="comorbid_crystal_meth" <?= chk('comorbid_crystal_meth') ?>>
                            <label class="selection-chip chip-addict" for="comorbid_crystal_meth"><i class="bi bi-snow"></i> ไอซ์</label>

                            <input class="btn-check" type="checkbox" name="comorbid_yaba" value="1" id="comorbid_yaba" <?= chk('comorbid_yaba') ?>>
                            <label class="selection-chip chip-addict" for="comorbid_yaba"><i class="bi bi-capsule"></i> ยาบ้า</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-form">
                <div class="section-title" style="margin-top:0;">
                    <i class="bi bi-door-open"></i> 5. ประเภทการมาของคนไข้ (Arrival Type)
                </div>
                <div class="row">
                    <div class="col-12 mb-4">
                        <label class="form-label fw-bold mb-3 text-secondary">
                            <i class="bi bi-geo-alt-fill me-2"></i> ช่องทางการมา (Arrival Type)
                        </label>

                        <div class="d-flex flex-wrap gap-3">
                            <input type="radio" class="btn-check arrival-trigger" name="arrival_type" id="arrival_refer" value="refer" <?= chk('arrival_type', 'refer') ?>>
                            <label class="arrival-option shadow-sm" for="arrival_refer">
                                <i class="bi bi-ambulance"></i>
                                <span>Refer (ส่งต่อ)</span>
                            </label>

                            <input type="radio" class="btn-check arrival-trigger" name="arrival_type" id="arrival_ems" value="ems" <?= chk('arrival_type', 'ems') ?>>
                            <label class="arrival-option shadow-sm" for="arrival_ems">
                                <i class="bi bi-telephone-plus"></i>
                                <span>EMS (1669)</span>
                            </label>

                            <input type="radio" class="btn-check arrival-trigger" name="arrival_type" id="arrival_walk_in" value="walk_in" <?= chk('arrival_type', 'walk_in') ?>>
                            <label class="arrival-option shadow-sm" for="arrival_walk_in">
                                <i class="bi bi-person-walking"></i>
                                <span>Walk-in (มาเอง)</span>
                            </label>

                            <input type="radio" class="btn-check arrival-trigger" name="arrival_type" id="arrival_ipd" value="ipd" <?= chk('arrival_type', 'ipd') ?>>
                            <label class="arrival-option shadow-sm" for="arrival_ipd">
                                <i class="bi bi-hospital"></i>
                                <span>IPD (ย้ายตึก)</span>
                            </label>
                        </div>
                    </div>

                    <div class="col-md-12 arrival-field p-3 bg-light rounded border" id="refer_from_field" style="<?= chk('arrival_type', 'refer') ? 'display:block' : 'display:none' ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="refer_from_text" class="form-label">Refer จาก (ระบุโรงพยาบาล):</label>
                                <select name="refer_from_text" type="text" class="form-select" id="refer_from_text">
                                    <option value="">-- เลือกโรงพยาบาลต้นทาง --</option>
                                    <optgroup label="ระดับ A/S (โรงพยาบาลศูนย์/ทั่วไป)">
                                        <option value="รพศ.หาดใหญ่" <?= sel('refer_from_hospital', 'รพศ.หาดใหญ่') ?>>รพศ.หาดใหญ่ (สงขลา)</option>
                                        <option value="รพศ.ตรัง" <?= sel('refer_from_hospital', 'รพศ.ตรัง') ?>>รพศ.ตรัง (ตรัง)</option>
                                        <option value="รพท.พัทลุง" <?= sel('refer_from_hospital', 'รพท.พัทลุง') ?>>รพท.พัทลุง (พัทลุง)</option>
                                        <option value="รพท.สงขลา" <?= sel('refer_from_hospital', 'รพท.สงขลา') ?>>รพท.สงขลา (สงขลา)</option>
                                        <option value="รพท.ปัตตานี" <?= sel('refer_from_hospital', 'รพท.ปัตตานี') ?>>รพท.ปัตตานี (ปัตตานี)</option>
                                        <option value="รพศ.ยะลา" <?= sel('refer_from_hospital', 'รพศ.ยะลา') ?>>รพศ.ยะลา (ยะลา)</option>
                                        <option value="รพศ.นราธิวาสราชนครินทร์" <?= sel('refer_from_hospital', 'รพศ.นราธิวาสราชนครินทร์') ?>>รพศ.นราธิวาสราชนครินทร์ (นราธิวาส)</option>
                                        <option value="รพท.สตูล" <?= sel('refer_from_hospital', 'รพท.สตูล') ?>>รพท.สตูล (สตูล)</option>
                                        <option value="รพท.สุไหงโก-ลก" <?= sel('refer_from_hospital', 'รพท.สุไหงโก-ลก') ?>>รพท.สุไหงโก-ลก (นราธิวาส)</option>
                                        <option value="รพท.เบตง" <?= sel('refer_from_hospital', 'รพท.เบตง') ?>>รพท.เบตง (ยะลา)</option>
                                    </optgroup>
                                    <optgroup label="ระดับ M/F (โรงพยาบาลชุมชน)">
                                        <option value="รพช.ห้วยยอด" <?= sel('refer_from_hospital', 'รพช.ห้วยยอด') ?>>รพช.ห้วยยอด (ตรัง)</option>
                                        <option value="รพช.ย่านตาขาว" <?= sel('refer_from_hospital', 'รพช.ย่านตาขาว') ?>>รพช.ย่านตาขาว (ตรัง)</option>
                                        <option value="รพช.ควนขนุน" <?= sel('refer_from_hospital', 'รพช.ควนขนุน') ?>>รพช.ควนขนุน (พัทลุง)</option>
                                        <option value="รพช.ละงู" <?= sel('refer_from_hospital', 'รพช.ละงู') ?>>รพช.ละงู (สตูล)</option>
                                        <option value="รพช.สมเด็จพระบรมราชินีนาถ ณ อ.นาทวี" <?= sel('refer_from_hospital', 'รพช.สมเด็จพระบรมราชินีนาถ ณ อ.นาทวี') ?>>รพช.สมเด็จพระบรมราชินีนาถ ณ อ.นาทวี (สงขลา)</option>
                                        <option value="รพช.สะเดา" <?= sel('refer_from_hospital', 'รพช.สะเดา') ?>>รพช.สะเดา (สงขลา)</option>
                                        <option value="รพช.ระโนด" <?= sel('refer_from_hospital', 'รพช.ระโนด') ?>>รพช.ระโนด (สงขลา)</option>
                                        <option value="รพช.จะนะ" <?= sel('refer_from_hospital', 'รพช.จะนะ') ?>>รพช.จะนะ (สงขลา)</option>
                                        <option value="รพช.เทพา" <?= sel('refer_from_hospital', 'รพช.เทพา') ?>>รพช.เทพา (สงขลา)</option>
                                        <option value="รพช.สายบุรี" <?= sel('refer_from_hospital', 'รพช.สายบุรี') ?>>รพช.สายบุรี (ปัตตานี)</option>
                                        <option value="รพช.โคกโพธิ์" <?= sel('refer_from_hospital', 'รพช.โคกโพธิ์') ?>>รพช.โคกโพธิ์ (ปัตตานี)</option>
                                        <option value="รพช.ยะหา" <?= sel('refer_from_hospital', 'รพช.ยะหา') ?>>รพช.ยะหา (ยะลา)</option>
                                        <option value="รพช.รามัน" <?= sel('refer_from_hospital', 'รพช.รามัน') ?>>รพช.รามัน (ยะลา)</option>
                                        <option value="รพช.ระแงะ" <?= sel('refer_from_hospital', 'รพช.ระแงะ') ?>>รพช.ระแงะ (นราธิวาส)</option>
                                        <option value="รพช.ตากใบ" <?= sel('refer_from_hospital', 'รพช.ตากใบ') ?>>รพช.ตากใบ (นราธิวาส)</option>
                                        <option value="รพช.กันตัง" <?= sel('refer_from_hospital', 'รพช.กันตัง') ?>>รพช.กันตัง (ตรัง)</option>
                                        <option value="รพช.ปะเหลียน" <?= sel('refer_from_hospital', 'รพช.ปะเหลียน') ?>>รพช.ปะเหลียน (ตรัง)</option>
                                        <option value="รพช.นาโยง" <?= sel('refer_from_hospital', 'รพช.นาโยง') ?>>รพช.นาโยง (ตรัง)</option>
                                        <option value="รพช.สิเกา" <?= sel('refer_from_hospital', 'รพช.สิเกา') ?>>รพช.สิเกา (ตรัง)</option>
                                        <option value="รพช.รัษฎา" <?= sel('refer_from_hospital', 'รพช.รัษฎา') ?>>รพช.รัษฎา (ตรัง)</option>
                                        <option value="รพช.วังวิเศษ" <?= sel('refer_from_hospital', 'รพช.วังวิเศษ') ?>>รพช.วังวิเศษ (ตรัง)</option>
                                        <option value="รพช.หาดสำราญเฉลิมพระเกียรติ" <?= sel('refer_from_hospital', 'รพช.หาดสำราญเฉลิมพระเกียรติ') ?>>รพช.หาดสำราญเฉลิมพระเกียรติ (ตรัง)</option>
                                        <option value="รพช.ตะโหมด" <?= sel('refer_from_hospital', 'รพช.ตะโหมด') ?>>รพช.ตะโหมด (พัทลุง)</option>
                                        <option value="รพช.กงหรา" <?= sel('refer_from_hospital', 'รพช.กงหรา') ?>>รพช.กงหรา (พัทลุง)</option>
                                        <option value="รพช.เขาชัยสน" <?= sel('refer_from_hospital', 'รพช.เขาชัยสน') ?>>รพช.เขาชัยสน (พัทลุง)</option>
                                        <option value="รพช.บางแก้ว" <?= sel('refer_from_hospital', 'รพช.บางแก้ว') ?>>รพช.บางแก้ว (พัทลุง)</option>
                                        <option value="รพช.ปากพะยูน" <?= sel('refer_from_hospital', 'รพช.ปากพะยูน') ?>>รพช.ปากพะยูน (พัทลุง)</option>
                                        <option value="รพช.ป่าบอน" <?= sel('refer_from_hospital', 'รพช.ป่าบอน') ?>>รพช.ป่าบอน (พัทลุง)</option>
                                        <option value="รพช.ป่าพะยอม" <?= sel('refer_from_hospital', 'รพช.ป่าพะยอม') ?>>รพช.ป่าพะยอม (พัทลุง)</option>
                                        <option value="รพช.ศรีบรรพต" <?= sel('refer_from_hospital', 'รพช.ศรีบรรพต') ?>>รพช.ศรีบรรพต (พัทลุง)</option>
                                        <option value="รพช.ศรีนครินทร์" <?= sel('refer_from_hospital', 'รพช.ศรีนครินทร์') ?>>รพช.ศรีนครินทร์ (พัทลุง)</option>
                                        <option value="รพช.ควนกาหลง" <?= sel('refer_from_hospital', 'รพช.ควนกาหลง') ?>>รพช.ควนกาหลง (สตูล)</option>
                                        <option value="รพช.ควนโดน" <?= sel('refer_from_hospital', 'รพช.ควนโดน') ?>>รพช.ควนโดน (สตูล)</option>
                                        <option value="รพช.ทุ่งหว้า" <?= sel('refer_from_hospital', 'รพช.ทุ่งหว้า') ?>>รพช.ทุ่งหว้า (สตูล)</option>
                                        <option value="รพช.ท่าแพ" <?= sel('refer_from_hospital', 'รพช.ท่าแพ') ?>>รพช.ท่าแพ (สตูล)</option>
                                        <option value="รพช.มะนัง" <?= sel('refer_from_hospital', 'รพช.มะนัง') ?>>รพช.มะนัง (สตูล)</option>
                                        <option value="รพช.สิงหนคร" <?= sel('refer_from_hospital', 'รพช.สิงหนคร') ?>>รพช.สิงหนคร (สงขลา)</option>
                                        <option value="รพช.สทิงพระ" <?= sel('refer_from_hospital', 'รพช.สทิงพระ') ?>>รพช.สทิงพระ (สงขลา)</option>
                                        <option value="รพช.สะบ้าย้อย" <?= sel('refer_from_hospital', 'รพช.สะบ้าย้อย') ?>>รพช.สะบ้าย้อย (สงขลา)</option>
                                        <option value="รพช.คลองหอยโข่ง" <?= sel('refer_from_hospital', 'รพช.คลองหอยโข่ง') ?>>รพช.คลองหอยโข่ง (สงขลา)</option>
                                        <option value="รพช.ควนเนียง" <?= sel('refer_from_hospital', 'รพช.ควนเนียง') ?>>รพช.ควนเนียง (สงขลา)</option>
                                        <option value="รพช.นาหม่อม" <?= sel('refer_from_hospital', 'รพช.นาหม่อม') ?>>รพช.นาหม่อม (สงขลา)</option>
                                        <option value="รพช.บางกล่ำ" <?= sel('refer_from_hospital', 'รพช.บางกล่ำ') ?>>รพช.บางกล่ำ (สงขลา)</option>
                                        <option value="รพช.รัตภูมิ" <?= sel('refer_from_hospital', 'รพช.รัตภูมิ') ?>>รพช.รัตภูมิ (สงขลา)</option>
                                        <option value="รพช.ปาดังเบซาร์" <?= sel('refer_from_hospital', 'รพช.ปาดังเบซาร์') ?>>รพช.ปาดังเบซาร์ (สงขลา)</option>
                                        <option value="รพช.กระแสสินธุ์" <?= sel('refer_from_hospital', 'รพช.กระแสสินธุ์') ?>>รพช.กระแสสินธุ์ (สงขลา)</option>
                                        <option value="รพช.กะพ้อ" <?= sel('refer_from_hospital', 'รพช.กะพ้อ') ?>>รพช.กะพ้อ (ปัตตานี)</option>
                                        <option value="รพช.ทุ่งยางแดง" <?= sel('refer_from_hospital', 'รพช.ทุ่งยางแดง') ?>>รพช.ทุ่งยางแดง (ปัตตานี)</option>
                                        <option value="รพช.แม่ลาน" <?= sel('refer_from_hospital', 'รพช.แม่ลาน') ?>>รพช.แม่ลาน (ปัตตานี)</option>
                                        <option value="รพช.ไม้แก่น" <?= sel('refer_from_hospital', 'รพช.ไม้แก่น') ?>>รพช.ไม้แก่น (ปัตตานี)</option>
                                        <option value="รพช.มายอ" <?= sel('refer_from_hospital', 'รพช.มายอ') ?>>รพช.มายอ (ปัตตานี)</option>
                                        <option value="รพช.ปานาเระ" <?= sel('refer_from_hospital', 'รพช.ปานาเระ') ?>>รพช.ปานาเระ (ปัตตานี)</option>
                                        <option value="รพช.ยะรัง" <?= sel('refer_from_hospital', 'รพช.ยะรัง') ?>>รพช.ยะรัง (ปัตตานี)</option>
                                        <option value="รพช.ยะหริ่ง" <?= sel('refer_from_hospital', 'รพช.ยะหริ่ง') ?>>รพช.ยะหริ่ง (ปัตตานี)</option>
                                        <option value="รพช.หนองจิก" <?= sel('refer_from_hospital', 'รพช.หนองจิก') ?>>รพช.หนองจิก (ปัตตานี)</option>
                                        <option value="รพช.กาบัง" <?= sel('refer_from_hospital', 'รพช.กาบัง') ?>>รพช.กาบัง (ยะลา)</option>
                                        <option value="รพช.กรงปินัง" <?= sel('refer_from_hospital', 'รพช.กรงปินัง') ?>>รพช.กรงปินัง (ยะลา)</option>
                                        <option value="รพช.ธารโต" <?= sel('refer_from_hospital', 'รพช.ธารโต') ?>>รพช.ธารโต (ยะลา)</option>
                                        <option value="รพช.บันนังสตา" <?= sel('refer_from_hospital', 'รพช.บันนังสตา') ?>>รพช.บันนังสตา (ยะลา)</option>
                                        <option value="รพช.ยี่งอ" <?= sel('refer_from_hospital', 'รพช.ยี่งอ') ?>>รพช.ยี่งอ (นราธิวาส)</option>
                                        <option value="รพช.รือเสาะ" <?= sel('refer_from_hospital', 'รพช.รือเสาะ') ?>>รพช.รือเสาะ (นราธิวาส)</option>
                                        <option value="รพช.จะแนะ" <?= sel('refer_from_hospital', 'รพช.จะแนะ') ?>>รพช.จะแนะ (นราธิวาส)</option>
                                        <option value="รพช.เจาะไอร้อง" <?= sel('refer_from_hospital', 'รพช.เจาะไอร้อง') ?>>รพช.เจาะไอร้อง (นราธิวาส)</option>
                                        <option value="รพช.แว้ง" <?= sel('refer_from_hospital', 'รพช.แว้ง') ?>>รพช.แว้ง (นราธิวาส)</option>
                                        <option value="รพช.สุคิริน" <?= sel('refer_from_hospital', 'รพช.สุคิริน') ?>>รพช.สุคิริน (นราธิวาส)</option>
                                        <option value="รพช.สุไหงปาดี" <?= sel('refer_from_hospital', 'รพช.สุไหงปาดี') ?>>รพช.สุไหงปาดี (นราธิวาส)</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="arrivalTime" class="form-label">วันที่ส่งต่อ (Departure Date)</label>
                                <input type="date" class="form-control" id="arrivalTime" name="arrivalTime" value="<?= dt('transfer_departure_datetime', 'd') ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="refer_departure_time" class="form-label">เวลาที่รถออก (Time)</label>
                                <input type="text" class="form-control timepicker" id="refer_departure_time" name="refer_departure_time" value="<?= dt('transfer_departure_datetime', 't') ?>" readonly>
                            </div>
                            <div class="col-md-3">
                                <label for="refer_arrival_date" class="form-label ">วันที่ที่ผป.มาถึง</label>
                                <input type="date" class="form-control" name="refer_arrival_date" value="<?= dt('refer_arrival_datetime', 'd') ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="refer_arrival_time" class="form-label">เวลา</label>
                                <input type="text" class="form-control timepicker" name="refer_arrival_time" value="<?= dt('refer_arrival_datetime', 't') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 arrival-field p-3 bg-light rounded border" id="ems_time_field" style="<?= chk('arrival_type', 'ems') ? 'display:block' : 'display:none' ?>">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="first_medical_contact" class="form-label">First Medical contact (Date)</label>
                                <input type="date" class="form-control" id="first_medical_contact" name="first_medical_contact_date" value="<?= dt('ems_first_medical_contact', 'd') ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="first_medical_contact_time" class="form-label">Time</label>
                                <input type="text" class="form-control timepicker" name="first_medical_contact_time" value="<?= dt('ems_first_medical_contact', 't') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 arrival-field p-3 bg-light rounded border" id="walkin_time_field" style="<?= chk('arrival_type', 'walk_in') ? 'display:block' : 'display:none' ?>">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="er_arrival_date" class="form-label">เวลาที่มาถึงห้องฉุกเฉิน (Date)</label>
                                <input type="date" class="form-control" id="er_arrival_date" name="er_arrival_date" value="<?= dt('walk_in_datetime', 'd') ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="er_arrival_time" class="form-label">Time</label>
                                <input type="text" class="form-control timepicker" value="<?= dt('walk_in_datetime', 't') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 arrival-field p-3 bg-light rounded border" id="ipd_time_field" style="<?= chk('arrival_type', 'ipd') ? 'display:block' : 'display:none' ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="ipd_ward" class="form-label">หอผู้ป่วย</label>
                                <select name="ipd_ward" id="ipd_ward" class="form-select">
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
                            <div class="col-md-3">
                                <label class="form-label">เวลาเริ่มมีอาการในหอผู้ป่วย</label>
                                <input type="date" class="form-control" id="ipd_onset_date" name="ipd_onset_date" value="<?= dt('ipd_onset_datetime', 'd') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Time</label>
                                <input type="text" class="form-control timepicker" id="timepicker" name="ipd_onset_time" value="<?= dt('ipd_onset_datetime', 't') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-form">
                <div class="row g-4">
                    <div class="col-md-7 border-end pe-md-4">
                        <h6 class="section-title mb-3">
                            <i class="bi bi-capsule-pill text-success"></i> 6. ยาประจำตัว (Regular Medication)
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="med-group-card">
                                    <input class="btn-check med-check-main" type="checkbox" id="med_anti_platelet" name="med_anti_platelet" value="1" <?= chk('med_anti_platelet') ?>>
                                    <label class="med-group-header" for="med_anti_platelet">
                                        <span><i class="bi bi-shield-check me-2"></i> Anti-platelet</span>
                                        <i class="bi bi-check-circle-fill"></i>
                                    </label>

                                    <div class="d-flex flex-column gap-1">
                                        <div class="form-check med-sub-item">
                                            <input class="form-check-input" type="checkbox" id="med_asa" name="med_asa" value="1" <?= chk('med_asa') ?>>
                                            <label class="form-check-label" for="med_asa">ASA (Aspirin)</label>
                                        </div>
                                        <div class="form-check med-sub-item">
                                            <input class="form-check-input" type="checkbox" id="med_clopidogrel" name="med_clopidogrel" value="1" <?= chk('med_clopidogrel') ?>>
                                            <label class="form-check-label" for="med_clopidogrel">Clopidogrel</label>
                                        </div>
                                        <div class="form-check med-sub-item">
                                            <input class="form-check-input" type="checkbox" id="med_cilostazol" name="med_cilostazol" value="1" <?= chk('med_cilostazol') ?>>
                                            <label class="form-check-label" for="med_cilostazol">Cilostazol</label>
                                        </div>
                                        <div class="form-check med-sub-item">
                                            <input class="form-check-input" type="checkbox" id="med_ticaqrelor" name="med_ticaqrelor" value="1" <?= chk('med_ticaqrelor') ?>>
                                            <label class="form-check-label" for="med_ticaqrelor">Ticagrelor</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="med-group-card">
                                    <input class="btn-check med-check-main" type="checkbox" id="med_anti_coagulant" name="med_anti_coagulant" value="1" <?= chk('med_anti_coagulant') ?>>
                                    <label class="med-group-header" for="med_anti_coagulant">
                                        <span><i class="bi bi-droplet-half me-2"></i> Anti-coagulant</span>
                                        <i class="bi bi-check-circle-fill"></i>
                                    </label>

                                    <div class="d-flex flex-column gap-1">
                                        <div class="form-check med-sub-item">
                                            <input class="form-check-input" type="checkbox" id="med_warfarin" name="med_warfarin" value="1" <?= chk('med_warfarin') ?>>
                                            <label class="form-check-label" for="med_warfarin">Warfarin</label>
                                        </div>
                                        <div class="form-check med-sub-item">
                                            <input class="form-check-input" type="checkbox" id="med_noac" name="med_noac" value="1" <?= chk('med_noac') ?>>
                                            <label class="form-check-label" for="med_noac">NOAC</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5 ps-md-4">
                        <h6 class="section-title mb-3">
                            <i class="bi bi-star-fill text-warning"></i> 7. MRS Score (Pre-morbid)
                        </h6>

                        <div class="p-3 bg-light rounded-4 border border-2 border-white shadow-sm text-center">
                            <label class="form-label mb-3 text-muted fw-bold">เลือกคะแนนความพิการก่อนหน้า (0-6)</label>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <?php for ($i = 0; $i <= 6; $i++): ?>
                                    <input type="radio" class="btn-check mrs-<?= $i ?>" name="mrs_score" id="mrs_<?= $i ?>" value="<?= $i ?>" required <?= chk('pre_morbid_mrs', $i) ?>>
                                    <label class="mrs-option" for="mrs_<?= $i ?>" title="Score <?= $i ?>">
                                        <?= $i ?>
                                    </label>
                                <?php endfor; ?>
                            </div>
                            <div class="mt-2 small text-muted">
                                <span class="badge bg-success bg-opacity-25 text-success me-1">0 = ปกติ</span>
                                <span class="badge bg-danger bg-opacity-25 text-danger">6 = เสียชีวิต</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-form card-alert" style="border-left: 5px solid #d32f2f;">
                <div class="section-title text-danger" style="margin-top:0;">
                    <i class="bi bi-card-checklist"></i> 8. ประเมินแรกรับ (Triage Assessment)
                </div>
                <div class="col-md-5 mb-3">
                    <div class="input-box-card danger">
                        <label class="form-label fw-bold mb-2 text-uppercase small">
                            <i class="bi bi-activity me-2"></i> เวลาที่เริ่มมีอาการ (Onset)
                        </label>

                        <div class="input-group">
                            <span class="input-group-text rounded-start-4 ps-3">
                                <i class="bi bi-calendar2-week"></i>
                            </span>
                            <input type="date" class="form-control" name="onsetTime_onset_date"
                                value="<?= dt('onset_datetime', 'd') ?>"
                                style="border-right: 0;">

                            <span class="input-group-text" style="border-left: 0; border-right: 0;">
                                <i class="bi bi-clock"></i>
                            </span>
                            <input type="text" class="form-control timepicker rounded-end-4 text-center fw-bold text-danger"
                                name="onsetTime_onset_time"
                                value="<?= dt('onset_datetime', 't') ?>"
                                placeholder="--:--" style="border-left: 0;">
                        </div>
                    </div>
                </div>


                <div class="p-3 mb-4 bg-white rounded border border-danger border-opacity-25 shadow-sm">
                    <p class="fs-5 fw-bold text-danger mb-2">ผู้ป่วยเข้าเกณฑ์ Stroke Fast Track หรือไม่?</p>
                    <div class="col-12 mb-4">
                        <label class="form-label fw-bold mb-3 text-secondary">
                            <i class="bi bi-lightning-charge-fill me-2 text-warning"></i> Fast Track Status
                        </label>

                        <div class="d-flex align-items-center gap-3">
                            <input type="radio" class="btn-check" name="fast_track_status" id="fast_track_yes" value="yes" required <?= chk('fast_track_status', 'yes') ?>>
                            <label class="fast-track-option option-yes shadow-sm w-100" for="fast_track_yes">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>YES</span>
                            </label>

                            <input type="radio" class="btn-check" name="fast_track_status" id="fast_track_no" value="no" required <?= chk('fast_track_status', 'no') ?>>
                            <label class="fast-track-option option-no shadow-sm w-100" for="fast_track_no">
                                <i class="bi bi-x-circle-fill"></i>
                                <span>NO</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label fw-bold mb-3 text-secondary">
                            <i class="bi bi-activity me-2 text-danger"></i> อาการสำคัญ (Symptoms - F.A.S.T.)
                        </label>

                        <div class="d-flex flex-wrap gap-3">
                            <input class="btn-check" type="checkbox" id="sympDroop" value="1" name="sympDroop" <?= chk('symp_face') ?>>
                            <label class="symptom-card shadow-sm" for="sympDroop">
                                <i class="bi bi-emoji-dizzy"></i>
                                <span>Face</span>
                            </label>

                            <input class="btn-check" type="checkbox" id="sympWeakness" value="1" name="sympWeakness" <?= chk('symp_arm') ?>>
                            <label class="symptom-card shadow-sm" for="sympWeakness">
                                <i class="bi bi-person-arms-up"></i>
                                <span>Arm</span>
                            </label>

                            <input class="btn-check" type="checkbox" id="sympSpeech" value="1" name="sympSpeech" <?= chk('symp_speech') ?>>
                            <label class="symptom-card shadow-sm" for="sympSpeech">
                                <i class="bi bi-chat-dots-fill"></i>
                                <span>Speech</span>
                            </label>

                            <input class="btn-check" type="checkbox" id="symp_v" name="symp_v" value="1" <?= chk('symp_vision') ?>>
                            <label class="symptom-card shadow-sm" for="symp_v">
                                <i class="bi bi-eye-fill"></i>
                                <span>Vision</span>
                            </label>

                            <input class="btn-check" type="checkbox" id="symp_a2" name="symp_a2" value="1" <?= chk('symp_aphasia') ?>>
                            <label class="symptom-card shadow-sm" for="symp_a2">
                                <i class="bi bi-translate"></i>
                                <span>Aphasia</span>
                            </label>

                            <input class="btn-check" type="checkbox" id="symp_n" name="symp_n" value="1" <?= chk('symp_neglect') ?>>
                            <label class="symptom-card shadow-sm" for="symp_n">
                                <i class="bi bi-person-x-fill"></i>
                                <span>Neglect</span>
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <hr class="my-0">
                    </div>

                    <div class="col-md-7">
                        <div class="card-form h-100 mb-0">
                            <h6 class="section-title mb-4"><i class="bi bi-calculator"></i> คะแนนประเมิน (Scores)</h6>

                            <div class="row g-4">
                                <div class="col-md-8 border-end pe-4">
                                    <label class="form-label fw-bold mb-3 text-secondary">GCS (Glasgow Coma Scale)</label>

                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="gcs-label" title="Eye Opening">E</div>
                                        <div class="d-flex flex-wrap">
                                            <?php for ($i = 1; $i <= 4; $i++) echo "<input type='radio' class='btn-check' name='gcs_e' id='gcs_e$i' value='$i' " . chk('gcs_e', $i) . "><label class='btn btn-outline-secondary btn-gcs' for='gcs_e$i'>$i</label>"; ?>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="gcs-label" title="Verbal Response">V</div>
                                        <div class="d-flex flex-wrap">
                                            <?php for ($i = 1; $i <= 5; $i++) echo "<input type='radio' class='btn-check' name='gcs_v' id='gcs_v$i' value='$i' " . chk('gcs_v', $i) . "><label class='btn btn-outline-secondary btn-gcs' for='gcs_v$i'>$i</label>"; ?>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-3">
                                        <div class="gcs-label" title="Motor Response">M</div>
                                        <div class="d-flex flex-wrap">
                                            <?php for ($i = 1; $i <= 6; $i++) echo "<input type='radio' class='btn-check' name='gcs_m' id='gcs_m$i' value='$i' " . chk('gcs_m', $i) . "><label class='btn btn-outline-secondary btn-gcs' for='gcs_m$i'>$i</label>"; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 d-flex flex-column justify-content-center align-items-center">
                                    <label for="nihss" class="form-label fw-bold text-primary mb-2" style="font-size: 1.1rem;">NIHSS Score</label>
                                    <div class="position-relative w-100">
                                        <input type="number" class="form-control input-nihss text-center" id="nihss" placeholder="0" min="0" max="42" oninput="if(parseInt(this.value) > 42) this.value = 42; if(parseInt(this.value) < 0) this.value = 0;" name="nihss" required value="<?= val('nihss_score') ?>">
                                        <small class="text-muted position-absolute start-50 translate-middle-x" style="bottom: -25px;">(Max 42)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <h6 class="form-label fw-bold mb-3 text-secondary">
                            <i class="bi bi-clock-history me-2 text-primary"></i> เวลา (Time)
                        </h6>

                        <div class="input-box-card">
                            <label class="form-label fw-bold text-muted small mb-2 text-uppercase">
                                Hatyai Hospital Arrival (เวลาถึง รพ.)
                            </label>

                            <div class="input-group">
                                <span class="input-group-text rounded-start-4 ps-3">
                                    <i class="bi bi-calendar-event"></i>
                                </span>
                                <input type="date" class="form-control" name="arrivalTime_date"
                                    value="<?= dt('hospital_arrival_datetime', 'd') ?>"
                                    style="border-right: 0;">

                                <span class="input-group-text" style="border-left: 0; border-right: 0;">
                                    <i class="bi bi-clock"></i>
                                </span>
                                <input type="text" class="form-control timepicker rounded-end-4 text-center fw-bold text-primary"
                                    name="arrivalTime_time"
                                    value="<?= dt('hospital_arrival_datetime', 't') ?>"
                                    placeholder="--:--" style="border-left: 0;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-form bg-light border-primary border-opacity-25">
                <div class="section-title text-primary" style="margin-top:0;">
                    <i class="bi bi-stopwatch"></i> 9. สรุป Time Process of Care
                </div>
                <div class="row g-4">
                    <div class="col-md-6" id="calc_refer_section" style="display: none;">
                        <label class="form-label fw-bold">1. Onset to Hospital Level รพช. (นาที)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-calculator"></i></span>
                            <input type="number" class="form-control fw-bold text-primary"
                                id="calc_onset_to_refer" name="time_onset_to_refer_min"
                                readonly placeholder="คำนวณอัตโนมัติ...">
                            <span class="input-group-text">นาที</span>
                        </div>
                        <div class="form-text text-muted small">
                            (เวลาที่ถึง รพ.ต้นทาง - เวลาที่เริ่มมีอาการ)
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">2. Onset to Hospital Level รพ.หาดใหญ่ (นาที)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-calculator"></i></span>
                            <input type="number" class="form-control fw-bold text-danger"
                                id="calc_onset_to_hatyai" name="time_onset_to_hatyai_min"
                                readonly placeholder="คำนวณอัตโนมัติ...">
                            <span class="input-group-text">นาที</span>
                        </div>
                        <div class="form-text text-muted small">
                            (เวลาที่ถึง รพ.หาดใหญ่ - เวลาที่เริ่มมีอาการ)
                            <span id="calc_arrival_type_badge" class="badge bg-secondary ms-1"></span>
                        </div>
                    </div>
                </div>
                <div class="col-12" id="calc_refer_details" style="display: none;">
                    <div class="p-3 bg-white border rounded">
                        <h6 class="text-primary fw-bold"><i class="bi bi-ambulance"></i> (2) Time to Refer Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">ระยะเวลาที่อยู่ รพช. (นาที)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-hourglass-split"></i></span>
                                    <input type="number" class="form-control fw-bold text-primary"
                                        id="calc_refer_stay" name="time_refer_hospital_min" readonly placeholder="...">
                                    <span class="input-group-text">นาที</span>
                                </div>
                                <div class="form-text small">(เวลารถออก - เวลาถึง รพช.)</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">ระยะเวลาเดินทาง (นาที)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-speedometer"></i></span>
                                    <input type="number" class="form-control fw-bold text-primary"
                                        id="calc_refer_travel" name="time_refer_travel_min" readonly placeholder="...">
                                    <span class="input-group-text">นาที</span>
                                </div>
                                <div class="form-text small">(เวลาถึง รพ.หาดใหญ่ - เวลารถออก)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5 mb-5">
                <button type="button" class="btn btn-primary btn-lg px-5 py-3 shadow" id="saveMainFormBtn" style="border-radius: 50px;">
                    <i class="bi bi-save-fill me-2"></i> บันทึกข้อมูล
                </button>

                <a href="<?= $admission_id ? 'diagnosis_form.php?admission_id=' . $admission_id : '#' ?>"
                    id="nextStepBtn"
                    class="btn btn-success btn-lg px-5 ms-2 py-3 shadow <?= $admission_id ? '' : 'd-none' ?>" style="border-radius: 50px;">
                    ไปยังหน้า 2 (ER) <i class="bi bi-arrow-right-circle-fill ms-2"></i>
                </a>
            </div>

        </form>

    </div>

    <!-- JavaScript รวมศูนย์ (Consolidated Script) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- ส่วนที่ 1: การจัดการ UI ทั่วไป ---

            // 1.1 Logic สำหรับ "โรคประจำตัวอื่นๆ"
            const otherCheckbox = document.getElementById('comorbid_other_check');
            const otherContainer = document.getElementById('other_disease_input'); // กล่องคลุม
            const otherInput = document.getElementById('comorbid_other_text');     // ตัว input

            if (otherCheckbox) {
                otherCheckbox.addEventListener('change', function() {
                    // *** แก้ไข: สั่งโชว์/ซ่อนที่กล่องคลุม แทนที่จะเป็นตัว input ***
                    otherContainer.style.display = this.checked ? 'block' : 'none';
                    
                    // ถ้าติ๊กออก ให้ล้างค่าใน input ด้วย
                    if (!this.checked) otherInput.value = '';
                });
            }

            // 1.2 Logic สำหรับ "ประเภทบัตรอื่นๆ" (Alien/Passport)
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

            // 1.3 Logic สำหรับ "ประเภทการมาของคนไข้"
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
            
            
            // --- ส่วนที่ 2: ระบบค้นหา (HN / AN) ---
            const radioHn = document.getElementById('search_type_hn');
            const radioAn = document.getElementById('search_type_an');
            const searchInput = document.getElementById('search_keyword');
            const fetchButton = document.getElementById('fetch_patient_btn');

            // Input Fields ที่จะนำข้อมูลมาใส่
            const realHnField = document.getElementById('real_hn_field');
            const displayCid = document.getElementById('id_card');
            const displayName = document.getElementById('display_name');
            const displayAge = document.getElementById('display_age');
            const displaySex = document.getElementById('display_sex');
            const displayAd = document.getElementById('display_address');
            const displayDate = document.getElementById('display_date');
            const displayBT = document.getElementById('display_blood_type');
            const anField = document.getElementById('patient_an');

            // 2.1 เปลี่ยน Placeholder ตามประเภทการค้นหา
            function updatePlaceholder() {
                if (radioAn.checked) searchInput.placeholder = "กรอกเลข AN (เช่น 1/45)";
                else searchInput.placeholder = "กรอกเลข HN...";
            }
            radioHn.addEventListener('change', updatePlaceholder);
            radioAn.addEventListener('change', updatePlaceholder);
            
            // เรียกใช้เพื่อตั้งค่าเริ่มต้น
            updatePlaceholder();

            // 2.2 ฟังก์ชันกดปุ่มค้นหา
            if (fetchButton) {
                fetchButton.addEventListener('click', function() {
                    const keyword = searchInput.value.trim();
                    const searchType = document.querySelector('input[name="search_type"]:checked').value;

                    if (!keyword) {
                        Swal.fire('Warning', 'กรุณาระบุข้อมูลเพื่อค้นหา', 'warning');
                        return;
                    }

                    Swal.fire({
                        title: 'กำลังค้นหา...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    const formData = new FormData();
                    formData.append('keyword', keyword);
                    formData.append('type', searchType); // ส่งประเภทการค้นหา (hn หรือ an)

                    fetch('api_patient.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            Swal.close();

                            // ตรวจสอบข้อมูล (API อาจส่งมาเป็น Array หรือ Object)
                            let p = null;
                            if (Array.isArray(data) && data.length > 0) p = data[0];
                            else if (data && !Array.isArray(data) && data.hn) p = data;

                            if (p) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'พบข้อมูล!',
                                    text: searchType === 'an' ? `AN: ${p.an} (HN: ${p.hn})` : `HN: ${p.hn}`,
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                // --- MAPPING DATA ---

                                // 1. HN
                                const retrievedHN = p.hn || '';
                                realHnField.value = retrievedHN;
                                document.getElementById('patient_hn_display').value = retrievedHN;

                                // 2. AN (Logic สำคัญ: เติมให้อัตโนมัติ)
                                // เคลียร์ค่าก่อน
                                if(anField) anField.value = '';

                                if (searchType === 'an') {
                                    // กรณี A: ค้นด้วย AN -> เอาเลขที่พิมพ์ค้นหานั่นแหละ มาใส่ช่อง AN เลย
                                    if(anField) anField.value = keyword; 
                                } else if (p.an) {
                                    // กรณี B: ค้นด้วย HN แต่ API ส่ง AN ล่าสุดมาด้วย -> เอามาใส่
                                    if(anField) anField.value = p.an;
                                }

                                // 3. ชื่อ-สกุล
                                let fullName = '';
                                if (p.flname) {
                                    fullName = p.flname;
                                } else if (p.fname) {
                                    const pname = p.pname || '';
                                    const fname = p.fname || '';
                                    const lname = p.lname || '';
                                    fullName = `${pname}${fname} ${lname}`.trim();
                                }
                                displayName.value = fullName;

                                // 4. เลขบัตร
                                displayCid.value = p.id_card || p.cid || '';

                                // 5. อายุ & เพศ & กรุ๊ปเลือด
                                displayAge.value = p.age_year || p.age || '';
                                displaySex.value = p.sex || '';
                                displayBT.value = p.bgname || ''; 

                                // 6. ที่อยู่
                                let fullAddress = p.hname || '';
                                if (!fullAddress && p.addrpart) {
                                    fullAddress = `บ้านเลขที่ ${p.addrpart} ม.${p.moopart||'-'} ต.${p.tmbpart||'-'} อ.${p.amppart||'-'} จ.${p.chwpart||'-'}`;
                                }
                                displayAd.value = fullAddress;

                                // 7. วันเกิด
                                let birthDateRaw = p.bdate || p.birth || '';
                                if (birthDateRaw) {
                                    // ถ้าเป็น Format ISO (YYYY-MM-DD)
                                    if (birthDateRaw.match(/^\d{4}-\d{2}-\d{2}$/)) {
                                        document.getElementById('birthdate_db').value = birthDateRaw;
                                        const [y, m, d] = birthDateRaw.split('-');
                                        const thaiYear = parseInt(y) + 543;
                                        displayDate.value = `${d}/${m}/${thaiYear}`;
                                    }
                                    // ถ้าเป็นภาษาไทยเดิม
                                    else {
                                        displayDate.value = birthDateRaw;
                                        const mysqlDate = convertThaiTextToMySQL(birthDateRaw);
                                        document.getElementById('birthdate_db').value = mysqlDate;
                                    }
                                } else {
                                    displayDate.value = '';
                                    document.getElementById('birthdate_db').value = '';
                                }

                                // 8. วันที่ Admit (Date Admit) ** เพิ่มส่วนนี้ **
                                // เช็คหลายๆ key ที่เป็นไปได้จาก API (regdate, date_admit, admitdate)
                                const rawAdmitDate = p.regdate || p.admitdate || p.date_admit || '';
                                const dateAdmitField = document.getElementById('date_admit');
                                
                                if (rawAdmitDate && dateAdmitField) {
                                    // ฟังก์ชันช่วยแปลงวันที่จาก API (รองรับทั้ง พ.ศ. และ ค.ศ.)
                                    const processedDate = processApiDate(rawAdmitDate);
                                    if(processedDate) {
                                        dateAdmitField.value = processedDate;
                                    }
                                }

                                // 9. แยกที่อยู่ลงฟิลด์ย่อย
                                let subDist = '', district = '', province = '', zipcode = '';
                                const addressRegex = /(?:ต\.|แขวง)\s*([^\s]+)\s+(?:อ\.|เขต)\s*([^\s]+)\s+(?:จ\.|จังหวัด)\s*([^\s]+)(?:\s+(\d{5}))?/;
                                const match = fullAddress.match(addressRegex);

                                if (match) {
                                    subDist = match[1];
                                    district = match[2];
                                    province = match[3];
                                    zipcode = match[4] || '';
                                    document.getElementById('detailed_address_section').classList.remove('d-none');
                                }

                                

                                document.getElementById('subdistrict').value = subDist;
                                document.getElementById('district').value = district;
                                document.getElementById('province').value = province;
                                document.getElementById('zipcode').value = zipcode;

                            } else {
                                Swal.fire('Error', 'ไม่พบข้อมูลผู้ป่วย', 'error');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Error', 'การเชื่อมต่อขัดข้อง หรือ API มีปัญหา', 'error');
                        });
                });
            }

            // Helper แปลงวันที่จาก API (รองรับ YYYY-MM-DD, YYYYMMDD และ พ.ศ.)
            function processApiDate(dateStr) {
                if (!dateStr) return '';
                let y, m, d;
                
                // ตัดเวลาออกถ้ามี (เช่น 2024-01-01 10:30:00)
                dateStr = dateStr.split(' ')[0];

                if (dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    // รูปแบบ YYYY-MM-DD
                    [y, m, d] = dateStr.split('-');
                } else if (dateStr.match(/^\d{8}$/)) { 
                    // รูปแบบ YYYYMMDD (เช่น 25670120)
                    y = dateStr.substring(0, 4);
                    m = dateStr.substring(4, 6);
                    d = dateStr.substring(6, 8);
                } else {
                    return ''; // ไม่ตรงรูปแบบ
                }

                // แปลง พ.ศ. -> ค.ศ. (ถ้าปีมากกว่า 2400 ถือว่าเป็น พ.ศ.)
                if (parseInt(y) > 2400) {
                    y = parseInt(y) - 543;
                }
                
                // คืนค่า YYYY-MM-DD (format ของ html input date)
                return `${y}-${m}-${d}`;
            }

            // Helper แปลงวันที่ไทย (สำหรับวันเกิด)
            function convertThaiTextToMySQL(thaiDateStr) {
                if (!thaiDateStr) return "";
                const parts = thaiDateStr.trim().split(/\s+/);
                if (parts.length !== 3) return "";
                let day = parts[0];
                let monthStr = parts[1];
                let yearThai = parseInt(parts[2]);
                const thaiMonths = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
                let monthIndex = thaiMonths.indexOf(monthStr);
                if (monthIndex === -1) {
                    const thaiMonthsShort = ["ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
                    monthIndex = thaiMonthsShort.indexOf(monthStr);
                }
                if (monthIndex === -1) return "";
                let month = (monthIndex + 1).toString().padStart(2, '0');
                day = day.toString().padStart(2, '0');
                let yearEng = yearThai - 543;
                return `${yearEng}-${month}-${day}`;
            }

            // --- ส่วนที่ 3: การบันทึกข้อมูล (Save) ---
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
                            Swal.showLoading();

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

                                        saveButton.innerHTML = '<i class="bi bi-check-lg"></i> บันทึกแล้ว';
                                        saveButton.classList.replace('btn-primary', 'btn-secondary');

                                        nextButton.classList.remove('d-none');
                                        nextButton.href = data.redirect_url;
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

            // --- ส่วนที่ 4: การคำนวณเวลา (Time Calculation) ---
            // 1. Onset
            const onsetDate = document.querySelector('input[name="onsetTime_onset_date"]');
            const onsetTime = document.querySelector('input[name="onsetTime_onset_time"]');
            // 2. Refer Arrival
            const referArrDate = document.querySelector('input[name="refer_arrival_date"]');
            const referArrTime = document.querySelector('input[name="refer_arrival_time"]');
            // 3. Refer Departure
            const referDepDate = document.querySelector('input[name="arrivalTime"]');
            const referDepTime = document.querySelector('input[name="refer_departure_time"]');
            // 4. EMS
            const emsDate = document.querySelector('input[name="first_medical_contact_date"]');
            const emsTime = document.querySelector('input[name="first_medical_contact_time"]');
            // 5. Walk-in
            const walkinDate = document.querySelector('input[name="er_arrival_date"]');
            const walkinTime = document.querySelector('input[name="er_arrival_time"]');
            // 6. Hatyai Arrival
            const hatyaiDate = document.querySelector('input[name="arrivalTime_date"]');
            const hatyaiTime = document.querySelector('input[name="arrivalTime_time"]');
            
            // Outputs
            const outRefer = document.getElementById('calc_onset_to_refer');
            const outHatyai = document.getElementById('calc_onset_to_hatyai');
            const sectionRefer = document.getElementById('calc_refer_section');
            const badgeType = document.getElementById('calc_arrival_type_badge');
            const sectionReferDetails = document.getElementById('calc_refer_details');
            const outReferStay = document.getElementById('calc_refer_stay');
            const outReferTravel = document.getElementById('calc_refer_travel');

            function calculateDiffMinutes(d1, t1, d2, t2) {
                if (!d1 || !t1 || !d2 || !t2) return '';
                let timeForCalc1 = t1;
                let timeForCalc2 = t2;
                if (timeForCalc1.startsWith("24:")) timeForCalc1 = timeForCalc1.replace("24:", "00:");
                if (timeForCalc2.startsWith("24:")) timeForCalc2 = timeForCalc2.replace("24:", "00:");

                const start = new Date(`${d1}T${timeForCalc1}`);
                const end = new Date(`${d2}T${timeForCalc2}`);
                const diffMs = end - start;
                return Math.floor(diffMs / 60000);
            }

            function syncWalkInToHatyai() {
                const currentType = document.querySelector('input[name="arrival_type"]:checked')?.value;
                if (currentType === 'walk_in') {
                    if (walkinDate && hatyaiDate) hatyaiDate.value = walkinDate.value;
                    if (walkinTime && hatyaiTime) hatyaiTime.value = walkinTime.value;
                }
            }

            function updateCalculations() {
                const currentType = document.querySelector('input[name="arrival_type"]:checked')?.value || '';
                const isRefer = (currentType === 'refer');

                if (sectionRefer) sectionRefer.style.display = isRefer ? 'block' : 'none';
                if (sectionReferDetails) sectionReferDetails.style.display = isRefer ? 'block' : 'none';

                if (currentType === 'ems') badgeType.textContent = 'EMS';
                else if (currentType === 'walk_in') badgeType.textContent = 'Walk-in';
                else if (currentType === 'refer') badgeType.textContent = 'Refer';
                else badgeType.textContent = '';

                // A. Refer Calculation
                if (isRefer) {
                    const minsRefer = calculateDiffMinutes(onsetDate.value, onsetTime.value, referArrDate.value, referArrTime.value);
                    outRefer.value = (minsRefer !== '' && !isNaN(minsRefer)) ? minsRefer : '';

                    const minsStay = calculateDiffMinutes(referArrDate.value, referArrTime.value, referDepDate.value, referDepTime.value);
                    outReferStay.value = (minsStay !== '' && !isNaN(minsStay)) ? minsStay : '';

                    const minsTravel = calculateDiffMinutes(referDepDate.value, referDepTime.value, hatyaiDate.value, hatyaiTime.value);
                    outReferTravel.value = (minsTravel !== '' && !isNaN(minsTravel)) ? minsTravel : '';
                } else {
                    outRefer.value = ''; outReferStay.value = ''; outReferTravel.value = '';
                }

                // B. Hatyai Calculation
                let minsHatyai = '';
                if (currentType === 'ems') {
                    minsHatyai = calculateDiffMinutes(onsetDate.value, onsetTime.value, emsDate.value, emsTime.value);
                } else if (currentType === 'walk_in') {
                    syncWalkInToHatyai();
                    minsHatyai = calculateDiffMinutes(onsetDate.value, onsetTime.value, hatyaiDate.value, hatyaiTime.value);
                } else {
                    minsHatyai = calculateDiffMinutes(onsetDate.value, onsetTime.value, hatyaiDate.value, hatyaiTime.value);
                }
                outHatyai.value = (minsHatyai !== '' && !isNaN(minsHatyai)) ? minsHatyai : '';
            }

            const inputs = [onsetDate, onsetTime, referArrDate, referArrTime, referDepDate, referDepTime, emsDate, emsTime, walkinDate, walkinTime, hatyaiDate, hatyaiTime];
            inputs.forEach(el => { if (el) el.addEventListener('change', updateCalculations); });
            
            const arrRadios = document.querySelectorAll('input[name="arrival_type"]');
            arrRadios.forEach(radio => { radio.addEventListener('change', updateCalculations); });

            updateCalculations();
        });
    </script>

    <!-- Script สำหรับ Timepicker -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            formatDate: function(date, format, locale) {
                let hours = date.getHours();
                let minutes = date.getMinutes();
                if (hours === 0) {
                    const formattedMinutes = minutes.toString().padStart(2, '0');
                    return `24:${formattedMinutes}`;
                }
                return flatpickr.formatDate(date, format);
            }
        });
    </script>

</body>
</html>