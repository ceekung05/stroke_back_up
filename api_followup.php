<?php
session_start();
require_once 'connectdb.php';
header('Content-Type: application/json');

// 1. รับชื่อผู้ใช้งาน
$current_user = $_SESSION['user_data']['hr_fname'] ?? 'System';

$action = $_POST['action'] ?? '';
$admission_id = $_POST['admission_id'] ?? '';

// --- แก้ไข: เช็ค admission_id เฉพาะเคสที่จำเป็นต้องใช้ (auto_create, get_list) ---
// ส่วน update_item หรือ update_date ให้ผ่านไปได้เลย เพราะใช้ id ของตัวเอง
if (($action == 'auto_create' || $action == 'get_list') && empty($admission_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing Admission ID']);
    exit;
}

// --- CASE 1: สร้างนัดอัตโนมัติ (INSERT) ---
if ($action === 'auto_create') {
    $start_date = $_POST['start_date'] ?? '';
    
    if (empty($start_date)) {
        echo json_encode(['status' => 'error', 'message' => 'กรุณาระบุวันที่นัดครั้งแรกก่อน']);
        exit;
    }

    $conn->query("DELETE FROM tbl_followup WHERE admission_id = $admission_id");

    $intervals = [1, 3, 6, 12];
    $base_time = strtotime($start_date);

    // แก้ SQL INSERT เพิ่ม created/updated
    $stmt = $conn->prepare("INSERT INTO tbl_followup (admission_id, followup_label, scheduled_date, status, created_by, created_at, updated_by, updated_at) VALUES (?, ?, ?, 'pending', ?, NOW(), ?, NOW())");

    foreach ($intervals as $month) {
        $label = "mRS $month เดือน";
        $next_date = date('Y-m-d', strtotime("+$month month", $base_time));
        
        // Bind Param (เพิ่ม user 2 ตัว)
        $stmt->bind_param("issss", $admission_id, $label, $next_date, $current_user, $current_user);
        $stmt->execute();
    }

    echo json_encode(['status' => 'success', 'message' => 'สร้างตารางนัดหมายเรียบร้อย']);
}

// --- CASE 2: อัปเดตผล (UPDATE) ---
else if ($action === 'update_item') {
    $followup_id = $_POST['followup_id'];
    $status = $_POST['status']; 
    $mrs_score = $_POST['mrs_score'] ?? null; 

    // แก้ SQL UPDATE เพิ่ม updated_by
    $sql = "UPDATE tbl_followup SET status = ?, mrs_score = ?, updated_by = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    // Bind Param
    $stmt->bind_param("sisi", $status, $mrs_score, $current_user, $followup_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
}

// --- CASE 4: อัปเดตเฉพาะวันที่นัด (UPDATE DATE) ---
else if ($action === 'update_date') {
    $followup_id = $_POST['followup_id'] ?? '';
    $new_date = $_POST['new_date'] ?? '';

    if (empty($followup_id) || empty($new_date)) {
        echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
        exit;
    }

    // อัปเดตวันที่ + คนที่แก้ไขล่าสุด
    $sql = "UPDATE tbl_followup 
            SET scheduled_date = ?, 
                updated_by = ?, 
                updated_at = NOW() 
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $new_date, $current_user, $followup_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'บันทึกวันที่เรียบร้อย']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
}

// --- CASE 3: ดึงข้อมูล (เหมือนเดิม) ---
else if ($action === 'get_list') {
    $sql = "SELECT * FROM tbl_followup WHERE admission_id = ? ORDER BY scheduled_date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $data]);
}
?>