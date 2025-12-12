<?php
session_start();
require_once 'connectdb.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$admission_id = $_POST['admission_id'] ?? '';

if (empty($admission_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing Admission ID']);
    exit;
}

// --- CASE 1: สร้างนัดอัตโนมัติ (Auto Create) ---
if ($action === 'auto_create') {
    $start_date = $_POST['start_date'] ?? '';
    
    if (empty($start_date)) {
        echo json_encode(['status' => 'error', 'message' => 'กรุณาระบุวันที่นัดครั้งแรกก่อน']);
        exit;
    }

    // ลบของเก่าทิ้งก่อน (Reset)
    $conn->query("DELETE FROM tbl_followup WHERE admission_id = $admission_id");

    // คำนวณวันนัด (1, 3, 6, 12 เดือน)
    $intervals = [1, 3, 6, 12];
    $base_time = strtotime($start_date);

    $stmt = $conn->prepare("INSERT INTO tbl_followup (admission_id, followup_label, scheduled_date, status) VALUES (?, ?, ?, 'pending')");

    foreach ($intervals as $month) {
        $label = "mRS $month เดือน";
        $next_date = date('Y-m-d', strtotime("+$month month", $base_time));
        $stmt->bind_param("iss", $admission_id, $label, $next_date);
        $stmt->execute();
    }

    echo json_encode(['status' => 'success', 'message' => 'สร้างตารางนัดหมายเรียบร้อย']);
}

// --- CASE 2: อัปเดตผล (Update Score / Status) ---
else if ($action === 'update_item') {
    $followup_id = $_POST['followup_id'];
    $status = $_POST['status']; // attended, no_show
    $mrs_score = $_POST['mrs_score'] ?? null; // 0-6 หรือ null

    $sql = "UPDATE tbl_followup SET status = ?, mrs_score = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $status, $mrs_score, $followup_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
}

// --- CASE 3: ดึงข้อมูลล่าสุด (Get List) ---
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