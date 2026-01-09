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
    // เปิด Error Reporting เพื่อช่วยหาจุดผิดพลาด (เอาออกได้เมื่อใช้งานจริง)
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        // 1. รับค่า admission_id
        $admission_id = $_POST['admission_id'] ?? '';

        if (empty($admission_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing Admission ID']);
            exit;
        }

        // 2. รับค่าเวลาต่างๆ
        
        $consult_neuro_datetime = combineDateTime($_POST['consult_neuro_date'] ?? '', $_POST['consult_neuro_time_input'] ?? '');

        $ctnc_datetime = combineDateTime($_POST['ctncDate'] ?? '', $_POST['ctncTime_input'] ?? '');
        $cta_datetime = combineDateTime($_POST['ctaTime'] ?? '', $_POST['ctaTime_input'] ?? '');
        $mri_datetime = combineDateTime($_POST['mriTime'] ?? '', $_POST['mriTime_input'] ?? '');

        $consult_intervention_datetime = combineDateTime($_POST['consult_intervention_time'] ?? '', $_POST['consult_intervention_time_input'] ?? '');

        // 3. รับค่าผลตรวจ
        $aspect_score = $_POST['aspect'] ?? null;
        $collateral_score = $_POST['collateral'] ?? null;
        $occlusion_site = $_POST['occlusionLocation'] ?? null;
        $ct_result = $_POST['ctResult'] ?? null;

        // 4. รับค่าการรักษา
        $fibrinolytic_type = $_POST['fibrinolytic_type'] ?? null;
        $tpa_start_time = $_POST['tpaTime'] ?? null;

        $anesthesia_set_datetime = combineDateTime($_POST['anesthesiaTime_date'] ?? '', $_POST['anesthesiaTime_time'] ?? '');
        $activate_team_datetime = combineDateTime($_POST['punctureTime_date'] ?? '', $_POST['punctureTime_time'] ?? '');

        $consult_neurosurgeon = checkVal('consultNS');

        // แก้ไข: รับชื่อผู้ใช้ (รองรับทั้งตัวพิมพ์ใหญ่และเล็ก)
        $sess_user = $_SESSION['user_data'] ?? [];
        $current_user = $sess_user['HR_FNAME'] ?? $sess_user['hr_fname'] ?? 'System';

        $sql = "INSERT INTO tbl_er (
                    admission_id,
                    
                    consult_neuro_datetime,
                    ctnc_datetime, cta_datetime, mri_datetime,
                    consult_intervention_datetime,
                    aspect_score, collateral_score, occlusion_site, ct_result,
                    fibrinolytic_type, tpa_start_time,
                    anesthesia_set_datetime, activate_team_datetime,
                    consult_neurosurgeon,
                    created_by, created_at, updated_by, updated_at
                ) VALUES (
                    ?, 
                     
                    ?, 
                    ?, ?, ?, 
                    ?, 
                    ?, ?, ?, ?, 
                    ?, ?, 
                    ?, ?, 
                    ?, 
                    ?, NOW(), ?, NOW()
                ) 
                ON DUPLICATE KEY UPDATE 
                    
                    ct_result = VALUES(ct_result),
                    aspect_score = VALUES(aspect_score),
                    occlusion_site = VALUES(occlusion_site),
                    fibrinolytic_type = VALUES(fibrinolytic_type),
                    updated_by = VALUES(updated_by),
                    updated_at = NOW()
                ";

        $stmt = $conn->prepare($sql);

        // *** แก้ไข: ใช้ bind_param ชุดเดียว และต้องมีตัวแปรครบ 19 ตัว ***
        // i = int, s = string
        // ลำดับ: id(1) + times(7) + scores(2) + text(2) + fib/tpa(2) + times(2) + consult(1) + users(2) = 19

        $stmt->bind_param(
            "isssssiissssssiss",
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
            $fibrinolytic_type,
            $tpa_start_time,
            $anesthesia_set_datetime,
            $activate_team_datetime,
            $consult_neurosurgeon,
            $current_user, // created_by
            $current_user  // updated_by
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
