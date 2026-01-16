<?php
session_start();
require_once 'connectdb.php';

// ฟังก์ชันรวม Date + Time
function combineDateTime($date, $time)
{
    if (!empty($date) && !empty($time)) {
        return $date . ' ' . $time;
    }
    return null;
}

// ฟังก์ชันแปลง Checkbox
function checkVal($field_name)
{
    return isset($_POST[$field_name]) ? 1 : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $admission_id = $_POST['admission_id'] ?? '';
        if (empty($admission_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing Admission ID']);
            exit;
        }

        // 1. รับค่าเวลา
        $consult_neuro_datetime = combineDateTime($_POST['consult_neuro_date'] ?? '', $_POST['consult_neuro_time_input'] ?? '');
        $ctnc_datetime = combineDateTime($_POST['ctncDate'] ?? '', $_POST['ctncTime_input'] ?? '');
        $cta_datetime = combineDateTime($_POST['ctaTime'] ?? '', $_POST['ctaTime_input'] ?? '');
        $mri_datetime = combineDateTime($_POST['mriTime'] ?? '', $_POST['mriTime_input'] ?? '');
        $consult_intervention_datetime = combineDateTime($_POST['consult_intervention_time'] ?? '', $_POST['consult_intervention_time_input'] ?? '');

        // 2. รับค่าผลตรวจ
        $aspect_score = $_POST['aspect'] ?? null;
        $collateral_score = $_POST['collateral'] ?? null;
        $occlusion_site = $_POST['occlusionLocation'] ?? null;
        $ct_result = $_POST['ctResult'] ?? null;

        // 3. รับค่าการรักษา
        
        $tpa_datetime = combineDateTime($_POST['tpaDate'] ?? '', $_POST['tpaTime'] ?? '');
        $anesthesia_set_datetime = combineDateTime($_POST['anesthesiaTime_date'] ?? '', $_POST['anesthesiaTime_time'] ?? '');
        $activate_team_datetime = combineDateTime($_POST['punctureTime_date'] ?? '', $_POST['punctureTime_time'] ?? '');
        $consult_neurosurgeon_datetime = combineDateTime($_POST['consultNS_date'] ?? '', $_POST['consultNS_time'] ?? '');

        // 4. เวลาคำนวณ (Door to...)
        $time_door_to_doctor_min = !empty($_POST['time_door_to_doctor_min']) ? $_POST['time_door_to_doctor_min'] : null;
        $time_door_to_ct_min = !empty($_POST['time_door_to_ct_min']) ? $_POST['time_door_to_ct_min'] : null;
        $time_door_to_cta_min = !empty($_POST['time_door_to_cta_min']) ? $_POST['time_door_to_cta_min'] : null;
        $time_door_to_intervention_min = !empty($_POST['time_door_to_intervention_min']) ? $_POST['time_door_to_intervention_min'] : null;
        // [ใหม่]
        $time_door_to_needle_min = !empty($_POST['time_door_to_needle_min']) ? $_POST['time_door_to_needle_min'] : null;

        $sess_user = $_SESSION['user_data'] ?? [];
        $current_user = $sess_user['HR_FNAME'] ?? $sess_user['hr_fname'] ?? 'System';

        $sql = "INSERT INTO tbl_er (
                    admission_id,
                    consult_neuro_datetime, ctnc_datetime, cta_datetime, mri_datetime,
                    consult_intervention_datetime,
                    aspect_score, collateral_score, occlusion_site, ct_result,
                    tpa_datetime,
                    anesthesia_set_datetime, activate_team_datetime,
                    consult_neurosurgeon_datetime,
                    time_door_to_doctor_min, time_door_to_ct_min, time_door_to_cta_min,
                    time_door_to_intervention_min, time_door_to_needle_min, -- เพิ่ม
                    created_by, created_at, updated_by, updated_at
                ) VALUES (
                    ?, 
                    ?, ?, ?, ?, 
                    ?, 
                    ?, ?, ?, ?, 
                    ?, 
                    ?, ?, 
                    ?, 
                    ?, ?, ?, 
                    ?, ?, -- Placeholder ใหม่
                    ?, NOW(), ?, NOW()
                ) 
                ON DUPLICATE KEY UPDATE 
                    consult_neuro_datetime = VALUES(consult_neuro_datetime),
                    ctnc_datetime = VALUES(ctnc_datetime),
                    cta_datetime = VALUES(cta_datetime),
                    mri_datetime = VALUES(mri_datetime),
                    consult_intervention_datetime = VALUES(consult_intervention_datetime),
                    
                    aspect_score = VALUES(aspect_score),
                    collateral_score = VALUES(collateral_score),
                    occlusion_site = VALUES(occlusion_site),
                    ct_result = VALUES(ct_result),
                    
                    
                    tpa_datetime = VALUES(tpa_datetime),
                    
                    anesthesia_set_datetime = VALUES(anesthesia_set_datetime),
                    activate_team_datetime = VALUES(activate_team_datetime),
                    consult_neurosurgeon_datetime = VALUES(consult_neurosurgeon_datetime),
                    
                    time_door_to_doctor_min = VALUES(time_door_to_doctor_min),
                    time_door_to_ct_min = VALUES(time_door_to_ct_min),
                    time_door_to_cta_min = VALUES(time_door_to_cta_min),
                    time_door_to_intervention_min = VALUES(time_door_to_intervention_min),
                    time_door_to_needle_min = VALUES(time_door_to_needle_min), -- Update

                    updated_by = VALUES(updated_by),
                    updated_at = NOW()
                ";

        $stmt = $conn->prepare($sql);

        // Bind Param: isssssiisssssssssiiiii (22 ตัว)
        // เพิ่ม i ท้ายสุดอีก 1 ตัว
        
        $stmt->bind_param(
            "isssssiissssssssiiiii", 
            $admission_id,
            
            $consult_neuro_datetime,
            $ctnc_datetime,
            $cta_datetime,
            $mri_datetime,
            $consult_intervention_datetime,
            
            $aspect_score,
            $collateral_score,
            $occlusion_site,
            $ct_result,
            
            
            $tpa_datetime,
            $anesthesia_set_datetime,
            $activate_team_datetime,
            $consult_neurosurgeon_datetime,
            
            $time_door_to_doctor_min,
            $time_door_to_ct_min,
            $time_door_to_cta_min,
            $time_door_to_intervention_min,
            $time_door_to_needle_min, // [ใหม่]

            $current_user,
            $current_user
        );

        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'บันทึกข้อมูล ER สำเร็จ!',
                'redirect_url' => 'OR_Procedure_Form.php?admission_id=' . $admission_id
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