<?php
session_start();
require_once 'connectdb.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// --- Helper Function ---
function fetchData($conn, $sql) {
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function fetchSingle($conn, $sql) {
    $result = $conn->query($sql);
    return $result ? $result->fetch_assoc() : null;
}

// 1. สรุปยอดรวม (Total Cases)
$sql_summary = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN e.ct_result = 'ischemic' THEN 1 ELSE 0 END) as ischemic,
    SUM(CASE WHEN e.ct_result = 'hemorrhagic' THEN 1 ELSE 0 END) as hemorrhagic
FROM tbl_stroke_admission a
LEFT JOIN tbl_er e ON a.id = e.admission_id";
$summary = fetchSingle($conn, $sql_summary);

// 2. ค่าเฉลี่ยเวลา (Time KPI) - เพิ่ม Door to Consult Interventionist
$sql_times = "SELECT 
    AVG(e.time_door_to_ct_min) as avg_door_ct,
    AVG(e.time_door_to_needle_min) as avg_door_needle,
    AVG(o.time_door_to_puncture_min) as avg_door_puncture,
    AVG(o.time_onset_to_recanalization_min) as avg_onset_recan,
    AVG(TIMESTAMPDIFF(MINUTE, a.onset_datetime, a.hospital_arrival_datetime)) as avg_onset_to_door,
    
    AVG(e.time_door_to_doctor_min) as avg_door_consult,
    AVG(e.time_door_to_intervention_min) as avg_door_inter, -- [เพิ่ม] Consult Interventionist

    AVG(TIMESTAMPDIFF(MINUTE, e.ctnc_datetime, e.tpa_datetime)) as avg_ct_to_needle,
    AVG(TIMESTAMPDIFF(MINUTE, o.mt_puncture_datetime, o.mt_recanalization_datetime)) as avg_proc_time,
    AVG(DATEDIFF(w.discharge_date, DATE(a.created_at))) as avg_los
FROM tbl_stroke_admission a
LEFT JOIN tbl_er e ON a.id = e.admission_id
LEFT JOIN tbl_or_procedure o ON a.id = o.admission_id
LEFT JOIN tbl_ward w ON a.id = w.admission_id";
$times = fetchSingle($conn, $sql_times);

// --- SQL สำหรับกราฟต่างๆ (เพิ่มตามใบสั่ง) ---

// 3. Arrival Type
$arrival_data = fetchData($conn, "SELECT arrival_type, COUNT(*) as count FROM tbl_stroke_admission GROUP BY arrival_type");

// 4. Fibrinolytic
$fibro_data = fetchData($conn, "SELECT fibrinolytic_type, COUNT(*) as count FROM tbl_er WHERE fibrinolytic_type IS NOT NULL AND fibrinolytic_type != '' GROUP BY fibrinolytic_type");

// 5. TICI Score
$tici_data = fetchData($conn, "SELECT mt_tici_score, COUNT(*) as count FROM tbl_or_procedure WHERE mt_tici_score IS NOT NULL GROUP BY mt_tici_score ORDER BY mt_tici_score");

// 6. mRS (Discharge)
$mrs_data = fetchData($conn, "SELECT discharge_mrs, COUNT(*) as count FROM tbl_ward WHERE discharge_mrs IS NOT NULL GROUP BY discharge_mrs ORDER BY discharge_mrs");

// 7. [ใหม่] Occlusion Vessel (ตำแหน่งเส้นเลือดอุดตัน)
$occlusion_data = fetchData($conn, "SELECT occlusion_site, COUNT(*) as count FROM tbl_er WHERE occlusion_site IS NOT NULL AND occlusion_site != '' GROUP BY occlusion_site");

// 8. [ใหม่] Complications (ภาวะแทรกซ้อน)
$complication_data = fetchData($conn, "SELECT complication_details, COUNT(*) as count FROM tbl_or_procedure WHERE complication_details IS NOT NULL AND complication_details != '' GROUP BY complication_details");

// 9. [ใหม่] Hemorrhagic Surgery (ผ่าตัด vs ไม่ผ่าตัด)
// นับคนที่เป็น Hemorrhagic และมีการติ๊กหัตถการอย่างใดอย่างหนึ่ง (Craniotomy/Craniectomy/Ventriculostomy)
$sql_hemo_op = "SELECT 
    SUM(CASE WHEN (hemo_proc_craniotomy = 1 OR hemo_proc_craniectomy = 1 OR hemo_proc_ventriculostomy = 1) THEN 1 ELSE 0 END) as operated,
    SUM(CASE WHEN (hemo_proc_craniotomy = 0 AND hemo_proc_craniectomy = 0 AND hemo_proc_ventriculostomy = 0) THEN 1 ELSE 0 END) as conservative
FROM tbl_or_procedure o
JOIN tbl_er e ON o.admission_id = e.admission_id
WHERE e.ct_result = 'hemorrhagic'";
$hemo_op_data = fetchSingle($conn, $sql_hemo_op);

?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Executive Dashboard - Stroke Care</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <link rel="stylesheet" href="style.css">

    <style>
        /* CSS ปกติ */
        .kpi-card { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.2s; color: #fff; position: relative; overflow: hidden; }
        .kpi-card:hover { transform: translateY(-3px); }
        .kpi-value { font-size: 2.5rem; font-weight: 700; line-height: 1.2; }
        .kpi-label { font-size: 0.9rem; text-transform: uppercase; opacity: 0.9; }
        .kpi-icon { position: absolute; right: 15px; bottom: 10px; font-size: 3.5rem; opacity: 0.2; }
        
        .time-card { background: #fff; border-radius: 10px; padding: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-left: 5px solid #ddd; height: 100%; }
        .time-val { font-size: 1.8rem; font-weight: bold; color: #333; }
        .time-unit { font-size: 0.9rem; color: #888; font-weight: normal; }
        
        .chart-box { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); height: 100%; }

        /* Print CSS */
        @media print {
            .sidebar, .no-print { display: none !important; }
            .main { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
            body { background-color: #fff !important; }
            .kpi-card, .time-card, .chart-box { background: #fff !important; color: #000 !important; box-shadow: none !important; border: 1px solid #ccc !important; }
            .kpi-card .kpi-label, .kpi-card .kpi-value, .kpi-card span, .kpi-card i { color: #000 !important; opacity: 1 !important; }
            .progress { display: none !important; }
            .row, .chart-box { page-break-inside: avoid; }
            .col-md-3, .col-md-4, .col-md-6 { flex: 0 0 auto; } /* บังคับ Grid ตอนพิมพ์ */
            .col-md-3 { width: 25%; } .col-md-4 { width: 33.33%; } .col-md-6 { width: 50%; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <i class="bi bi-heart-pulse-fill"></i> <span>Stroke Care</span>
    </div>
    <hr class="sidebar-divider">
    <a href="dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="index.php"><i class="bi bi-list-task"></i> กลับไปหน้า Patient List</a>
    <hr class="sidebar-divider">
    <a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a>
</div>

<div class="main" id="reportContent">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-container bg-primary text-white me-3">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div>
                <h1 class="mb-0">Executive Dashboard</h1>
                <p class="text-muted mb-0">สรุปผลการดำเนินงาน Stroke Care Unit</p>
            </div>
        </div>
        <div class="d-flex gap-2 no-print">
            <a href="export_excel.php" target="_blank" class="btn btn-success shadow-sm"><i class="bi bi-file-earmark-excel"></i> Excel</a>
            <button onclick="exportPDF()" class="btn btn-danger shadow-sm"><i class="bi bi-file-earmark-pdf"></i> PDF</button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="kpi-card bg-primary bg-gradient p-4">
                <div class="kpi-label">Total Cases</div>
                <div class="kpi-value"><?= number_format($summary['total']) ?> <span class="fs-6">ราย</span></div>
                <i class="bi bi-people-fill kpi-icon"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card bg-info bg-gradient p-4">
                <div class="kpi-label">Ischemic Stroke</div>
                <div class="kpi-value"><?= number_format($summary['ischemic']) ?> <span class="fs-6">ราย</span></div>
                <div class="progress mt-2" style="height: 4px; background: rgba(255,255,255,0.3);"><div class="progress-bar bg-white" style="width: <?= $summary['total'] > 0 ? ($summary['ischemic']/$summary['total'])*100 : 0 ?>%"></div></div>
                <i class="bi bi-droplet-half kpi-icon"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card bg-danger bg-gradient p-4">
                <div class="kpi-label">Hemorrhagic Stroke</div>
                <div class="kpi-value"><?= number_format($summary['hemorrhagic']) ?> <span class="fs-6">ราย</span></div>
                <div class="progress mt-2" style="height: 4px; background: rgba(255,255,255,0.3);"><div class="progress-bar bg-white" style="width: <?= $summary['total'] > 0 ? ($summary['hemorrhagic']/$summary['total'])*100 : 0 ?>%"></div></div>
                <i class="bi bi-bandaid kpi-icon"></i>
            </div>
        </div>
    </div>

    <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-stopwatch"></i> ประสิทธิภาพด้านเวลา (Time Performance)</h6>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="time-card" style="border-left-color: #4e73df;"><div class="text-muted small fw-bold">ONSET TO DOOR</div><div class="time-val text-primary"><?= $times['avg_onset_to_door'] > 0 ? number_format($times['avg_onset_to_door'], 0) : '-' ?> <span class="time-unit">นาที</span></div></div></div>
        <div class="col-md-3"><div class="time-card" style="border-left-color: #1cc88a;"><div class="text-muted small fw-bold">DOOR TO CT</div><div class="time-val text-success"><?= $times['avg_door_ct'] > 0 ? number_format($times['avg_door_ct'], 0) : '-' ?> <span class="time-unit">นาที</span></div></div></div>
        <div class="col-md-3"><div class="time-card" style="border-left-color: #f6c23e;"><div class="text-muted small fw-bold">DOOR TO NEEDLE</div><div class="time-val text-warning"><?= $times['avg_door_needle'] > 0 ? number_format($times['avg_door_needle'], 0) : '-' ?> <span class="time-unit">นาที</span></div></div></div>
        <div class="col-md-3"><div class="time-card" style="border-left-color: #36b9cc;"><div class="text-muted small fw-bold">DOOR TO PUNCTURE</div><div class="time-val text-info"><?= $times['avg_door_puncture'] > 0 ? number_format($times['avg_door_puncture'], 0) : '-' ?> <span class="time-unit">นาที</span></div></div></div>
        
        <div class="col-md-3"><div class="time-card" style="border-left-color: #6610f2;"><div class="text-muted small fw-bold">DOOR TO CONSULT NEURO</div><div class="time-val" style="color: #6610f2;"><?= $times['avg_door_consult'] > 0 ? number_format($times['avg_door_consult'], 0) : '-' ?> <span class="time-unit">นาที</span></div></div></div>
        <div class="col-md-3"><div class="time-card" style="border-left-color: #e83e8c;"><div class="text-muted small fw-bold">DOOR TO INTERVENTIONIST</div><div class="time-val" style="color: #e83e8c;"><?= $times['avg_door_inter'] > 0 ? number_format($times['avg_door_inter'], 0) : '-' ?> <span class="time-unit">นาที</span></div></div></div>
        <div class="col-md-3"><div class="time-card" style="border-left-color: #fd7e14;"><div class="text-muted small fw-bold">CT TO NEEDLE</div><div class="time-val" style="color: #fd7e14;"><?= $times['avg_ct_to_needle'] > 0 ? number_format($times['avg_ct_to_needle'], 0) : '-' ?> <span class="time-unit">นาที</span></div></div></div>
        <div class="col-md-3"><div class="time-card" style="border-left-color: #20c997;"><div class="text-muted small fw-bold">PROCEDURE TIME</div><div class="time-val" style="color: #20c997;"><?= $times['avg_proc_time'] > 0 ? number_format($times['avg_proc_time'], 0) : '-' ?> <span class="time-unit">นาที</span></div></div></div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="chart-box">
                <h6 class="fw-bold text-secondary mb-3">1. Occlusion Vessel (ตำแหน่งเส้นเลือดอุดตัน)</h6>
                <div style="height: 250px;"><canvas id="chartOcclusion"></canvas></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-box">
                <h6 class="fw-bold text-secondary mb-3">2. Complications (ภาวะแทรกซ้อน)</h6>
                <div style="height: 250px;"><canvas id="chartComplication"></canvas></div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="chart-box">
                <h6 class="fw-bold text-secondary mb-3">3. Hemorrhagic Operation Rate</h6>
                <div style="height: 250px;"><canvas id="chartHemoOp"></canvas></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="chart-box">
                <h6 class="fw-bold text-secondary mb-3">4. Fibrinolytic Type</h6>
                <div style="height: 250px;"><canvas id="chartFibro"></canvas></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="chart-box">
                <h6 class="fw-bold text-secondary mb-3">5. Arrival Type</h6>
                <div style="height: 250px;"><canvas id="chartArrival"></canvas></div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="chart-box">
                <h6 class="fw-bold text-secondary mb-3">6. TICI Score (ผลเปิดหลอดเลือด)</h6>
                <div style="height: 250px;"><canvas id="chartTici"></canvas></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-box">
                <h6 class="fw-bold text-secondary mb-3">7. mRS at Discharge (ผลลัพธ์)</h6>
                <div style="height: 250px;"><canvas id="chartMrs"></canvas></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // --- Config สำหรับ PDF ---
    function exportPDF() {
        const element = document.getElementById('reportContent');
        const opt = {
            margin: [5, 5, 5, 5], filename: 'Stroke_Executive_Report.pdf',
            image: { type: 'jpeg', quality: 1 },
            html2canvas: { scale: 2, useCORS: true, scrollY: 0 },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
        };
        html2pdf().set(opt).from(element).save();
    }

    // --- ข้อมูลกราฟ ---
    const occlusionData = <?= json_encode($occlusion_data) ?>;
    const complicationData = <?= json_encode($complication_data) ?>;
    const hemoOpData = <?= json_encode($hemo_op_data) ?>;
    const arrivalData = <?= json_encode($arrival_data) ?>;
    const fibroData = <?= json_encode($fibro_data) ?>;
    const ticiData = <?= json_encode($tici_data) ?>;
    const mrsData = <?= json_encode($mrs_data) ?>;

    // --- ชุดสี Cotton Candy Palette ---
    const pastelColors = [
        '#FFB7B2', // ชมพูพีช
        '#A0C4FF', // ฟ้าพาสเทล
        '#BDB2FF', // ม่วงลาเวนเดอร์
        '#FFDAC1', // ส้มไข่ไก่
        '#E2F0CB', // เขียวอ่อน
        '#FF9AA2'  // แดงกุหลาบ
    ];

    // 1. Occlusion Chart (Bar - แนวนอน)
    new Chart(document.getElementById('chartOcclusion'), {
        type: 'bar',
        data: {
            labels: occlusionData.map(d => d.occlusion_site),
            datasets: [{
                label: 'จำนวนเคส',
                data: occlusionData.map(d => d.count),
                backgroundColor: '#A0C4FF', // ฟ้าพาสเทล
                borderRadius: 8
            }]
        },
        options: { maintainAspectRatio: false, indexAxis: 'y' }
    });

    // 2. Complication Chart (Doughnut)
    new Chart(document.getElementById('chartComplication'), {
        type: 'doughnut',
        data: {
            labels: complicationData.map(d => d.complication_details),
            datasets: [{
                data: complicationData.map(d => d.count),
                backgroundColor: pastelColors, // ใช้ชุดสีรวม
                hoverOffset: 4
            }]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
    });

    // 3. Hemo Op Chart (Pie)
    new Chart(document.getElementById('chartHemoOp'), {
        type: 'pie',
        data: {
            labels: ['Operated (ผ่าตัด)', 'Conservative (ไม่ผ่า)'],
            datasets: [{
                data: [hemoOpData.operated, hemoOpData.conservative],
                backgroundColor: ['#FF9AA2', '#E2F0CB'] // แดงพาสเทล vs เขียวพาสเทล
            }]
        },
        options: { maintainAspectRatio: false }
    });

    // 4. Fibrinolytic (Pie)
    new Chart(document.getElementById('chartFibro'), {
        type: 'pie',
        data: {
            labels: fibroData.map(d => d.fibrinolytic_type),
            datasets: [{
                data: fibroData.map(d => d.count),
                backgroundColor: pastelColors
            }]
        },
        options: { maintainAspectRatio: false }
    });

    // 5. Arrival Type (Doughnut)
    new Chart(document.getElementById('chartArrival'), {
        type: 'doughnut',
        data: {
            labels: arrivalData.map(d => d.arrival_type),
            datasets: [{
                data: arrivalData.map(d => d.count),
                backgroundColor: pastelColors
            }]
        },
        options: { maintainAspectRatio: false }
    });

    // 6. TICI Score (Bar - แนวตั้ง)
    new Chart(document.getElementById('chartTici'), {
        type: 'bar',
        data: {
            labels: ticiData.map(d => 'Score ' + d.mt_tici_score),
            datasets: [{
                label: 'Cases',
                data: ticiData.map(d => d.count),
                backgroundColor: '#BDB2FF', // ม่วงพาสเทล
                borderRadius: 8
            }]
        },
        options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
    });

    // 7. mRS (Bar - แนวตั้ง)
    new Chart(document.getElementById('chartMrs'), {
        type: 'bar',
        data: {
            labels: mrsData.map(d => 'mRS ' + d.discharge_mrs),
            datasets: [{
                label: 'Patients',
                data: mrsData.map(d => d.count),
                backgroundColor: '#FFDAC1', // ส้มพาสเทล
                borderRadius: 8
            }]
        },
        options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
    });
</script>
</body>
</html>