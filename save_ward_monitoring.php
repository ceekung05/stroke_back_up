<?php
session_start();
require_once 'connectdb.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $admission_id = $_POST['admission_id'] ?? '';
    
    // รวมวันเวลา (DateTime-Local ส่งมาเป็น YYYY-MM-DDTHH:MM)
    $record_datetime = $_POST['record_datetime'] ?? null; 
    if ($record_datetime) {
        // ตัดตัว T ออกให้เป็น format MySQL
        $record_datetime = str_replace('T', ' ', $record_datetime) . ':00'; 
    }

    $sbp = $_POST['sbp'] ?? null;
    $dbp = $_POST['dbp'] ?? null;
    $nihss = $_POST['nihss'] ?? null;
    $gcs = $_POST['gcs'] ?? null;
    $created_by = $_SESSION['user_data']['name'] ?? 'System';

    if (empty($admission_id) || empty($record_datetime)) {
        echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบ (ต้องมี ID และ เวลา)']);
        exit;
    }

    // INSERT ลงตารางลูก (1:N)
    $sql = "INSERT INTO tbl_ward_monitoring 
            (admission_id, record_datetime, sbp, dbp, nihss, gcs, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    // Types: i (id), s (time), i (sbp), i (dbp), i (nihss), s (gcs), s (created_by)
    // รวม: isiiiss
    $stmt->bind_param("isiiiss", $admission_id, $record_datetime, $sbp, $dbp, $nihss, $gcs, $created_by);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'เพิ่มบันทึกสำเร็จ']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
}
?>