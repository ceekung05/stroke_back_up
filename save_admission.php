<?php
session_start();
require_once 'connectdb.php';

// ฟังก์ชันสำหรับแปลงค่า Checkbox เป็น 1 หรือ 0
function checkVal($field_name) {
    return isset($_POST[$field_name]) ? 1 : 0;
}

// ฟังก์ชันรวม Date และ Time เป็น DATETIME
function combineDateTime($date, $time) {
    if (!empty($date) && !empty($time)) {
        return $date . ' ' . $time;
    }
    return null;
}
// 
    // ฟังก์ชันแปลงวันที่ไทย (รองรับทั้ง dd/mm/yyyy และ "30 กรกฎาคม 2567")
function convertThaiDateToMySQL($dateString) {
    if (empty($dateString)) return null;

    $dateString = trim($dateString);

    // 1. กรณีเป็นรูปแบบ Slash (31/01/2567)
    if (strpos($dateString, '/') !== false) {
        $parts = explode('/', $dateString);
        if (count($parts) == 3) {
            $d = $parts[0];
            $m = $parts[1];
            $y = (int)$parts[2] - 543; // ลบปีไทย
            return "$y-$m-$d";
        }
    }

    // 2. กรณีเป็นภาษาไทย (30 กรกฎาคม 2521)
    $thaiMonths = [
        'มกราคม'=>'01', 'กุมภาพันธ์'=>'02', 'มีนาคม'=>'03', 'เมษายน'=>'04', 'พฤษภาคม'=>'05', 'มิถุนายน'=>'06',
        'กรกฎาคม'=>'07', 'กรกฏาคม'=>'07','สิงหาคม'=>'08', 'กันยายน'=>'09', 'ตุลาคม'=>'10', 'พฤศจิกายน'=>'11', 'ธันวาคม'=>'12',
        'ม.ค.'=>'01', 'ก.พ.'=>'02', 'มี.ค.'=>'03', 'เม.ย.'=>'04', 'พ.ค.'=>'05', 'มิ.ย.'=>'06',
        'ก.ค.'=>'07', 'ส.ค.'=>'08', 'ก.ย.'=>'09', 'ต.ค.'=>'10', 'พ.ย.'=>'11', 'ธ.ค.'=>'12'
    ];

    // แยกคำด้วยช่องว่าง (Space)
    $parts = preg_split('/\s+/', $dateString);
    if (count($parts) == 3) {
        $d = str_pad($parts[0], 2, '0', STR_PAD_LEFT); // เติม 0 ข้างหน้า
        $mStr = $parts[1];
        $y = (int)$parts[2] - 543;

        if (isset($thaiMonths[$mStr])) {
            $m = $thaiMonths[$mStr];
            return "$y-$m-$d";
        }
    }

    return null; // แปลงไม่ได้จริงๆ
}
// 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // =================================================================
    // PART 1: รับค่าและเตรียมข้อมูลสำหรับ tbl_patient
    // =================================================================
    // ดึงชื่อผู้ใช้ (ลองหา hr_fname ก่อน ถ้าไม่มีหา name ถ้าไม่มีอีกให้เป็น System)
$created_by = $_SESSION['user_data']['hr_fname'] ?? $_SESSION['user_data']['hr_fname'] ?? 'System'; 
    $hn = $_POST['hn'];
    $id_card = $_POST['id_card'] ?? null;
    $flname = $_POST['flname'] ?? null;
    // รับวันเกิดจาก Hidden Field ที่แปลงเป็น YYYY-MM-DD แล้ว (จาก JS)
    // ในไฟล์ save_admission.php (ส่วน PART 1)
// หาบรรทัด $birthdate = $_POST['birthdate'] ?? null; แล้วเปลี่ยนเป็นชุดนี้ครับ:

// --- แก้ไขใหม่: รับวันเกิดแบบฉลาด (รองรับทุกท่า) ---
    // 1. ลองดูใน Hidden Field ก่อน (เผื่อ JS ทำงานถูกต้อง)
    $birthdate = !empty($_POST['birthdate']) ? $_POST['birthdate'] : null;

    // 2. ถ้า Hidden ไม่มี หรือเป็น 0000-00-00 ให้ไปเอาจากช่องโชว์ แล้วให้ PHP แปลงเอง
    if (empty($birthdate) || $birthdate == '0000-00-00') {
        if (!empty($_POST['birthdate_show'])) {
            // เรียกฟังก์ชันเทพที่เราเพิ่งสร้าง
            $birthdate = convertThaiDateToMySQL($_POST['birthdate_show']);
        }
    }

    // 3. กันเหนียว ถ้าสุดท้ายยังว่าง ให้เป็น NULL
    if (empty($birthdate)) $birthdate = null;

// 2. ถ้า Hidden ว่าง (กรณีกรอกมือ หรือ แก้ไข) ให้ไปเอาจากช่องโชว์ (birthdate_show)
if (empty($birthdate) && !empty($_POST['birthdate_show'])) {
    $temp_date = $_POST['birthdate_show'];
    // เช็คว่ามีเครื่องหมาย / หรือไม่ (เช่น 31/01/2567)
    if (strpos($temp_date, '/') !== false) {
        $parts = explode('/', $temp_date);
        if (count($parts) == 3) {
            $d = $parts[0];
            $m = $parts[1];
            // ลบ 543 เพื่อแปลงปีไทยเป็นคริสต์ศักราช
            $y_eng = (int)$parts[2] - 543; 
            $birthdate = "$y_eng-$m-$d";
        }
    }
}

// ถ้าสุดท้ายยังเป็นค่าว่าง ให้ส่ง NULL เข้า Database (เพื่อไม่ให้เป็น 0000-00-00 แบบผิดๆ)
if (empty($birthdate)) {
    $birthdate = null;
}
    $gender = $_POST['gender'] ?? null;
    $age = $_POST['age'] ?? null; // อายุอาจจะไม่ต้องเก็บลง DB เพราะคำนวณจากวันเกิดได้ แต่ถ้าจะเก็บก็เพิ่มได้
    $blood_type = $_POST['blood_type'] ?? null;
    $address_full = $_POST['address_full'] ?? null; // ในฟอร์มชื่อ name="address"
    
    // จัดการบัตรอื่นๆ
    $other_id_type = $_POST['card_type'] ?? null; // ในฟอร์มชื่อ name="card_type"
    $other_id_number = null;

    if ($other_id_type === 'Alien') {
        $other_id_number = $_POST['alien_number'] ?? null;
    } elseif ($other_id_type === 'Passport') {
        $other_id_number = $_POST['passport_number'] ?? null;
    } else {
        $other_id_type = null; // ถ้าเลือกไม่ระบุ ให้เป็น NULL
    }

    $treatment_scheme = $_POST['btnTreatmentRight'] ?? null;


    // =================================================================
    // PART 2: บันทึกข้อมูลผู้ป่วย (tbl_patient) - UPSERT Logic
    // =================================================================

    // เช็คว่ามี HN นี้หรือยัง
    $check_stmt = $conn->prepare("SELECT hn FROM tbl_patient WHERE hn = ?");
    $check_stmt->bind_param("s", $hn);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // --- กรณี A: มีแล้ว -> UPDATE (เพิ่ม updated_by, updated_at) ---
        $sql_patient = "UPDATE tbl_patient SET 
                        id_card = ?, flname = ?, birthdate = ?, gender = ?, age = ?,
                        blood_type = ?, address_full = ?, other_id_type = ?, 
                        other_id_number = ?, treatment_scheme = ?,
                        updated_by = ?, updated_at = NOW()  -- << เพิ่มตรงนี้
                        WHERE hn = ?";
        $stmt = $conn->prepare($sql_patient);
        
        // bind_param: เพิ่ม s (updated_by) ก่อนตัวสุดท้าย (hn)
        // เดิม 11 ตัว -> ใหม่ 12 ตัว (ssssssssssss)
        $stmt->bind_param("ssssssssssss", 
            $id_card, $flname, $birthdate, $gender, $age, 
            $blood_type, $address_full, $other_id_type, 
            $other_id_number, $treatment_scheme, 
            $created_by, // updated_by (ใช้ตัวแปรเดียวกัน)
            $hn
        );
    } else {
        // --- กรณี B: ยังไม่มี -> INSERT (เพิ่ม created_by, updated_by และเวลา) ---
        $sql_patient = "INSERT INTO tbl_patient 
                        (hn, id_card, flname, birthdate, gender, age, blood_type, address_full, other_id_type, other_id_number, treatment_scheme, created_by, created_at, updated_by, updated_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, NOW())"; // << เพิ่ม ?
        $stmt = $conn->prepare($sql_patient);
        
        // bind_param: เพิ่ม s 2 ตัวท้าย (created_by, updated_by)
        // เดิม 11 ตัว -> ใหม่ 13 ตัว (sssssssssssss)
        $stmt->bind_param("sssssssssssss", 
            $hn, $id_card, $flname, $birthdate, $gender, $age, 
            $blood_type, $address_full, $other_id_type, $other_id_number, $treatment_scheme,
            $created_by, // created_by
            $updated_by // updated_by
        );
    }

    if (!$stmt->execute()) {
        die("Error saving patient: " . $stmt->error);
    }


    // =================================================================
    // PART 3: เตรียมข้อมูลสำหรับ tbl_stroke_admission
    // =================================================================

    // 3.1 โรคประจำตัว (Checkbox)
    $comorbid_ht = checkVal('comorbid_ht');
    $comorbid_dm = checkVal('comorbid_dm');
    $old_cva = checkVal('old_cva');
    $comorbid_mi = checkVal('comorbid_mi');
    $comorbid_af = checkVal('comorbid_af');
    $comorbid_dlp = checkVal('comorbid_dlp');
    $comorbid_other_text = $_POST['comorbid_other_text'] ?? null;

    // 3.2 สารเสพติด
    $addict_alcohol = checkVal('comorbid_alcohol');
    $addict_smoking = checkVal('comorbid_smoking');

    // 3.3 ประเภทการมา (Arrival Type)
    $arrival_type = $_POST['arrival_type'] ?? null;
    $er_arrival_datetime = combineDateTime($_POST['er_arrival_date'] ?? '', $_POST['er_arrival_time'] ?? '');
    // จัดการเวลา Arrival ตามประเภท
    $refer_from_hospital = $_POST['refer_from_text'] ?? null;
    $refer_arrival_datetime = combineDateTime($_POST['refer_arrival_date'] ?? '', $_POST['refer_arrival_time'] ?? '');
    $ems_first_medical_contact = combineDateTime($_POST['first_medical_contact_date'] ?? '', $_POST['first_medical_contact_time'] ?? '');
    $er_arrival_datetime = combineDateTime($_POST['er_arrival_date'] ?? '', $_POST['er_arrival_time'] ?? '');
    $ipd_ward_name = $_POST['ipd_ward'] ?? null;
    $ipd_onset_datetime = combineDateTime($_POST['ipd_onset_date'] ?? '', $_POST['ipd_onset_time'] ?? '');

    // 3.4 ยาประจำตัว
    $med_anti_platelet = checkVal('med_anti_platelet');
    $med_asa = checkVal('med_asa');
    $med_clopidogrel = checkVal('med_clopidogrel');
    $med_anti_coagulant = checkVal('med_anti_coagulant');
    $med_warfarin = checkVal('med_warfarin');
    $med_noac = checkVal('med_noac');

    // 3.5 Scores & Triage
    $pre_morbid_mrs = $_POST['mrs_score'] ?? 0;
    $fast_track_status = $_POST['fast_track_status'] ?? 'no';
    
    // FAST symptoms
    $symp_face = checkVal('sympDroop'); // เช็คชื่อ id ใน html อีกที (ผมเดาจาก context)
    $symp_arm = checkVal('sympWeakness');
    $symp_speech = checkVal('sympSpeech');
    $symp_vision = checkVal('symp_v');
    $symp_aphasia = checkVal('symp_a2');
    $symp_neglect = checkVal('symp_n');

    // GCS & NIHSS
    $gcs_e = $_POST['gcs_e'] ?? null;
    $gcs_v = $_POST['gcs_v'] ?? null;
    $gcs_m = $_POST['gcs_m'] ?? null;
    $nihss_score = $_POST['nihss'] ?? null;

    // 3.6 เวลาสำคัญ (Timelines) - **สำคัญมาก**
    $onset_datetime = combineDateTime($_POST['onsetTime_onset_date'] ?? '', $_POST['onsetTime_onset_time'] ?? '');
    $departure_datetime = combineDateTime($_POST['departureTime_date'] ?? '', $_POST['departureTime_time'] ?? '');
    $hospital_arrival_datetime = combineDateTime($_POST['arrivalTime_date'] ?? '', $_POST['arrivalTime_time'] ?? '');
    $updated_by = $created_by;
    $created_by = $_SESSION['user_data']['name'] ?? 'System';


// =================================================================
    // PART 4: บันทึกข้อมูล Admission (INSERT หรือ UPDATE)
    // =================================================================

    // รับค่า admission_id เพื่อเช็คว่าเป็น เพิ่มใหม่ หรือ แก้ไข
    $admission_id = $_POST['admission_id'] ?? '';

    if (!empty($admission_id)) {
        // ---------------------------------------------------------
        // กรณี A: มี ID ส่งมา = "แก้ไขข้อมูลเดิม (UPDATE)"
        // ---------------------------------------------------------
        $sql_adm = "UPDATE tbl_stroke_admission SET 
            patient_hn=?, 
            is_ht=?, is_dm=?, is_old_cva=?, is_mi=?, is_af=?, is_dlp=?, is_other_text=?, 
            addict_alcohol=?, addict_smoking=?,
            arrival_type=?, refer_from_hospital=?, refer_arrival_datetime=?, ems_first_medical_contact=?, walk_in_datetime=?, ipd_ward_name=?, ipd_onset_datetime=?,
            med_anti_platelet=?, med_asa=?, med_clopidogrel=?, med_anti_coagulant=?, med_warfarin=?, med_noac=?,
            pre_morbid_mrs=?, fast_track_status=?,
            symp_face=?, symp_arm=?, symp_speech=?, symp_vision=?, symp_aphasia=?, symp_neglect=?,
            gcs_e=?, gcs_v=?, gcs_m=?, nihss_score=?,
            onset_datetime=?, departure_datetime=?, hospital_arrival_datetime=?,
            updated_by=?
            WHERE id=?";

        $stmt_adm = $conn->prepare($sql_adm);

        // Type String: เหมือน Insert แต่ตัด created_by ออก (1 ตัว) และเพิ่ม id ต่อท้าย (1 ตัว) = 40 ตัวเท่าเดิม
        // แต่ตำแหน่ง updated_by จะเลื่อนมา และ id จะมาปิดท้าย
        // s iiiiii s ii sssssss iiiiii i s iiiiii iiii sss s i
        $types = "siiiiiisiisssssssiiiiiiisiiiiiiiiiissssi";

        $stmt_adm->bind_param($types, 
            $hn,                                      
            $comorbid_ht, $comorbid_dm, $old_cva, $comorbid_mi, $comorbid_af, $comorbid_dlp, 
            $comorbid_other_text,                     
            $addict_alcohol, $addict_smoking,         
            $arrival_type, $refer_from_hospital, $refer_arrival_datetime, $ems_first_medical_contact, $er_arrival_datetime, $ipd_ward_name, $ipd_onset_datetime, 
            $med_anti_platelet, $med_asa, $med_clopidogrel, $med_anti_coagulant, $med_warfarin, $med_noac, 
            $pre_morbid_mrs, $fast_track_status,      
            $symp_face, $symp_arm, $symp_speech, $symp_vision, $symp_aphasia, $symp_neglect, 
            $gcs_e, $gcs_v, $gcs_m, $nihss_score,     
            $onset_datetime, $departure_datetime, $hospital_arrival_datetime, 
            $updated_by,   // อัพเดทเฉพาะคนแก้ไข
            $admission_id  // WHERE id (Integer)
        );

    } else {
        // ---------------------------------------------------------
        // กรณี B: ไม่มี ID = "เพิ่มข้อมูลใหม่ (INSERT)"
        // ---------------------------------------------------------
        $sql_adm = "INSERT INTO tbl_stroke_admission (
            patient_hn, 
            is_ht, is_dm, is_old_cva, is_mi, is_af, is_dlp, is_other_text, 
            addict_alcohol, addict_smoking,
            arrival_type, refer_from_hospital, refer_arrival_datetime, ems_first_medical_contact, walk_in_datetime, ipd_ward_name, ipd_onset_datetime,
            med_anti_platelet, med_asa, med_clopidogrel, med_anti_coagulant, med_warfarin, med_noac,
            pre_morbid_mrs, fast_track_status,
            symp_face, symp_arm, symp_speech, symp_vision, symp_aphasia, symp_neglect,
            gcs_e, gcs_v, gcs_m, nihss_score,
            onset_datetime, departure_datetime, hospital_arrival_datetime,
            created_by, updated_by
        ) VALUES (
            ?, 
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?,
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?
        )";

        $stmt_adm = $conn->prepare($sql_adm);

        // Type String: 40 ตัว (เหมือนเดิมเป๊ะ)
        $stmt_adm->bind_param("siiiiiisiisssssssiiiiiiisiiiiiiiiiisssss", 
            $hn,                                      
            $comorbid_ht, $comorbid_dm, $old_cva, $comorbid_mi, $comorbid_af, $comorbid_dlp, 
            $comorbid_other_text,                     
            $addict_alcohol, $addict_smoking,         
            $arrival_type, $refer_from_hospital, $refer_arrival_datetime, $ems_first_medical_contact, $er_arrival_datetime, $ipd_ward_name, $ipd_onset_datetime, 
            $med_anti_platelet, $med_asa, $med_clopidogrel, $med_anti_coagulant, $med_warfarin, $med_noac, 
            $pre_morbid_mrs, $fast_track_status,      
            $symp_face, $symp_arm, $symp_speech, $symp_vision, $symp_aphasia, $symp_neglect, 
            $gcs_e, $gcs_v, $gcs_m, $nihss_score,     
            $onset_datetime, $departure_datetime, $hospital_arrival_datetime, 
            $created_by,                              
            $updated_by                               
        );
    }

    // --- สั่ง Execute ---
    if ($stmt_adm->execute()) {
        
        // ถ้าเป็นการ Insert ให้เอา ID ใหม่มา, ถ้า Update ให้ใช้ ID เดิม
        $redirect_id = !empty($admission_id) ? $admission_id : $conn->insert_id;

        echo json_encode([
            'status' => 'success',
            'message' => 'บันทึกข้อมูลสำเร็จ!',
            'redirect_url' => 'diagnosis_form.php?admission_id=' . $redirect_id
        ]);
        exit;
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Error saving admission: ' . $stmt_adm->error
        ]);
        exit;
    }

} else {
    // ถ้าไม่ได้มาจากการ POST
    header("Location: form.php");
    exit;
}
?>