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

            <div class="card-form">
                <div class="section-title" style="margin-top:0;">
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
            </div>

            <div id="patient_info_section" class="card-form">
                <div class="section-title" style="margin-top:0;">
                    <i class="bi bi-person-badge"></i> 2. ข้อมูลผู้ป่วย
                </div>
                <div class="row g-3">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">เลขบัตรประจำตัวประชาชน</label>
                        <input type="text" name="id_card" id="id_card" class="form-control bg-light" value="<?= val('id_card') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">ชื่อ-สกุล</label>
                        <input type="text" class="form-control fw-bold text-primary" id="display_name" name="flname" value="<?= val('flname') ?>" placeholder="...">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">วันเกิด</label>
                        <input type="text" class="form-control" id="display_date" name="birthdate_show"
                            value="<?= dt('birthdate', 'd') ? date('d/m/Y', strtotime(dt('birthdate', 'd') . ' +543 years')) : '' ?>"
                            placeholder="วว/ดด/ปปปป (ปีไทย)">
                        <input type="hidden" id="birthdate_db" name="birthdate" value="<?= dt('birthdate', 'd') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">ที่อยู่</label>
                        <input type="text" class="form-control" id="display_address" name="address_full" value="<?= val('address_full') ?>" placeholder="...">
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label">กรุ๊ปเลือด</label>
                        <input type="text" class="form-control" id="display_blood_type" name="blood_type" value="<?= val('blood_type') ?>" placeholder="...">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">อายุ</label>
                        <input type="text" class="form-control" id="display_age" name="age" value="<?= val('age') ?>" placeholder="...">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">เพศ</label>
                        <input type="text" class="form-control" id="display_sex" name="gender" value="<?= val('gender') ?>" placeholder="...">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">เบอร์โทรศัพท์</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?= val('phone_number') ?>" placeholder="...">
                    </div>
                    <div class="col-12">
                        <hr class="text-muted opacity-25">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="card_type" class="form-label mb-2">ประเภทบัตรอื่นๆ</label>
                        <select name="card_type" id="card_type" class="form-select">
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

                    <div class="col-md-6 mb-3">
                        <label for="" class="form-label mb-2">สิทธิการรักษา</label>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="btn-group" role="group">
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

            <div class="card-form">
                <div class="row">
                    <div class="col-md-8 border-end">
                        <div class="section-title" style="margin-top:0;">
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
                    </div>
                    <div class="col-md-4">
                        <div class="section-title" style="margin-top:0;">
                            <i class="bi bi-person-x"></i> 4. สารเสพติด
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="comorbid_alcohol" value="1" id="comorbid_alcohol" <?= chk('addict_alcohol') ?>><label class="form-check-label" for="comorbid_alcohol">ALCOHOL</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="comorbid_smoking" value="1" id="comorbid_smoking" <?= chk('addict_smoking') ?>>
                            <label class="form-check-label" for="comorbid_smoking">SMOKING</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="comorbid_kratom" value="1" id="comorbid_kratom" <?= chk('addict_kratom') ?>>
                            <label class="form-check-label" for="comorbid_kratom">กระท่อม</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="comorbid_cannabis" value="1" id="comorbid_cannabis" <?= chk('addict_cannabis') ?>>
                            <label class="form-check-label" for="comorbid_cannabis">กัญชา</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="comorbid_crystal_meth" value="1" id="comorbid_crystal_meth" <?= chk('addict_crystal_meth') ?>>
                            <label class="form-check-label" for="comorbid_crystal_meth">ไอซ์</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="comorbid_yaba" value="1" id="comorbid_yaba" <?= chk('addict_yaba') ?>>
                            <label class="form-check-label" for="comorbid_yaba">ยาบ้า</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-form">
                <div class="section-title" style="margin-top:0;">
                    <i class="bi bi-door-open"></i> 5. ประเภทการมาของคนไข้ (Arrival Type)
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-check">
                                <input class="form-check-input arrival-trigger" type="radio" name="arrival_type" id="arrival_refer" value="refer" <?= chk('arrival_type', 'refer') ?>>
                                <label class="form-check-label fw-bold" for="arrival_refer"> Refer </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input arrival-trigger" type="radio" name="arrival_type" id="arrival_ems" value="ems" <?= chk('arrival_type', 'ems') ?>>
                                <label class="form-check-label fw-bold" for="arrival_ems"> EMS (1669) </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input arrival-trigger" type="radio" name="arrival_type" id="arrival_walk_in" value="walk_in" <?= chk('arrival_type', 'walk_in') ?>>
                                <label class="form-check-label fw-bold" for="arrival_walk_in"> Walk in </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input arrival-trigger" type="radio" name="arrival_type" id="arrival_ipd" value="ipd" <?= chk('arrival_type', 'ipd') ?>>
                                <label class="form-check-label fw-bold" for="arrival_ipd"> IPD (ย้ายจากตึกใน) </label>
                            </div>
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
                                <label for="arrivalTime_time" class="form-label">เวลาที่รถออก (Time)</label>
                                <input type="time" class="form-control" id="arrivalTime_time" name="arrivalTime_time" value="<?= dt('transfer_departure_datetime', 't') ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="refer_arrival_date" class="form-label ">วันที่ที่ผป.มาถึง</label>
                                <input type="date" class="form-control" name="refer_arrival_date" value="<?= dt('refer_arrival_datetime', 'd') ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="refer_arrival_time" class="form-label">เวลา</label>
                                <input type="time" class="form-control" name="refer_arrival_time" value="<?= dt('refer_arrival_datetime', 't') ?>">
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
                                <input type="time" class="form-control" name="first_medical_contact_time" value="<?= dt('ems_first_medical_contact', 't') ?>">
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
                                <input type="time" class="form-control" name="er_arrival_time" value="<?= dt('walk_in_datetime', 't') ?>">
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
                                <input type="time" class="form-control" name="ipd_onset_time" value="<?= dt('ipd_onset_datetime', 't') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-form">
                <div class="row">
                    <div class="col-md-8 border-end">
                        <div class="section-title" style="margin-top:0;">
                            <i class="bi bi-capsule"></i> 6. ยาประจำตัว (Regular medication)
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="med_anti_platelet" value="1" id="med_anti_platelet" <?= chk('med_anti_platelet') ?>>
                                    <label class="form-check-label fw-bold" for="med_anti_platelet">Anti-platelet</label>
                                    <div class="ms-4 mt-1 p-2 bg-light rounded">
                                        <div class="form-check mb-1">
                                            <input class="form-check-input" type="checkbox" value="1" id="med_asa" name="med_asa" <?= chk('med_asa') ?>>
                                            <label class="form-check-label" for="med_asa">ASA (Aspirin)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" id="med_clopidogrel" name="med_clopidogrel" <?= chk('med_clopidogrel') ?>>
                                            <label class="form-check-label" for="med_clopidogrel">Clopidogrel</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" id="med_cilostazol" name="med_cilostazol" <?= chk('med_cilostazol') ?>>
                                            <label class="form-check-label" for="med_cilostazol">Cilostazol</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" id="med_ticaqrelor" name="med_ticaqrelor" <?= chk('med_ticaqrelor') ?>>
                                            <label class="form-check-label" for="med_ticaqrelor">Ticaqrelor</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="med_anti_coagulant" value="1" id="med_anti_coagulant" <?= chk('med_anti_coagulant') ?>>
                                    <label class="form-check-label fw-bold" for="med_anti_coagulant">Anti-coagulant</label>
                                    <div class="ms-4 mt-1 p-2 bg-light rounded">
                                        <div class="form-check mb-1">
                                            <input class="form-check-input" type="checkbox" value="1" id="med_warfarin" name="med_warfarin" <?= chk('med_warfarin') ?>>
                                            <label class="form-check-label" for="med_warfarin">Warfarin</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" id="med_noac" name="med_noac" <?= chk('med_noac') ?>>
                                            <label class="form-check-label" for="med_noac">NOAC</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="section-title" style="margin-top:0;">
                            <i class="bi bi-star-half"></i> 7. MRS Score (Pre-morbid)
                        </div>
                        <div class="mb-3">
                            <label for="mrs_score" class="form-label ">เลือกคะแนน MRS (0-6) <span class="required-mark text-danger">*</span></label>
                            <select class="form-select" id="mrs_score" name="mrs_score" required>
                                <option value="" disabled <?= sel('pre_morbid_mrs', '') ?>>---- กรุณาเลือก ----</option>
                                <?php for ($i = 0; $i <= 6; $i++) echo "<option value='$i' " . sel('pre_morbid_mrs', $i) . ">$i</option>"; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-form card-alert" style="border-left: 5px solid #d32f2f;">
                <div class="section-title text-danger" style="margin-top:0;">
                    <i class="bi bi-card-checklist"></i> 8. ส่วนประเมินแรกรับ (Triage Assessment)
                </div>
                <div class="p-3 mb-3 bg-white rounded border border-danger border-opacity-25 shadow-sm">
                    <div class="col-md-5 ">
                    <div class="mb-2">
                            <label class="form-label small text-muted mb-0">เวลาที่เริ่มมีอาการ (Onset)</label>
                            <div class="input-group input-group-sm">
                                <input type="date" class="form-control" name="onsetTime_onset_date" value="<?= dt('onset_datetime', 'd') ?>">
                                <input type="time" class="form-control" name="onsetTime_onset_time" value="<?= dt('onset_datetime', 't') ?>">
                            </div>
                    </div>
                </div>
                </div>
                
                
                <div class="p-3 mb-4 bg-white rounded border border-danger border-opacity-25 shadow-sm">
                    <p class="fs-5 fw-bold text-danger mb-2">ผู้ป่วยเข้าเกณฑ์ Stroke Fast Track หรือไม่?</p>
                    <div class="d-flex align-items-center gap-3">
                        <input type="radio" class="btn-check" name="fast_track_status" id="fast_track_yes" value="yes" autocomplete="off" required <?= chk('fast_track_status', 'yes') ?>>
                        <label class="btn btn-outline-danger btn-toggle-custom px-4" for="fast_track_yes"> YES </label>

                        <input type="radio" class="btn-check" name="fast_track_status" id="fast_track_no" value="no" autocomplete="off" required <?= chk('fast_track_status', 'no') ?>>
                        <label class="btn btn-outline-secondary btn-toggle-custom px-4" for="fast_track_no"> NO </label>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-12">
                        <h6 class="fw-bold text-secondary"><i class="bi bi-activity"></i> อาการสำคัญ (Symptoms - F.A.S.T.)</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <div class="border p-2 rounded text-center" style="min-width: 80px;">
                                <input class="form-check-input" type="checkbox" id="sympDroop" value="1" name="sympDroop" <?= chk('symp_face') ?>>
                                <label class="d-block fw-bold" for="sympDroop">Face</label>
                            </div>
                            <div class="border p-2 rounded text-center" style="min-width: 80px;">
                                <input class="form-check-input" type="checkbox" id="sympWeakness" value="1" name="sympWeakness" <?= chk('symp_arm') ?>>
                                <label class="d-block fw-bold" for="sympWeakness">Arm</label>
                            </div>
                            <div class="border p-2 rounded text-center" style="min-width: 80px;">
                                <input class="form-check-input" type="checkbox" id="sympSpeech" value="1" name="sympSpeech" <?= chk('symp_speech') ?>>
                                <label class="d-block fw-bold" for="sympSpeech">Speech</label>
                            </div>
                            <div class="border p-2 rounded text-center" style="min-width: 80px;">
                                <input class="form-check-input" type="checkbox" id="symp_v" name="symp_v" value="1" <?= chk('symp_vision') ?>>
                                <label class="d-block fw-bold" for="symp_v">Vision</label>
                            </div>
                            <div class="border p-2 rounded text-center" style="min-width: 80px;">
                                <input class="form-check-input" type="checkbox" id="symp_a2" name="symp_a2" value="1" <?= chk('symp_aphasia') ?>>
                                <label class="d-block fw-bold" for="symp_a2">Aphasia</label>
                            </div>
                            <div class="border p-2 rounded text-center" style="min-width: 80px;">
                                <input class="form-check-input" type="checkbox" id="symp_n" name="symp_n" value="1" <?= chk('symp_neglect') ?>>
                                <label class="d-block fw-bold" for="symp_n">Neglect</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <hr class="my-0">
                    </div>

                    <div class="col-md-7">
                        <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-calculator"></i> คะแนนประเมิน (Scores)</h6>
                        <div class="row g-3">
                            <div class="col-md-9">
                                <label class="form-label fw-bold">GCS (Glasgow Coma Scale)</label>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="fw-bold text-muted" style="width:20px;">E</span>
                                    <?php for ($i = 1; $i <= 4; $i++) echo "<input type='radio' class='btn-check' name='gcs_e' id='gcs_e$i' value='$i' " . chk('gcs_e', $i) . "><label class='btn btn-outline-secondary btn-gcs btn-sm' for='gcs_e$i'>$i</label>"; ?>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="fw-bold text-muted" style="width:20px;">V</span>
                                    <?php for ($i = 1; $i <= 5; $i++) echo "<input type='radio' class='btn-check' name='gcs_v' id='gcs_v$i' value='$i' " . chk('gcs_v', $i) . "><label class='btn btn-outline-secondary btn-gcs btn-sm' for='gcs_v$i'>$i</label>"; ?>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-muted" style="width:20px;">M</span>
                                    <?php for ($i = 1; $i <= 6; $i++) echo "<input type='radio' class='btn-check' name='gcs_m' id='gcs_m$i' value='$i' " . chk('gcs_m', $i) . "><label class='btn btn-outline-secondary btn-gcs btn-sm' for='gcs_m$i'>$i</label>"; ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="nihss" class="form-label fw-bold text-primary">NIHSS</label>
                                <input type="number" class="form-control form-control-lg border-primary text-center fw-bold" id="nihss" placeholder="0-42" min="0" max="42" oninput="if(parseInt(this.value) > 42) this.value = 42; if(parseInt(this.value) < 0) this.value = 0;" name="nihss" required value="<?= val('nihss_score') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5 border-start">
                        <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-clock-history"></i> เวลา (Time)</h6>
                        
                        
                        <div class="mb-2">
                            <label class="form-label small text-muted mb-0">เวลาที่ถึง รพ.หาดใหญ่ (Arrival)</label>
                            <div class="input-group input-group-sm">
                                <input type="date" class="form-control" name="arrivalTime_date" value="<?= dt('hospital_arrival_datetime', 'd') ?>">
                                <input type="time" class="form-control" name="arrivalTime_time" value="<?= dt('hospital_arrival_datetime', 't') ?>">
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

                                // ใหม่
                                if (p.bdate) {
                                    // เรียกฟังก์ชันใหม่ที่รองรับภาษาไทย
                                    const mysqlDate = convertThaiTextToMySQL(p.bdate);
                                    document.getElementById('birthdate_db').value = mysqlDate;

                                    // (Optional) เช็คใน Console ดูว่าแปลงถูกไหม
                                    console.log(`Original: ${p.bdate} -> Converted: ${mysqlDate}`);
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

            // ฟังก์ชันแปลง "30 กรกฎาคม 2521" -> "1978-07-30"
            function convertThaiTextToMySQL(thaiDateStr) {
                if (!thaiDateStr) return "";

                // ตัดช่องว่างหน้าหลัง และแยกคำด้วยช่องว่าง (Space)
                const parts = thaiDateStr.trim().split(/\s+/);

                // ถ้าแยกแล้วไม่ได้ 3 ส่วน (วัน เดือน ปี) ให้จบการทำงาน
                if (parts.length !== 3) return "";

                let day = parts[0];
                let monthStr = parts[1];
                let yearThai = parseInt(parts[2]);

                // สร้างตารางเทียบชื่อเดือนไทย
                const thaiMonths = [
                    "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
                    "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
                ];

                // หา index ของเดือน (เริ่มที่ 0)
                let monthIndex = thaiMonths.indexOf(monthStr);

                // ถ้าไม่เจอชื่อเดือน ให้ลองหาแบบย่อ (เผื่อ API ส่ง ก.ค. มา)
                if (monthIndex === -1) {
                    const thaiMonthsShort = [
                        "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.",
                        "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."
                    ];
                    monthIndex = thaiMonthsShort.indexOf(monthStr);
                }

                if (monthIndex === -1) return ""; // ยังไม่เจออีก ก็ยอมแพ้

                // แปลงเป็นตัวเลข
                let month = (monthIndex + 1).toString().padStart(2, '0'); // แปลง 1 -> "01"
                day = day.toString().padStart(2, '0'); // แปลง 5 -> "05"
                let yearEng = yearThai - 543; // ลบ 543

                // คืนค่า YYYY-MM-DD
                return `${yearEng}-${month}-${day}`;
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- TIME CALCULATION LOGIC (UPDATED FOR EMS) ---

            // 1. Onset (เวลาเริ่มมีอาการ)
            const onsetDate = document.querySelector('input[name="onsetTime_onset_date"]');
            const onsetTime = document.querySelector('input[name="onsetTime_onset_time"]');

            // 2. Refer Arrival (เวลาถึง รพ.ชุมชน - Part 5)
            const referDate = document.querySelector('input[name="refer_arrival_date"]');
            const referTime = document.querySelector('input[name="refer_arrival_time"]');

            // 3. EMS Contact (เวลาเจ้าหน้าที่ไปถึงตัวผู้ป่วย - Part 5)
            const emsDate = document.querySelector('input[name="first_medical_contact_date"]');
            const emsTime = document.querySelector('input[name="first_medical_contact_time"]');

            // 4. Walk-in Arrival (เวลาถึง ER - Part 5)
            const walkinDate = document.querySelector('input[name="er_arrival_date"]');
            const walkinTime = document.querySelector('input[name="er_arrival_time"]');

            // 5. Hatyai Arrival (เวลาถึง รพ.หาดใหญ่ - Part 8)
            const hatyaiDate = document.querySelector('input[name="arrivalTime_date"]');
            const hatyaiTime = document.querySelector('input[name="arrivalTime_time"]');

            // Radio Arrival Type
            const arrivalRadios = document.querySelectorAll('input[name="arrival_type"]');
            
            // Output Fields
            const outRefer = document.getElementById('calc_onset_to_refer');
            const outHatyai = document.getElementById('calc_onset_to_hatyai');
            const sectionRefer = document.getElementById('calc_refer_section');
            const badgeType = document.getElementById('calc_arrival_type_badge');

            // ฟังก์ชันคำนวณความต่างเวลา (นาที)
            function calculateDiffMinutes(d1, t1, d2, t2) {
                if (!d1 || !t1 || !d2 || !t2) return '';
                const start = new Date(`${d1}T${t1}`);
                const end = new Date(`${d2}T${t2}`);
                const diffMs = end - start;
                return Math.floor(diffMs / 60000); // แปลงเป็นนาที
            }

            // ฟังก์ชัน Sync เวลาจาก Walk-in Part 5 -> Part 8
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

                // --- 1. จัดการการแสดงผล ---
                if (sectionRefer) sectionRefer.style.display = isRefer ? 'block' : 'none';
                
                if(currentType === 'ems') badgeType.textContent = 'EMS (First Contact)';
                else if(currentType === 'walk_in') badgeType.textContent = 'Walk-in';
                else if(currentType === 'refer') badgeType.textContent = 'Refer';
                else badgeType.textContent = '';

                // --- 2. Logic การคำนวณ ---
                
                // A. คำนวณ Onset -> Refer Hospital (เฉพาะ Refer)
                if (isRefer) {
                    const minsRefer = calculateDiffMinutes(onsetDate.value, onsetTime.value, referDate.value, referTime.value);
                    outRefer.value = (minsRefer !== '' && !isNaN(minsRefer)) ? minsRefer : '';
                } else {
                    outRefer.value = ''; 
                }

                // B. คำนวณ Onset -> Hatyai Hospital (แยกเคส)
                let minsHatyai = '';

                if (currentType === 'ems') {
                    // *** แก้ไขตามที่ขอ: ใช้เวลา EMS Header (Part 5) ลบ Onset ***
                    minsHatyai = calculateDiffMinutes(onsetDate.value, onsetTime.value, emsDate.value, emsTime.value);

                } else if (currentType === 'walk_in') {
                    // Walk-in: Sync ไป Part 8 ก่อน แล้วคำนวณ
                    syncWalkInToHatyai();
                    minsHatyai = calculateDiffMinutes(onsetDate.value, onsetTime.value, hatyaiDate.value, hatyaiTime.value);
                
                } else {
                    // กรณี Refer หรืออื่นๆ: ใช้เวลาถึง รพ.หาดใหญ่ (Part 8) ปกติ
                    minsHatyai = calculateDiffMinutes(onsetDate.value, onsetTime.value, hatyaiDate.value, hatyaiTime.value);
                }

                outHatyai.value = (minsHatyai !== '' && !isNaN(minsHatyai)) ? minsHatyai : '';
            }

            // --- Event Listeners ---

            // Onset
            if(onsetDate) onsetDate.addEventListener('change', updateCalculations);
            if(onsetTime) onsetTime.addEventListener('change', updateCalculations);

            // Refer
            if(referDate) referDate.addEventListener('change', updateCalculations);
            if(referTime) referTime.addEventListener('change', updateCalculations);

            // EMS (เพิ่มใหม่)
            if(emsDate) emsDate.addEventListener('change', updateCalculations);
            if(emsTime) emsTime.addEventListener('change', updateCalculations);

            // Walk-in
            if(walkinDate) walkinDate.addEventListener('change', updateCalculations);
            if(walkinTime) walkinTime.addEventListener('change', updateCalculations);

            // Hatyai Arrival
            if(hatyaiDate) hatyaiDate.addEventListener('change', updateCalculations);
            if(hatyaiTime) hatyaiTime.addEventListener('change', updateCalculations);

            // Radio Buttons
            arrivalRadios.forEach(radio => {
                radio.addEventListener('change', updateCalculations);
            });

            // Run on load
            updateCalculations();
        });
    </script>
</body>

</html>