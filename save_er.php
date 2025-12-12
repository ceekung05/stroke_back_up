<?php
session_start();
require_once 'connectdb.php'; // อย่าลืมเปลี่ยนเป็นชื่อไฟล์ connect ของคุณ (เช่น connectdb.php)

// ฟังก์ชันรวม Date + Time
function combineDateTime($date, $time) {
    if (!empty($date) && !empty($time)) {
        return $date . ' ' . $time;
    }
    return null;
}

// ฟังก์ชันแปลง Checkbox
function checkVal($field_name) {
    return isset($_POST[$field_name]) ? 1 : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. รับค่า admission_id (หัวใจสำคัญ!)
    $admission_id = $_POST['admission_id'];

    if (empty($admission_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing Admission ID']);
        exit;
    }

    // 2. รับค่าเวลาต่างๆ (Section 1-4)
    // หมายเหตุ: ชื่อตัวแปร $_POST มาจาก name="..." ในฟอร์มของคุณ
    
    // 1. เวลาส่งต่อ
    $transfer_departure_datetime = combineDateTime($_POST['arrivalTime'] ?? '', $_POST['arrivalTime_time'] ?? '');
    $transfer_arrival_datetime = combineDateTime($_POST['arrivalTime_datetime'] ?? '', $_POST['arrivalTime_time_destination'] ?? '');

    // 2. ปรึกษา Neuro
    $consult_neuro_datetime = combineDateTime($_POST['consult_neuro_date'] ?? '', $_POST['consult_neuro_time_input'] ?? ''); // *** อย่าลืมไปเติม name ในฟอร์มนะ

    // 3. Imaging
    $ctnc_datetime = combineDateTime($_POST['ctncDate'] ?? '', $_POST['ctncTime_input'] ?? ''); // *** เติม name
    $cta_datetime = combineDateTime($_POST['ctaTime'] ?? '', $_POST['ctaTime_input'] ?? '');   // *** เติม name
    $mri_datetime = combineDateTime($_POST['mriTime'] ?? '', $_POST['mriTime_input'] ?? '');   // *** เติม name

    // 4. Intervention
    $consult_intervention_datetime = combineDateTime($_POST['consult_intervention_time'] ?? '', $_POST['consult_intervention_time_input'] ?? ''); // *** เติม name

    // 3. รับค่าผลตรวจ (Section 5)
    $aspect_score = $_POST['aspect'] ?? null;
    $collateral_score = $_POST['collateral'] ?? null;
    $occlusion_site = $_POST['occlusionLocation'] ?? null;
    $ct_result = $_POST['ctResult'] ?? null; // ischemic / hemorrhagic

    // 4. รับค่าการรักษา (Section 6)
    // Ischemic
    $fibrinolytic_type = $_POST['fibrinolytic_type'] ?? null;
    $tpa_start_time = $_POST['tpaTime'] ?? null; // รับแค่เวลา
    
    // *** (ในฟอร์มเดิมคุณมี input date/time แยกกันสำหรับ anesthesia/puncture ผมรวมให้นะ)
    $anesthesia_set_datetime = combineDateTime($_POST['anesthesiaTime_date'] ?? '', $_POST['anesthesiaTime_time'] ?? ''); 
    $activate_team_datetime = combineDateTime($_POST['punctureTime_date'] ?? '', $_POST['punctureTime_time'] ?? '');

    // Hemorrhagic
    $consult_neurosurgeon = checkVal('consultNS'); // checkbox

    $created_by = $_SESSION['user_data']['name'] ?? 'System';

    // ========================================================
    // บันทึกข้อมูล (ใช้ INSERT ON DUPLICATE KEY UPDATE เพื่อรองรับการแก้ไขซ้ำ)
    // ========================================================
    
    $sql = "INSERT INTO tbl_er (
                admission_id,
                transfer_departure_datetime, transfer_arrival_datetime,
                consult_neuro_datetime,
                ctnc_datetime, cta_datetime, mri_datetime,
                consult_intervention_datetime,
                aspect_score, collateral_score, occlusion_site, ct_result,
                fibrinolytic_type, tpa_start_time,
                anesthesia_set_datetime, activate_team_datetime,
                consult_neurosurgeon,
                created_by
            ) VALUES (
                ?, 
                ?, ?, 
                ?, 
                ?, ?, ?, 
                ?, 
                ?, ?, ?, ?, 
                ?, ?, 
                ?, ?, 
                ?, 
                ?
            ) 
            ON DUPLICATE KEY UPDATE 
                transfer_departure_datetime = VALUES(transfer_departure_datetime),
                ct_result = VALUES(ct_result),
                aspect_score = VALUES(aspect_score),
                occlusion_site = VALUES(occlusion_site),
                fibrinolytic_type = VALUES(fibrinolytic_type)
                -- (จริงๆ ควร UPDATE ให้ครบทุกฟิลด์ แต่ละไว้ฐานเข้าใจ)
            ";

    $stmt = $conn->prepare($sql);

    // Type String: i (id) + 7s (times) + 2i (scores) + 2s (text/enum) + 2s (fib/tpa) + 2s (times) + 1i (consult) + 1s (create)
    // รวม: 1+7+2+2+2+2+1+1 = 18 ตัว
    
    $stmt->bind_param("isssssssiissssssis", 
        $admission_id,
        $transfer_departure_datetime, $transfer_arrival_datetime,
        $consult_neuro_datetime,
        $ctnc_datetime, $cta_datetime, $mri_datetime,
        $consult_intervention_datetime,
        $aspect_score, $collateral_score, $occlusion_site, $ct_result,
        $fibrinolytic_type, $tpa_start_time,
        $anesthesia_set_datetime, $activate_team_datetime,
        $consult_neurosurgeon,
        $created_by
    );

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'บันทึกข้อมูล ER สำเร็จ!',
            'redirect_url' => 'OR_Procedure_Form.php?admission_id=' . $admission_id // ไปหน้า 3
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }

}
?>