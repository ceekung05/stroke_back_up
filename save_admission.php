<?php
session_start();
require_once 'connectdb.php';

// ตั้งค่า Header เป็น JSON เพื่อให้ JS รับค่าได้ถูกต้องเสมอ
header('Content-Type: application/json');

// เปิด Error Reporting เพื่อให้เห็นปัญหาชัดเจน
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid Request Method');
    }

    // --- Helper Functions ---
    function checkVal($field_name) { return isset($_POST[$field_name]) ? 1 : 0; }

    function combineDateTime($date, $time) {
        if (!empty($date) && !empty($time)) { 
            // กรณีที่ 1: ถ้าส่งมาเป็น 24:00 ให้ปัดเป็น 00:00 ของวันถัดไป
            if ($time == '24:00') {
                $time = '00:00:00';
                // บวกวันที่เพิ่ม 1 วัน
                $date = date('Y-m-d', strtotime($date . ' +1 day'));
            }
            return $date . ' ' . $time; 
        }
        return null;
    }

    function convertThaiDateToMySQL($dateString) {
        if (empty($dateString)) return null;
        $dateString = trim($dateString);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) return $dateString;
        
        if (strpos($dateString, '/') !== false) {
            $parts = explode('/', $dateString);
            if (count($parts) == 3) {
                $d = $parts[0]; $m = $parts[1]; $y = (int)$parts[2] - 543;
                return "$y-$m-$d";
            }
        }
        return null;
    }

    // ==========================================
    // 1. รับค่าและจัดการข้อมูลผู้ป่วย (Patient)
    // ==========================================
    $user_data = $_SESSION['user_data'] ?? [];
    $action_by = $user_data['HR_FNAME'] ?? 'System'; 
    
    $hn = $_POST['hn'] ?? '';
    if(empty($hn)) throw new Exception("กรุณาระบุ HN");

    $id_card = $_POST['id_card'] ?? null;
    $flname = $_POST['flname'] ?? null;
    
    $birthdate = !empty($_POST['birthdate']) ? $_POST['birthdate'] : null;
    if (empty($birthdate) || $birthdate == '0000-00-00') {
        if (!empty($_POST['birthdate_show'])) { 
            $birthdate = convertThaiDateToMySQL($_POST['birthdate_show']); 
        }
    }
    
    $gender = $_POST['gender'] ?? null;
    $age = !empty($_POST['age']) ? (int)$_POST['age'] : 0;
    $blood_type = $_POST['blood_type'] ?? null;
    $phone_number = $_POST['phone_number'] ?? null;

    $subdistrict = $_POST['subdistrict'] ?? null;
    $district    = $_POST['district'] ?? null;
    $province    = $_POST['province'] ?? null;
    $zipcode     = $_POST['zipcode'] ?? null;
    $address_full = $_POST['address_full'] ?? null;
    
    $other_id_type = $_POST['card_type'] ?? null; 
    $other_id_number = ($other_id_type === 'Alien') ? ($_POST['alien_number']??null) : (($other_id_type === 'Passport') ? ($_POST['passport_number']??null) : null);
    if(!in_array($other_id_type, ['Alien', 'Passport'])) $other_id_type = null;
    
    $treatment_scheme = $_POST['btnTreatmentRight'] ?? null;

    // Check existing patient
    $check_stmt = $conn->prepare("SELECT hn FROM tbl_patient WHERE hn = ?");
    $check_stmt->bind_param("s", $hn);
    $check_stmt->execute();
    $patient_exists = $check_stmt->get_result()->num_rows > 0;
    $check_stmt->close();

    if ($patient_exists) {
        $sql_patient = "UPDATE tbl_patient SET 
                        id_card=?, flname=?, birthdate=?, gender=?, age=?, blood_type=?, phone_number=?, 
                        subdistrict=?, district=?, province=?, zipcode=?, address_full=?, 
                        other_id_type=?, other_id_number=?, treatment_scheme=?, 
                        updated_by=?, updated_at=NOW() 
                        WHERE hn=?";
        $stmt = $conn->prepare($sql_patient);
        $stmt->bind_param("ssssississsssssss", 
            $id_card, $flname, $birthdate, $gender, $age, $blood_type, $phone_number,
            $subdistrict, $district, $province, $zipcode, $address_full,
            $other_id_type, $other_id_number, $treatment_scheme,
            $action_by, $hn
        );
    } else {
        $sql_patient = "INSERT INTO tbl_patient 
                        (hn, id_card, flname, birthdate, gender, age, blood_type, phone_number, 
                         subdistrict, district, province, zipcode, address_full, 
                         other_id_type, other_id_number, treatment_scheme, 
                         created_by, created_at, updated_by, updated_at) 
                        VALUES 
                        (?, ?, ?, ?, ?, ?, ?, ?, 
                         ?, ?, ?, ?, ?, 
                         ?, ?, ?, 
                         ?, NOW(), ?, NOW())";
        $stmt = $conn->prepare($sql_patient);
        $stmt->bind_param("ssssississssssssss", 
            $hn, $id_card, $flname, $birthdate, $gender, $age, $blood_type, $phone_number,
            $subdistrict, $district, $province, $zipcode, $address_full, 
            $other_id_type, $other_id_number, $treatment_scheme, 
            $action_by, $action_by
        );
    }
    $stmt->execute();
    $stmt->close();

    // ==========================================
    // 2. รับค่าและจัดการข้อมูล Admission
    // ==========================================
    
    // Disease
    $comorbid_ht = checkVal('comorbid_ht');
    $comorbid_dm = checkVal('comorbid_dm');
    $old_cva = checkVal('old_cva');
    $comorbid_mi = checkVal('comorbid_mi');
    $comorbid_af = checkVal('comorbid_af');
    $comorbid_dlp = checkVal('comorbid_dlp');
    $comorbid_other_text = $_POST['comorbid_other_text'] ?? null;

    // Addictions
    $addict_alcohol = checkVal('addict_alcohol');
    $addict_smoking = checkVal('addict_smoking');
    $comorbid_kratom = checkVal('comorbid_kratom');
    $comorbid_cannabis = checkVal('comorbid_cannabis');
    $comorbid_crystal_meth = checkVal('comorbid_crystal_meth');
    $comorbid_yaba = checkVal('comorbid_yaba');

    // Arrival
    $arrival_type = $_POST['arrival_type'] ?? null;
    $refer_from_hospital = $_POST['refer_from_text'] ?? null;
    
    $transfer_departure_datetime = combineDateTime($_POST['arrivalTime'] ?? '', $_POST['refer_departure_time'] ?? ''); 
    $refer_arrival_datetime = combineDateTime($_POST['refer_arrival_date'] ?? '', $_POST['refer_arrival_time'] ?? '');
    $ems_first_medical_contact = combineDateTime($_POST['first_medical_contact_date'] ?? '', $_POST['first_medical_contact_time'] ?? '');
    $er_arrival_datetime = combineDateTime($_POST['er_arrival_date'] ?? '', $_POST['er_arrival_time'] ?? '');
    $ipd_onset_datetime = combineDateTime($_POST['ipd_onset_date'] ?? '', $_POST['ipd_onset_time'] ?? '');
    $ipd_ward_name = $_POST['ipd_ward'] ?? null;

    // Meds
    $med_anti_platelet = checkVal('med_anti_platelet');
    $med_asa = checkVal('med_asa');
    $med_clopidogrel = checkVal('med_clopidogrel');
    $med_cilostazol = checkVal('med_cilostazol');
    $med_ticaqrelor = checkVal('med_ticaqrelor');
    $med_anti_coagulant = checkVal('med_anti_coagulant');
    $med_warfarin = checkVal('med_warfarin');
    $med_noac = checkVal('med_noac');

    $pre_morbid_mrs = $_POST['mrs_score'] ?? null; 
    $fast_track_status = $_POST['fast_track_status'] ?? 'no';
    
    // Symp
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

    $onset_datetime = combineDateTime($_POST['onsetTime_onset_date'] ?? '', $_POST['onsetTime_onset_time'] ?? '');
    $hospital_arrival_datetime = combineDateTime($_POST['arrivalTime_date'] ?? '', $_POST['arrivalTime_time'] ?? '');

    $time_onset_to_refer_min = !empty($_POST['time_onset_to_refer_min']) ? $_POST['time_onset_to_refer_min'] : null;
    $time_onset_to_hatyai_min = !empty($_POST['time_onset_to_hatyai_min']) ? $_POST['time_onset_to_hatyai_min'] : null;
    $time_refer_hospital_min = !empty($_POST['time_refer_hospital_min']) ? $_POST['time_refer_hospital_min'] : null;
    $time_refer_travel_min = !empty($_POST['time_refer_travel_min']) ? $_POST['time_refer_travel_min'] : null;

    $admission_id = $_POST['admission_id'] ?? '';

    // New Fields
    $patient_an = $_POST['patient_an'] ?? '';
    $date_admit = !empty($_POST['date_admit']) ? $_POST['date_admit'] : null;
    
    // ** แก้ไข Type String ให้ถูกต้องเป๊ะๆ (50 ตัวอักษรพื้นฐาน) **
    // 1. HN/AN/Date -> sss (3)
    // 2. Diseases(6)+Text -> iiiiiis (7)
    // 3. Addicts(6)+ArrivalType -> iiiiiis (7)
    // 4. ArrivalDetails(7 vars: referFrom, TransDep, RefArr, Ems, ErArr, Ward, IpdOnset) -> sssssss (7) [แก้จาก 8 เป็น 7]
    // 5. Meds(8)+Mrs -> iiiiiiiii (9)
    // 6. FastTrack -> s (1)
    // 7. Symp(6)+GCS(3)+NIHSS -> iiiiiiiiii (10)
    // 8. Onset+HospArr -> ss (2)
    // 9. Times(4) -> iiii (4)
    // รวม 3+7+7+7+9+1+10+2+4 = 50 ตัว
    
    $types_string = "sssiiiiiisiiiiiisssssssiiiiiiiiisiiiiiiiiiiissiiii";

    if (!empty($admission_id)) {
        // --- UPDATE ---
        $sql_adm = "UPDATE tbl_stroke_admission SET 
            patient_hn=?, patient_an=?, date_admit=?, 
            is_ht=?, is_dm=?, is_old_cva=?, is_mi=?, is_af=?, is_dlp=?, is_other_text=?, 
            addict_alcohol=?, addict_smoking=?, comorbid_kratom=?, comorbid_cannabis=?, comorbid_crystal_meth=?, comorbid_yaba=?,
            arrival_type=?, refer_from_hospital=?, transfer_departure_datetime=? ,refer_arrival_datetime=?, ems_first_medical_contact=?, walk_in_datetime=?, ipd_ward_name=?, ipd_onset_datetime=?,
            med_anti_platelet=?, med_asa=?, med_clopidogrel=?, med_cilostazol=?, med_ticaqrelor=?, med_anti_coagulant=?, med_warfarin=?, med_noac=?,
            pre_morbid_mrs=?, fast_track_status=?,
            symp_face=?, symp_arm=?, symp_speech=?, symp_vision=?, symp_aphasia=?, symp_neglect=?,
            gcs_e=?, gcs_v=?, gcs_m=?, nihss_score=?,
            onset_datetime=?, hospital_arrival_datetime=?,
            time_onset_to_refer_min=?, time_onset_to_hatyai_min=?,
            time_refer_hospital_min=?, time_refer_travel_min=?,
            updated_by=?
            WHERE id=?";

        $stmt_adm = $conn->prepare($sql_adm);
        
        // Param count: 50 + 2 (updated_by, id) = 52
        // Types: 50 chars + "si" = 52 chars
        $stmt_adm->bind_param($types_string . "si", 
            $hn, $patient_an, $date_admit,
            $comorbid_ht, $comorbid_dm, $old_cva, $comorbid_mi, $comorbid_af, $comorbid_dlp, $comorbid_other_text,                     
            $addict_alcohol, $addict_smoking, $comorbid_kratom, $comorbid_cannabis, $comorbid_crystal_meth, $comorbid_yaba,        
            $arrival_type, $refer_from_hospital, $transfer_departure_datetime, $refer_arrival_datetime, $ems_first_medical_contact, $er_arrival_datetime, $ipd_ward_name, $ipd_onset_datetime, 
            $med_anti_platelet, $med_asa, $med_clopidogrel, $med_cilostazol, $med_ticaqrelor, $med_anti_coagulant, $med_warfarin, $med_noac, 
            $pre_morbid_mrs, $fast_track_status,      
            $symp_face, $symp_arm, $symp_speech, $symp_vision, $symp_aphasia, $symp_neglect, 
            $gcs_e, $gcs_v, $gcs_m, $nihss_score,     
            $onset_datetime, $hospital_arrival_datetime,
            $time_onset_to_refer_min, $time_onset_to_hatyai_min,
            $time_refer_hospital_min, $time_refer_travel_min,
            $action_by, $admission_id
        );

    } else {
        // --- INSERT ---
        $sql_adm = "INSERT INTO tbl_stroke_admission (
            patient_hn, patient_an, date_admit,
            is_ht, is_dm, is_old_cva, is_mi, is_af, is_dlp, is_other_text, 
            addict_alcohol, addict_smoking, comorbid_kratom, comorbid_cannabis, comorbid_crystal_meth, comorbid_yaba,
            arrival_type, refer_from_hospital, transfer_departure_datetime ,refer_arrival_datetime, ems_first_medical_contact, walk_in_datetime, ipd_ward_name, ipd_onset_datetime,
            med_anti_platelet, med_asa, med_clopidogrel, med_cilostazol, med_ticaqrelor, med_anti_coagulant, med_warfarin, med_noac,
            pre_morbid_mrs, fast_track_status,
            symp_face, symp_arm, symp_speech, symp_vision, symp_aphasia, symp_neglect,
            gcs_e, gcs_v, gcs_m, nihss_score,
            onset_datetime, hospital_arrival_datetime,
            time_onset_to_refer_min, time_onset_to_hatyai_min,
            time_refer_hospital_min, time_refer_travel_min,
            created_by, updated_by
        ) VALUES (
            ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?,
            ?, ?,
            ?, ?,
            ?, ?
        )";

        $stmt_adm = $conn->prepare($sql_adm);
        
        // Param count: 50 + 2 (created_by, updated_by) = 52
        // Types: 50 chars + "ss" = 52 chars
        $stmt_adm->bind_param($types_string . "ss", 
            $hn, $patient_an, $date_admit,
            $comorbid_ht, $comorbid_dm, $old_cva, $comorbid_mi, $comorbid_af, $comorbid_dlp, $comorbid_other_text,                     
            $addict_alcohol, $addict_smoking, $comorbid_kratom, $comorbid_cannabis, $comorbid_crystal_meth, $comorbid_yaba,         
            $arrival_type, $refer_from_hospital, $transfer_departure_datetime, $refer_arrival_datetime, $ems_first_medical_contact, $er_arrival_datetime, $ipd_ward_name, $ipd_onset_datetime, 
            $med_anti_platelet, $med_asa, $med_clopidogrel, $med_cilostazol, $med_ticaqrelor, $med_anti_coagulant, $med_warfarin, $med_noac, 
            $pre_morbid_mrs, $fast_track_status,      
            $symp_face, $symp_arm, $symp_speech, $symp_vision, $symp_aphasia, $symp_neglect, 
            $gcs_e, $gcs_v, $gcs_m, $nihss_score,     
            $onset_datetime, $hospital_arrival_datetime, 
            $time_onset_to_refer_min, $time_onset_to_hatyai_min,
            $time_refer_hospital_min, $time_refer_travel_min,
            $action_by, $action_by                               
        );
    }

    $stmt_adm->execute();
    $redirect_id = !empty($admission_id) ? $admission_id : $conn->insert_id;
    $stmt_adm->close();
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'บันทึกข้อมูลสำเร็จ!', 
        'redirect_url' => 'diagnosis_form.php?admission_id=' . $redirect_id
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
} catch (mysqli_sql_exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>