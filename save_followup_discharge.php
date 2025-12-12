<?php
session_start();
require_once 'connectdb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admission_id = $_POST['admission_id'] ?? '';
    
    // รับค่าจาก Section 1
    $first_followup_date = $_POST['first_followup_date'] ?? null; // วันที่นัดครั้งแรก
    $discharge_destination = $_POST['discharge_destination'] ?? null; // home / refer

    if (empty($admission_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing ID']);
        exit;
    }

    // UPDATE ลง tbl_ward (เพราะเป็นข้อมูลต่อเนื่องจากหน้า Ward)
    $sql = "UPDATE tbl_ward SET 
            first_followup_date = ?, 
            discharge_destination = ? 
            WHERE admission_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $first_followup_date, $discharge_destination, $admission_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลจำหน่ายสำเร็จ']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
}
?>