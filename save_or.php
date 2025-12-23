<?php
session_start();
require_once 'connectdb.php'; 

// ฟังก์ชันรวม Date + Time
function combineDateTime($date, $time) {
    if (empty($date)) return null;
    if (empty($time)) return $date . ' 00:00:00';
    return $date . ' ' . $time;
}

// ฟังก์ชันแปลง Checkbox
function checkVal($field_name) {
    return isset($_POST[$field_name]) ? 1 : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // เปิด Error Reporting เพื่อช่วยหาจุดผิดพลาด
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $admission_id = $_POST['admission_id'] ?? '';

        if (empty($admission_id)) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบ Admission ID']);
            exit;
        }

        // 1. ประเภทหัตถการ
        $procedure_type = $_POST['procType'] ?? null;

        // A. MT Values
        $mt_anesthesia_datetime = combineDateTime($_POST['mt_anesthesia_date'] ?? '', $_POST['mt_anesthesia_time'] ?? '');
        $mt_puncture_datetime = combineDateTime($_POST['mt_puncture_date'] ?? '', $_POST['mt_puncture_time'] ?? '');
        $mt_recanalization_datetime = combineDateTime($_POST['mt_recanalization_date'] ?? '', $_POST['mt_recanalization_time'] ?? '');

        $mt_occlusion_vessel = $_POST['mt_occlusion_vessel'] ?? null;
        $mt_tici_score = $_POST['mt_tici_score'] ?? null;
        $mt_procedure_technique = $_POST['mt_procedure_technique'] ?? null;
        $mt_pass_count = $_POST['mt_pass_count'] ?? null;

        $mt_med_integrilin = checkVal('mt_med_integrilin');
        $mt_integrilin_bolus = $_POST['mt_integrilin_bolus'] ?? null;
        $mt_integrilin_drip = $_POST['mt_integrilin_drip'] ?? null;
        
        $mt_med_nimodipine = checkVal('mt_med_nimodipine');
        $mt_nimodipine_bolus = $_POST['mt_nimodipine_bolus'] ?? null;
        $mt_nimodipine_drip = $_POST['mt_nimodipine_drip'] ?? null;

        $mt_xray_dose = $_POST['mt_xray_dose'] ?? null;
        $mt_flu_time = $_POST['mt_flu_time'] ?? null;
        $mt_cone_beam_ct = (isset($_POST['mt_cone_beam_ct']) && $_POST['mt_cone_beam_ct'] == 'yes') ? 1 : 0;
        $mt_cone_beam_ct_details = $_POST['mt_cone_beam_ct_details'] ?? null;

        // B. Hemo Values
        $hemo_location = $_POST['hemo_location'] ?? null;
        $hemo_volume_cc = $_POST['hemo_volume_cc'] ?? null;
        
        $hemo_proc_craniotomy = checkVal('hemo_proc_craniotomy');
        $hemo_proc_craniectomy = checkVal('hemo_proc_craniectomy');
        $hemo_proc_ventriculostomy = checkVal('hemo_proc_ventriculostomy');

        // Common
        $complication_details = $_POST['complication_details'] ?? null;
        $current_user = $_SESSION['user_data']['hr_fname'] ?? 'System';

        $sql = "INSERT INTO tbl_or_procedure (
                    admission_id, procedure_type,
                    mt_anesthesia_datetime, mt_puncture_datetime, mt_recanalization_datetime,
                    mt_occlusion_vessel, mt_tici_score, mt_procedure_technique, mt_pass_count,
                    mt_med_integrilin, mt_integrilin_bolus, mt_integrilin_drip,
                    mt_med_nimodipine, mt_nimodipine_bolus, mt_nimodipine_drip,
                    mt_xray_dose, mt_flu_time, mt_cone_beam_ct, mt_cone_beam_ct_details,
                    hemo_location, hemo_volume_cc,
                    hemo_proc_craniotomy, hemo_proc_craniectomy, hemo_proc_ventriculostomy,
                    complication_details, 
                    created_by, created_at, updated_by, updated_at
                ) VALUES (
                    ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?,
                    ?, ?, ?,
                    ?, 
                    ?, NOW(), ?, NOW()
                )
                ON DUPLICATE KEY UPDATE
                    procedure_type = VALUES(procedure_type),
                    mt_anesthesia_datetime = VALUES(mt_anesthesia_datetime),
                    mt_puncture_datetime = VALUES(mt_puncture_datetime),
                    mt_recanalization_datetime = VALUES(mt_recanalization_datetime),
                    mt_occlusion_vessel = VALUES(mt_occlusion_vessel),
                    mt_tici_score = VALUES(mt_tici_score),
                    mt_procedure_technique = VALUES(mt_procedure_technique),
                    mt_pass_count = VALUES(mt_pass_count),
                    mt_med_integrilin = VALUES(mt_med_integrilin),
                    mt_integrilin_bolus = VALUES(mt_integrilin_bolus),
                    mt_integrilin_drip = VALUES(mt_integrilin_drip),
                    mt_med_nimodipine = VALUES(mt_med_nimodipine),
                    mt_nimodipine_bolus = VALUES(mt_nimodipine_bolus),
                    mt_nimodipine_drip = VALUES(mt_nimodipine_drip),
                    mt_xray_dose = VALUES(mt_xray_dose),
                    mt_flu_time = VALUES(mt_flu_time),
                    mt_cone_beam_ct = VALUES(mt_cone_beam_ct),
                    mt_cone_beam_ct_details = VALUES(mt_cone_beam_ct_details),
                    hemo_location = VALUES(hemo_location),
                    hemo_volume_cc = VALUES(hemo_volume_cc),
                    hemo_proc_craniotomy = VALUES(hemo_proc_craniotomy),
                    hemo_proc_craniectomy = VALUES(hemo_proc_craniectomy),
                    hemo_proc_ventriculostomy = VALUES(hemo_proc_ventriculostomy),
                    complication_details = VALUES(complication_details),
                    updated_by = VALUES(updated_by),
                    updated_at = NOW()
                ";

        $stmt = $conn->prepare($sql);

        // *** ตรวจสอบจำนวนตัวแปรให้ตรงกับเครื่องหมาย ? ใน SQL (27 ตัว) ***
        // i=1, s=1, sss=3, ssss=4, idd=3, idd=3, ddis=4, sd=2, iii=3, s=1, s=1, s=1 
        // รวมทั้งหมด 27 ตัว ถูกต้อง
        $stmt->bind_param("issssssssiddiddddissiiissss", 
            $admission_id, 
            $procedure_type,
            $mt_anesthesia_datetime, 
            $mt_puncture_datetime, 
            $mt_recanalization_datetime,
            $mt_occlusion_vessel, 
            $mt_tici_score, 
            $mt_procedure_technique, 
            $mt_pass_count,
            $mt_med_integrilin, 
            $mt_integrilin_bolus, 
            $mt_integrilin_drip,
            $mt_med_nimodipine, 
            $mt_nimodipine_bolus, 
            $mt_nimodipine_drip,
            $mt_xray_dose, 
            $mt_flu_time, 
            $mt_cone_beam_ct, 
            $mt_cone_beam_ct_details,
            $hemo_location, 
            $hemo_volume_cc,
            $hemo_proc_craniotomy, 
            $hemo_proc_craniectomy, 
            $hemo_proc_ventriculostomy,
            $complication_details, 
            $current_user, // created_by
            $current_user  // updated_by
        );

        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'บันทึกข้อมูล OR สำเร็จ!',
                'redirect_url' => 'ward.php?admission_id=' . $admission_id
            ]);
        } else {
            throw new Exception($stmt->error);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>