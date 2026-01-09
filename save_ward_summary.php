<?php
session_start();
require_once 'connectdb.php'; 

function combineDateTime($date, $time) {
    if (empty($date)) return null;
    if (empty($time)) return $date . ' 00:00:00';
    return $date . ' ' . $time;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. รับชื่อผู้ใช้งาน
    // แก้ไข: รับชื่อผู้ใช้ให้ชัวร์
$sess_user = $_SESSION['user_data'] ?? [];
$current_user = $sess_user['HR_FNAME'] ?? $sess_user['hr_fname'] ?? 'System';

    $admission_id = $_POST['admission_id'] ?? '';
    if (empty($admission_id)) {
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบ Admission ID']);
        exit;
    }

    // (รับค่าตัวแปรอื่นๆ เหมือนเดิม...)
    $followup_ct_datetime = combineDateTime($_POST['ct_date'] ?? '', $_POST['ct_time'] ?? '');
    $followup_ct_result = $_POST['ct_result'] ?? null;
    $discharge_assess_datetime = combineDateTime($_POST['dc_assess_date'] ?? '', $_POST['dc_assess_time'] ?? '');
    $discharge_mrs = $_POST['mrsDischarge'] ?? null;
    $discharge_barthel = $_POST['barthel'] ?? null;
    $discharge_hrs = $_POST['hrs'] ?? null;
    $discharge_plan_status = $_POST['discharge_planning'] ?? null;
    $discharge_date = $_POST['discharge_date'] ?? null;
    $discharge_status = $_POST['discharge_status'] ?? null;

    // 2. แก้ไข SQL (เพิ่ม Fields User ทั้ง Insert และ Update)
    $sql = "INSERT INTO tbl_ward (
                admission_id,
                followup_ct_datetime, followup_ct_result,
                discharge_assess_datetime, discharge_mrs, discharge_barthel, discharge_hrs,
                discharge_plan_status, discharge_date, discharge_status,
                created_by, created_at, updated_by, updated_at
            ) VALUES (
                ?, 
                ?, ?, 
                ?, ?, ?, ?, 
                ?, ?, ?, 
                ?, NOW(), ?, NOW()
            )
            ON DUPLICATE KEY UPDATE
                followup_ct_datetime = VALUES(followup_ct_datetime),
                followup_ct_result = VALUES(followup_ct_result),
                discharge_assess_datetime = VALUES(discharge_assess_datetime),
                discharge_mrs = VALUES(discharge_mrs),
                discharge_barthel = VALUES(discharge_barthel),
                discharge_hrs = VALUES(discharge_hrs),
                discharge_plan_status = VALUES(discharge_plan_status),
                discharge_date = VALUES(discharge_date),
                discharge_status = VALUES(discharge_status),
                updated_by = VALUES(updated_by), -- อัปเดตชื่อคนแก้
                updated_at = NOW()               -- อัปเดตเวลา
            ";

    $stmt = $conn->prepare($sql);

    // 3. แก้ bind_param (เพิ่ม s ท้ายสุด 2 ตัว)
    // isssiiisssss
    $stmt->bind_param("isssiiisssss", 
        $admission_id,
        $followup_ct_datetime, $followup_ct_result,
        $discharge_assess_datetime, $discharge_mrs, $discharge_barthel, $discharge_hrs,
        $discharge_plan_status, $discharge_date, $discharge_status,
        $current_user, // created_by
        $current_user  // updated_by
    );

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'บันทึกข้อมูล Ward สำเร็จ!',
            'redirect_url' => 'follow.php?admission_id=' . $admission_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
}
?>