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

// ฟังก์ชันแปลงวันที่ไทย
function convertThaiDateToMySQL($dateString) {
    if (empty($dateString)) return null;
    $dateString = trim($dateString);

    if (strpos($dateString, '/') !== false) {
        $parts = explode('/', $dateString);
        if (count($parts) == 3) {
            $d = $parts[0];
            $m = $parts[1];
            $y = (int)$parts[2] - 543;
            return "$y-$m-$d";
        }
    }

    $thaiMonths = [
        'มกราคม'=>'01', 'กุมภาพันธ์'=>'02', 'มีนาคม'=>'03', 'เมษายน'=>'04', 'พฤษภาคม'=>'05', 'มิถุนายน'=>'06',
        'กรกฎาคม'=>'07', 'กรกฏาคม'=>'07','สิงหาคม'=>'08', 'กันยายน'=>'09', 'ตุลาคม'=>'10', 'พฤศจิกายน'=>'11', 'ธันวาคม'=>'12',
        'ม.ค.'=>'01', 'ก.พ.'=>'02', 'มี.ค.'=>'03', 'เม.ย.'=>'04', 'พ.ค.'=>'05', 'มิ.ย.'=>'06',
        'ก.ค.'=>'07', 'ส.ค.'=>'08', 'ก.ย.'=>'09', 'ต.ค.'=>'10', 'พ.ย.'=>'11', 'ธ.ค.'=>'12'
    ];

    $parts = preg_split('/\s+/', $dateString);
    if (count($parts) == 3) {
        $d = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
        $mStr = $parts[1];
        $y = (int)$parts[2] - 543;
        if (isset($thaiMonths[$mStr])) {
            $m = $thaiMonths[$mStr];
            return "$y-$m-$d";
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // =================================================================
    // PART 1: รับค่าและเตรียมข้อมูลสำหรับ tbl_patient
    // =================================================================
    $user_data = $_SESSION['user_data'] ?? [];
    $created_by = $user_data['HR_FNAME'] ?? 'System';
    $updated_by = $created_by;
    
    $hn = $_POST['hn'];
    $id_card = $_POST['id_card'] ?? null;
    $flname = $_POST['flname'] ?? null;

    $birthdate = !empty($_POST['birthdate']) ? $_POST['birthdate'] : null;
    if (empty($birthdate) || $birthdate == '0000-00-00') {
        if (!empty($_POST['birthdate_show'])) {
            $birthdate = convertThaiDateToMySQL($_POST['birthdate_show']);
        }
    }
    if (empty($birthdate)) $birthdate = null;

    $gender = $_POST['gender'] ?? null;
    $age = !empty($_POST['age']) ? (int)$_POST['age'] : 0;
    
    $blood_type = $_POST['blood_type'] ?? null;
    $address_full = $_POST['address_full'] ?? null; 
    
    $other_id_type = $_POST['card_type'] ?? null; 
    $other_id_number = null;
    if ($other_id_type === 'Alien') {
        $other_id_number = $_POST['alien_number'] ?? null;
    } elseif ($other_id_type === 'Passport') {
        $other_id_number = $_POST['passport_number'] ?? null;
    } else {
        $other_id_type = null; 
    }

    $treatment_scheme = $_POST['btnTreatmentRight'] ?? null;

    // =================================================================
    // PART 2: บันทึกข้อมูลผู้ป่วย (tbl_patient)
    // =================================================================
    $check_stmt = $conn->prepare("SELECT hn FROM tbl_patient WHERE hn = ?");
    $check_stmt->bind_param("s", $hn);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $sql_patient = "UPDATE tbl_patient SET 
                        id_card = ?, flname = ?, birthdate = ?, gender = ?, age = ?,
                        blood_type = ?, address_full = ?, other_id_type = ?, 
                        other_id_number = ?, treatment_scheme = ?,
                        updated_by = ?, updated_at = NOW()
                        WHERE hn = ?";
        $stmt = $conn->prepare($sql_patient);
        $stmt->bind_param("ssssssssssss", 
            $id_card, $flname, $birthdate, $gender, $age, 
            $blood_type, $address_full, $other_id_type, 
            $other_id_number, $treatment_scheme, 
            $updated_by, $hn
        );
    } else {
        $sql_patient = "INSERT INTO tbl_patient 
                        (hn, id_card, flname, birthdate, gender, age, blood_type, address_full, other_id_type, other_id_number, treatment_scheme, created_by, created_at, updated_by, updated_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, NOW())";
        $stmt = $conn->prepare($sql_patient);
        $stmt->bind_param("sssssssssssss", 
            $hn, $id_card, $flname, $birthdate, $gender, $age, 
            $blood_type, $address_full, $other_id_type, $other_id_number, $treatment_scheme,
            $created_by, $updated_by
        );
    }

    if (!$stmt->execute()) {
        die("Error saving patient: " . $stmt->error);
    }

    // =================================================================
    // PART 3: เตรียมข้อมูลสำหรับ tbl_stroke_admission
    // =================================================================

    // 3.1 โรคประจำตัว
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
    $addict_kratom = checkVal('comorbid_kratom');
    $addict_cannabis = checkVal('comorbid_cannabis');
    $addict_crystal_meth = checkVal('comorbid_crystal_meth');
    $addict_yaba = checkVal('comorbid_yaba');

    // 3.3 Arrival
    $arrival_type = $_POST['arrival_type'] ?? null;
    $refer_from_hospital = $_POST['refer_from_text'] ?? null;
    $transfer_departure_datetime = combineDateTime($_POST['arrivalTime'] ?? '', $_POST['arrivalTime_time'] ?? '');
    $refer_arrival_datetime = combineDateTime($_POST['refer_arrival_date'] ?? '', $_POST['refer_arrival_time'] ?? '');
    $ems_first_medical_contact = combineDateTime($_POST['first_medical_contact_date'] ?? '', $_POST['first_medical_contact_time'] ?? '');
    $er_arrival_datetime = combineDateTime($_POST['er_arrival_date'] ?? '', $_POST['er_arrival_time'] ?? '');
    $ipd_ward_name = $_POST['ipd_ward'] ?? null;
    $ipd_onset_datetime = combineDateTime($_POST['ipd_onset_date'] ?? '', $_POST['ipd_onset_time'] ?? '');

    // 3.4 ยาประจำตัว
    $med_anti_platelet = checkVal('med_anti_platelet');
    $med_asa = checkVal('med_asa');
    $med_clopidogrel = checkVal('med_clopidogrel');
    $med_cilostazol = checkVal('med_cilostazol');
    $med_ticaqrelor = checkVal('med_ticaqrelor');
    $med_anti_coagulant = checkVal('med_anti_coagulant');
    $med_warfarin = checkVal('med_warfarin');
    $med_noac = checkVal('med_noac');

    // 3.5 Scores & Triage
    $pre_morbid_mrs = $_POST['mrs_score'] ?? 0;
    $fast_track_status = $_POST['fast_track_status'] ?? 'no';
    
    $symp_face = checkVal('sympDroop'); 
    $symp_arm = checkVal('sympWeakness');
    $symp_speech = checkVal('sympSpeech');
    $symp_vision = checkVal('symp_v');
    $symp_aphasia = checkVal('symp_a2');
    $symp_neglect = checkVal('symp_n');

    $gcs_e = $_POST['gcs_e'] ?? null;
    $gcs_v = $_POST['gcs_v'] ?? null;
    $gcs_m = $_POST['gcs_m'] ?? null;
    $nihss_score = ($_POST['nihss'] !== '') ? $_POST['nihss'] : null;

    // 3.6 Timelines
    $onset_datetime = combineDateTime($_POST['onsetTime_onset_date'] ?? '', $_POST['onsetTime_onset_time'] ?? '');
    $hospital_arrival_datetime = combineDateTime($_POST['arrivalTime_date'] ?? '', $_POST['arrivalTime_time'] ?? '');

    // 3.7 Time Process of Care
    $time_onset_to_refer_min = !empty($_POST['time_onset_to_refer_min']) ? $_POST['time_onset_to_refer_min'] : null;
    $time_onset_to_hatyai_min = !empty($_POST['time_onset_to_hatyai_min']) ? $_POST['time_onset_to_hatyai_min'] : null;

    // =================================================================
    // PART 4: บันทึกข้อมูล Admission (CORRECTED Type Strings - Final)
    // =================================================================
    $admission_id = $_POST['admission_id'] ?? '';
    
    // คำอธิบาย Type String 48 ตัวอักษร (ฉบับแก้ไขถูกต้องที่สุด):
    // 1. s (HN)
    // 2. iiiiiis (6 โรค + Text)
    // 3. iiiiiis (6 สารเสพติด + ArrivalType)
    // 4. sssssss (7 Arrival details - แก้ไขเหลือ 7 ตัวอักษร เพราะ arrival_type อยู่ในกลุ่มก่อนหน้าแล้ว)
    // 5. iiiiiiiis (8 Meds + MRS + Fast)
    // 6. iiiiiiiiii (6 Symp + 4 GCS/NIHSS)
    // 7. ss (Onset + HospArr)
    // 8. ii (TimeRefer + TimeHat)
    // 9. ss (Created + Updated)
    
    $types_insert = "siiiiiisiiiiiisssssssiiiiiiiiisiiiiiiiiiiissiiss"; 
    $types_update = "siiiiiisiiiiiisssssssiiiiiiiiisiiiiiiiiiiissiisi";

    if (!empty($admission_id)) {
        // --- UPDATE ---
        $sql_adm = "UPDATE tbl_stroke_admission SET 
            patient_hn=?, 
            is_ht=?, is_dm=?, is_old_cva=?, is_mi=?, is_af=?, is_dlp=?, is_other_text=?, 
            addict_alcohol=?, addict_smoking=?, comorbid_kratom=?, comorbid_cannabis=?, comorbid_crystal_meth=?, comorbid_yaba=?,
            arrival_type=?, refer_from_hospital=?, transfer_departure_datetime=? ,refer_arrival_datetime=?, ems_first_medical_contact=?, walk_in_datetime=?, ipd_ward_name=?, ipd_onset_datetime=?,
            med_anti_platelet=?, med_asa=?, med_clopidogrel=?, med_cilostazol=?, med_ticaqrelor=?, med_anti_coagulant=?, med_warfarin=?, med_noac=?,
            pre_morbid_mrs=?, fast_track_status=?,
            symp_face=?, symp_arm=?, symp_speech=?, symp_vision=?, symp_aphasia=?, symp_neglect=?,
            gcs_e=?, gcs_v=?, gcs_m=?, nihss_score=?,
            onset_datetime=?, hospital_arrival_datetime=?,
            time_onset_to_refer_min=?, time_onset_to_hatyai_min=?,
            updated_by=?
            WHERE id=?";

        $stmt_adm = $conn->prepare($sql_adm);
        if ($stmt_adm === false) { die('Prepare Failed (Update): ' . $conn->error); }

        $stmt_adm->bind_param($types_update, 
            $hn,                                      
            $comorbid_ht, $comorbid_dm, $old_cva, $comorbid_mi, $comorbid_af, $comorbid_dlp, 
            $comorbid_other_text,                     
            $addict_alcohol, $addict_smoking, $addict_kratom, $addict_cannabis, $addict_crystal_meth, $addict_yaba,        
            $arrival_type, $refer_from_hospital, $transfer_departure_datetime, $refer_arrival_datetime, $ems_first_medical_contact, $er_arrival_datetime, $ipd_ward_name, $ipd_onset_datetime, 
            $med_anti_platelet, $med_asa, $med_clopidogrel, $med_cilostazol, $med_ticaqrelor, $med_anti_coagulant, $med_warfarin, $med_noac, 
            $pre_morbid_mrs, $fast_track_status,      
            $symp_face, $symp_arm, $symp_speech, $symp_vision, $symp_aphasia, $symp_neglect, 
            $gcs_e, $gcs_v, $gcs_m, $nihss_score,     
            $onset_datetime, $hospital_arrival_datetime,
            $time_onset_to_refer_min, $time_onset_to_hatyai_min,
            $updated_by,   
            $admission_id
        );

    } else {
        // --- INSERT ---
        $sql_adm = "INSERT INTO tbl_stroke_admission (
            patient_hn, 
            is_ht, is_dm, is_old_cva, is_mi, is_af, is_dlp, is_other_text, 
            addict_alcohol, addict_smoking, comorbid_kratom, comorbid_cannabis, comorbid_crystal_meth, comorbid_yaba,
            arrival_type, refer_from_hospital, transfer_departure_datetime ,refer_arrival_datetime, ems_first_medical_contact, walk_in_datetime, ipd_ward_name, ipd_onset_datetime,
            med_anti_platelet, med_asa, med_clopidogrel, med_cilostazol, med_ticaqrelor, med_anti_coagulant, med_warfarin, med_noac,
            pre_morbid_mrs, fast_track_status,
            symp_face, symp_arm, symp_speech, symp_vision, symp_aphasia, symp_neglect,
            gcs_e, gcs_v, gcs_m, nihss_score,
            onset_datetime, hospital_arrival_datetime,
            time_onset_to_refer_min, time_onset_to_hatyai_min,
            created_by, updated_by
        ) VALUES (
            ?, 
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?,
            ?, ?,
            ?, ?
        )";

        $stmt_adm = $conn->prepare($sql_adm);
        if ($stmt_adm === false) { die('Prepare Failed (Insert): ' . $conn->error); }

        $stmt_adm->bind_param($types_insert, 
            $hn,                                      
            $comorbid_ht, $comorbid_dm, $old_cva, $comorbid_mi, $comorbid_af, $comorbid_dlp, 
            $comorbid_other_text,                     
            $addict_alcohol, $addict_smoking, $addict_kratom, $addict_cannabis, $addict_crystal_meth, $addict_yaba,         
            $arrival_type, $refer_from_hospital, $transfer_departure_datetime, $refer_arrival_datetime, $ems_first_medical_contact, $er_arrival_datetime, $ipd_ward_name, $ipd_onset_datetime, 
            $med_anti_platelet, $med_asa, $med_clopidogrel, $med_cilostazol, $med_ticaqrelor, $med_anti_coagulant, $med_warfarin, $med_noac, 
            $pre_morbid_mrs, $fast_track_status,      
            $symp_face, $symp_arm, $symp_speech, $symp_vision, $symp_aphasia, $symp_neglect, 
            $gcs_e, $gcs_v, $gcs_m, $nihss_score,     
            $onset_datetime, $hospital_arrival_datetime, 
            $time_onset_to_refer_min, $time_onset_to_hatyai_min,
            $created_by, $updated_by                               
        );
    }

    if ($stmt_adm->execute()) {
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
    header("Location: form.php");
    exit;
}
?>