<?php
session_start();
require_once 'connectdb.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    exit('Access Denied');
}

// ตั้งค่า Header ให้ Browser รู้ว่าเป็นไฟล์ Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Stroke_KPI_Report_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// *** สำคัญ: ใส่ BOM เพื่อให้รองรับภาษาไทย ***
echo "\xEF\xBB\xBF";

// --- QUERY DATA (เหมือนหน้า Dashboard) ---
function fetchSingle($conn, $sql) {
    $result = $conn->query($sql);
    return $result ? $result->fetch_assoc() : null;
}

// 1. ยอดรวม
$sql_summary = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN e.ct_result = 'ischemic' THEN 1 ELSE 0 END) as ischemic,
    SUM(CASE WHEN e.ct_result = 'hemorrhagic' THEN 1 ELSE 0 END) as hemorrhagic
FROM tbl_stroke_admission a
LEFT JOIN tbl_er e ON a.id = e.admission_id";
$summary = fetchSingle($conn, $sql_summary);

// 2. เวลาเฉลี่ย
$sql_times = "SELECT 
    AVG(TIMESTAMPDIFF(MINUTE, a.onset_datetime, a.hospital_arrival_datetime)) as avg_onset_to_door,
    AVG(e.time_door_to_ct_min) as avg_door_ct,
    AVG(e.time_door_to_needle_min) as avg_door_needle,
    AVG(o.time_door_to_puncture_min) as avg_door_puncture,
    AVG(e.time_door_to_doctor_min) as avg_door_consult,
    AVG(TIMESTAMPDIFF(MINUTE, e.ctnc_datetime, e.tpa_datetime)) as avg_ct_to_needle,
    AVG(TIMESTAMPDIFF(MINUTE, o.mt_puncture_datetime, o.mt_recanalization_datetime)) as avg_proc_time,
    AVG(DATEDIFF(w.discharge_date, DATE(a.created_at))) as avg_los
FROM tbl_stroke_admission a
LEFT JOIN tbl_er e ON a.id = e.admission_id
LEFT JOIN tbl_or_procedure o ON a.id = o.admission_id
LEFT JOIN tbl_ward w ON a.id = w.admission_id";
$times = fetchSingle($conn, $sql_times);

?>

<style>
    table { border-collapse: collapse; width: 100%; font-family: 'Sarabun', sans-serif; }
    th { background-color: #4e73df; color: white; padding: 10px; border: 1px solid #ddd; }
    td { padding: 8px; border: 1px solid #ddd; text-align: center; }
    .header-title { font-size: 20px; font-weight: bold; background-color: #f8f9fa; text-align: left; }
</style>

<table border="1">
    <tr>
        <td colspan="4" class="header-title" style="background-color: #FFFF00;">
            รายงานสรุปผลตัวชี้วัด Stroke Care (KPIs Report)
        </td>
    </tr>
    <tr>
        <td colspan="4" style="text-align: left;">ข้อมูล ณ วันที่: <?= date('d/m/Y H:i') ?></td>
    </tr>
    
    <tr><td colspan="4" class="header-title">1. สถิติปริมาณผู้ป่วย (Volume)</td></tr>
    <tr>
        <th>Total Cases</th>
        <th>Ischemic Stroke</th>
        <th>Hemorrhagic Stroke</th>
        <th>Other/Unknown</th>
    </tr>
    <tr>
        <td style="font-size: 18px; font-weight:bold;"><?= number_format($summary['total']) ?></td>
        <td><?= number_format($summary['ischemic']) ?></td>
        <td><?= number_format($summary['hemorrhagic']) ?></td>
        <td><?= number_format($summary['total'] - $summary['ischemic'] - $summary['hemorrhagic']) ?></td>
    </tr>

    <tr><td colspan="4" class="header-title">2. ประสิทธิภาพด้านเวลา (Time Performance) - หน่วย: นาที</td></tr>
    <tr>
        <th colspan="3">ตัวชี้วัด (KPI Name)</th>
        <th>เวลาเฉลี่ย (Average)</th>
    </tr>
    <tr>
        <td colspan="3" style="text-align:left;">Onset to Door (ระยะเวลามาถึง รพ.)</td>
        <td><?= number_format($times['avg_onset_to_door'], 0) ?></td>
    </tr>
    <tr>
        <td colspan="3" style="text-align:left;">Door to CT (ระยะเวลาทำ CT)</td>
        <td><?= number_format($times['avg_door_ct'], 0) ?></td>
    </tr>
    <tr>
        <td colspan="3" style="text-align:left;">Door to Needle (ระยะเวลาเริ่มยา)</td>
        <td><?= number_format($times['avg_door_needle'], 0) ?></td>
    </tr>
    <tr>
        <td colspan="3" style="text-align:left;">Door to Puncture (ระยะเวลาแทงเข็ม)</td>
        <td><?= number_format($times['avg_door_puncture'], 0) ?></td>
    </tr>
    <tr>
        <td colspan="3" style="text-align:left;">Door to Consult Neurologist</td>
        <td><?= number_format($times['avg_door_consult'], 0) ?></td>
    </tr>
    <tr>
        <td colspan="3" style="text-align:left;">CT to Needle Time</td>
        <td><?= number_format($times['avg_ct_to_needle'], 0) ?></td>
    </tr>
    <tr>
        <td colspan="3" style="text-align:left;">Procedure Time (Puncture to Recanalization)</td>
        <td><?= number_format($times['avg_proc_time'], 0) ?></td>
    </tr>
    <tr>
        <td colspan="3" style="text-align:left;">Length of Stay (วันนอนเฉลี่ย) *หน่วย: วัน</td>
        <td><?= number_format($times['avg_los'], 1) ?> วัน</td>
    </tr>
</table>