<?php
session_start();
require_once 'connectdb.php'; 

// ฟังก์ชันรวม Date + Time (ยืดหยุ่น)
function combineDateTime($date, $time) {
    if (empty($date)) return null;
    if (empty($time)) return $date . ' 00:00:00';
    return $date . ' ' . $time;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $admission_id = $_POST['admission_id'] ?? '';
    if (empty($admission_id)) {
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบ Admission ID']);
        exit;
    }

    // Section 2: CT Follow-up
    $followup_ct_datetime = combineDateTime($_POST['ct_date'] ?? '', $_POST['ct_time'] ?? '');
    $followup_ct_result = $_POST['ct_result'] ?? null;

    // Section 3: Discharge Assessment
    $discharge_assess_datetime = combineDateTime($_POST['dc_assess_date'] ?? '', $_POST['dc_assess_time'] ?? '');
    $discharge_mrs = $_POST['mrsDischarge'] ?? null;
    $discharge_barthel = $_POST['barthel'] ?? null;
    $discharge_hrs = $_POST['hrs'] ?? null;

    // Section 4: Discharge Status
    $discharge_plan_status = $_POST['discharge_planning'] ?? null; // came / not_came
    $discharge_date = $_POST['discharge_date'] ?? null; // Date only
    $discharge_status = $_POST['discharge_status'] ?? null; // recovery, improve...

    $created_by = $_SESSION['user_data']['name'] ?? 'System';

    // SQL Insert/Update (1:1)
    $sql = "INSERT INTO tbl_ward (
                admission_id,
                followup_ct_datetime, followup_ct_result,
                discharge_assess_datetime, discharge_mrs, discharge_barthel, discharge_hrs,
                discharge_plan_status, discharge_date, discharge_status,
                created_by
            ) VALUES (
                ?, 
                ?, ?, 
                ?, ?, ?, ?, 
                ?, ?, ?, 
                ?
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
                discharge_status = VALUES(discharge_status)
            ";

    $stmt = $conn->prepare($sql);

    // Type String:
    // i (id)
    // s (ct_dt) s (ct_res)
    // s (dc_dt) i (mrs) i (barthel) i (hrs)
    // s (plan) s (date) s (status)
    // s (created_by)
    // รวม: isssiiissss (11 ตัว)

    $stmt->bind_param("isssiiissss", 
        $admission_id,
        $followup_ct_datetime, $followup_ct_result,
        $discharge_assess_datetime, $discharge_mrs, $discharge_barthel, $discharge_hrs,
        $discharge_plan_status, $discharge_date, $discharge_status,
        $created_by
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