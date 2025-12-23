<?php
session_start();
require_once 'connectdb.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. รับชื่อผู้ใช้งานจาก Session
    $current_user = $_SESSION['user_data']['hr_fname'] ?? 'System';

    $admission_id = $_POST['admission_id'] ?? '';
    
    // รวมวันเวลา
    $record_datetime = $_POST['record_datetime'] ?? null; 
    if ($record_datetime) {
        $record_datetime = str_replace('T', ' ', $record_datetime) . ':00'; 
    }

    $sbp = $_POST['sbp'] ?? null;
    $dbp = $_POST['dbp'] ?? null;
    $nihss = $_POST['nihss'] ?? null;
    $gcs = $_POST['gcs'] ?? null;

    if (empty($admission_id) || empty($record_datetime)) {
        echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบ']);
        exit;
    }

    // 2. แก้ไข SQL INSERT (เพิ่ม created_at, updated_by, updated_at)
    $sql = "INSERT INTO tbl_ward_monitoring 
            (admission_id, record_datetime, sbp, dbp, nihss, gcs, created_by, created_at, updated_by, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, NOW())";

    $stmt = $conn->prepare($sql);

    // 3. แก้ bind_param (เพิ่ม s 2 ตัวท้ายสุด สำหรับ created_by และ updated_by)
    // Types: i(id) s(time) i(sbp) i(dbp) i(nihss) s(gcs) s(create) s(update)
    $stmt->bind_param("isiiisss", 
        $admission_id, $record_datetime, $sbp, $dbp, $nihss, $gcs, 
        $current_user, // created_by
        $current_user  // updated_by (ใส่ค่าเริ่มต้นให้เหมือนคนสร้าง)
    );

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'เพิ่มบันทึกสำเร็จ']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
}
?>