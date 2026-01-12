<?php
session_start();
require_once 'connectdb.php';

function combineDateTime($date, $time)
{
    if (!empty($date) && !empty($time)) {
        return $date . ' ' . $time;
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // เปิด Error Report เพื่อช่วย Debug
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        // 1. ตรวจสอบ ID
        $admission_id = $_POST['admission_id'] ?? '';
        if (empty($admission_id)) {
            throw new Exception('ไม่พบ Admission ID');
        }

        // รับชื่อผู้ใช้งาน
        $sess_user = $_SESSION['user_data'] ?? [];
        $current_user = $sess_user['HR_FNAME'] ?? $sess_user['hr_fname'] ?? 'System';

        // 2. รับค่าจากฟอร์ม
        // ส่วน Investigation
        $followup_ct_datetime = combineDateTime($_POST['ct_date'] ?? '', $_POST['ct_time'] ?? '');
        $followup_ct_result = $_POST['ct_result'] ?? null;

        // ส่วน Discharge Assessment
        $discharge_assess_datetime = combineDateTime($_POST['dc_assess_date'] ?? '', $_POST['dc_assess_time'] ?? '');
        $discharge_mrs = $_POST['mrsDischarge'] ?? null;
        $discharge_barthel = $_POST['barthel'] ?? null;

        // ส่วน Discharge Summary (เพิ่มตัวแปรใหม่)
        $discharge_type = $_POST['discharge_type'] ?? null;      // [ใหม่] Approval, Refer, Against, Death
        $discharge_status = $_POST['discharge_status'] ?? null;  // Recovery, Improve, Not Improved
        $discharge_plan_status = $_POST['discharge_planning'] ?? null;
        $discharge_date = !empty($_POST['discharge_date']) ? $_POST['discharge_date'] : null;

        // 3. คำสั่ง SQL (เพิ่ม discharge_type)
        $sql = "INSERT INTO tbl_ward (
                    admission_id, 
                    followup_ct_datetime, followup_ct_result,
                    discharge_assess_datetime, discharge_mrs, discharge_barthel,
                    discharge_type, discharge_status, discharge_plan_status, discharge_date, -- เพิ่ม discharge_type ตรงนี้
                    created_by, created_at, updated_by, updated_at
                ) VALUES (
                    ?, 
                    ?, ?, 
                    ?, ?, ?, 
                    ?, ?, ?, ?, -- เพิ่ม Placeholder ? อีก 1 ตัว
                    ?, NOW(), ?, NOW()
                )
                ON DUPLICATE KEY UPDATE
                    followup_ct_datetime = VALUES(followup_ct_datetime),
                    followup_ct_result = VALUES(followup_ct_result),
                    discharge_assess_datetime = VALUES(discharge_assess_datetime),
                    discharge_mrs = VALUES(discharge_mrs),
                    discharge_barthel = VALUES(discharge_barthel),
                    
                    discharge_type = VALUES(discharge_type), -- เพิ่มบรรทัดนี้
                    discharge_status = VALUES(discharge_status),
                    discharge_plan_status = VALUES(discharge_plan_status),
                    discharge_date = VALUES(discharge_date),
                    
                    updated_by = VALUES(updated_by),
                    updated_at = NOW()
                ";

        $stmt = $conn->prepare($sql);

        // 4. Bind Param (เพิ่ม 's' และตัวแปร $discharge_type)
        // types: i(id) + s(time) s(res) + s(time) i(mrs) i(bart) + s(type) s(stat) s(plan) s(date) + s(user) s(user)
        // รวมทั้งหมด 12 ตัวแปร -> Type String: "issiiissssss"

        // isssiissssss (แก้ตัวที่ 4 เป็น s)
        $stmt->bind_param(
            "isssiissssss",
            $admission_id,
            $followup_ct_datetime,
            $followup_ct_result,
            $discharge_assess_datetime,
            $discharge_mrs,
            $discharge_barthel,

            $discharge_type,        // [ใหม่] ตัวแปรที่ 7
            $discharge_status,      // ตัวแปรที่ 8
            $discharge_plan_status, // ตัวแปรที่ 9
            $discharge_date,        // ตัวแปรที่ 10

            $current_user,          // ตัวแปรที่ 11
            $current_user           // ตัวแปรที่ 12
        );

        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'บันทึกข้อมูล Ward สำเร็จ',
                'redirect_url' => 'follow.php?admission_id=' . $admission_id
            ]);
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        // ส่ง Error กลับไปเป็น JSON
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
