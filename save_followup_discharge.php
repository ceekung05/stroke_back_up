<?php
session_start();
require_once 'connectdb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // แก้ไข: รับชื่อผู้ใช้ให้ชัวร์
    $sess_user = $_SESSION['user_data'] ?? [];
    $current_user = $sess_user['HR_FNAME'] ?? $sess_user['hr_fname'] ?? 'System';
    $admission_id = $_POST['admission_id'] ?? '';
    $first_followup_date = $_POST['first_followup_date'] ?? null;
    $discharge_destination = $_POST['discharge_destination'] ?? null;
    $refer_name_hospital = $_POST['refer_name_hospital'] ?? null;

    if (empty($admission_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing ID']);
        exit;
    }

    // 2. แก้ SQL UPDATE
    $sql = "UPDATE tbl_ward SET 
            first_followup_date = ?, 
            discharge_destination = ?,
            refer_name_hospital = ?,
            updated_by = ?,      -- เพิ่ม
            updated_at = NOW()   -- เพิ่ม
            WHERE admission_id = ?";

    $stmt = $conn->prepare($sql);

    // 3. แก้ bind_param (เพิ่ม s ตรงกลางสำหรับ updated_by)
    $stmt->bind_param("ssssi", $first_followup_date, $discharge_destination,$refer_name_hospital, $current_user, $admission_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลจำหน่ายสำเร็จ']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
}
