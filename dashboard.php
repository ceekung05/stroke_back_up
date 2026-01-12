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

// 2. ค่าเฉลี่ยเวลา (Time KPI)
$sql_times = "SELECT 
    -- ตัวเดิม (จากผลลัพธ์ที่คำนวณแล้ว)
    AVG(e.time_door_to_ct_min) as avg_door_ct,
    AVG(e.time_door_to_needle_min) as avg_door_needle,
    AVG(o.time_door_to_puncture_min) as avg_door_puncture,
    AVG(o.time_onset_to_recanalization_min) as avg_onset_recan,

    -- [ใหม่ 1] Onset to Door (มาช้าแค่ไหน)
    AVG(TIMESTAMPDIFF(MINUTE, a.onset_datetime, a.hospital_arrival_datetime)) as avg_onset_to_door,

    -- [ใหม่ 2] Door to Consult Neuro (หมอมาเร็วไหม)
    AVG(e.time_door_to_doctor_min) as avg_door_consult,

    -- [ใหม่ 3] CT to Needle (ตัดสินใจหลัง CT นานไหม)
    -- *** แก้ไขตรงนี้: เปลี่ยน e.tpa_start_time เป็น e.tpa_datetime ***
    AVG(TIMESTAMPDIFF(MINUTE, e.ctnc_datetime, e.tpa_datetime)) as avg_ct_to_needle,

    -- [ใหม่ 4] Puncture to Recanalization (ใช้เวลาทำหัตถการนานไหม)
    AVG(TIMESTAMPDIFF(MINUTE, o.mt_puncture_datetime, o.mt_recanalization_datetime)) as avg_proc_time,

    -- [ใหม่ 5] Length of Stay (วันนอนเฉลี่ย)
    AVG(DATEDIFF(w.discharge_date, DATE(a.created_at))) as avg_los

FROM tbl_stroke_admission a
LEFT JOIN tbl_er e ON a.id = e.admission_id
LEFT JOIN tbl_or_procedure o ON a.id = o.admission_id
LEFT JOIN tbl_ward w ON a.id = w.admission_id";

$times = fetchSingle($conn, $sql_times);

// 3. กราฟ Arrival Type
$sql_arrival = "SELECT arrival_type, COUNT(*) as count FROM tbl_stroke_admission GROUP BY arrival_type";
$arrival_data = fetchData($conn, $sql_arrival);

// 4. กราฟ Fibrinolytic
$sql_fibro = "SELECT fibrinolytic_type, COUNT(*) as count FROM tbl_er WHERE fibrinolytic_type IS NOT NULL AND fibrinolytic_type != '' GROUP BY fibrinolytic_type";
$fibro_data = fetchData($conn, $sql_fibro);

// 5. กราฟ TICI Score
$sql_tici = "SELECT mt_tici_score, COUNT(*) as count FROM tbl_or_procedure WHERE mt_tici_score IS NOT NULL GROUP BY mt_tici_score ORDER BY mt_tici_score";
$tici_data = fetchData($conn, $sql_tici);

// 6. กราฟ mRS
$sql_mrs = "SELECT discharge_mrs, COUNT(*) as count FROM tbl_ward WHERE discharge_mrs IS NOT NULL GROUP BY discharge_mrs ORDER BY discharge_mrs";
$mrs_data = fetchData($conn, $sql_mrs);

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
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        .kpi-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.2s;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .kpi-card:hover { transform: translateY(-3px); }
        .kpi-value { font-size: 2.5rem; font-weight: 700; line-height: 1.2; }
        .kpi-label { font-size: 0.9rem; text-transform: uppercase; opacity: 0.9; letter-spacing: 0.5px; }
        .kpi-icon { position: absolute; right: 15px; bottom: 10px; font-size: 3.5rem; opacity: 0.2; }
        
        .time-card {
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-left: 5px solid #ddd;
            height: 100%;
        }
        .time-val { font-size: 1.8rem; font-weight: bold; color: #333; }
        .time-unit { font-size: 0.9rem; color: #888; font-weight: normal; }
        
        .chart-box {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            height: 100%;
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
                <p class="text-muted mb-0">สรุปผลการดำเนินงานและตัวชี้วัด (KPIs)</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="export_excel.php" target="_blank" class="btn btn-success shadow-sm">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            <button onclick="exportPDF()" class="btn btn-danger shadow-sm">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="kpi-card bg-primary bg-gradient p-4">
                <div class="kpi-label">Total Stroke Cases</div>
                <div class="kpi-value"><?= number_format($summary['total']) ?> <span class="fs-6">ราย</span></div>
                <i class="bi bi-people-fill kpi-icon"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card bg-info bg-gradient p-4">
                <div class="kpi-label">Ischemic Stroke</div>
                <div class="kpi-value"><?= number_format($summary['ischemic']) ?> <span class="fs-6">ราย</span></div>
                <div class="progress mt-2" style="height: 4px; background: rgba(255,255,255,0.3);">
                    <div class="progress-bar bg-white" style="width: <?= $summary['total'] > 0 ? ($summary['ischemic']/$summary['total'])*100 : 0 ?>%"></div>
                </div>
                <i class="bi bi-droplet-half kpi-icon"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card bg-danger bg-gradient p-4">
                <div class="kpi-label">Hemorrhagic Stroke</div>
                <div class="kpi-value"><?= number_format($summary['hemorrhagic']) ?> <span class="fs-6">ราย</span></div>
                <div class="progress mt-2" style="height: 4px; background: rgba(255,255,255,0.3);">
                    <div class="progress-bar bg-white" style="width: <?= $summary['total'] > 0 ? ($summary['hemorrhagic']/$summary['total'])*100 : 0 ?>%"></div>
                </div>
                <i class="bi bi-bandaid kpi-icon"></i>
            </div>
        </div>
    </div>

    <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-stopwatch"></i> ประสิทธิภาพด้านเวลา (Time Performance Metrics)</h6>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="time-card" style="border-left-color: #4e73df;">
                <div class="text-muted small fw-bold">ONSET TO DOOR</div>
                <div class="time-val text-primary"><?= $times['avg_onset_to_door'] > 0 ? number_format($times['avg_onset_to_door'], 0) : '-' ?> <span class="time-unit">นาที</span></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="time-card" style="border-left-color: #1cc88a;">
                <div class="text-muted small fw-bold">DOOR TO CT</div>
                <div class="time-val text-success"><?= $times['avg_door_ct'] > 0 ? number_format($times['avg_door_ct'], 0) : '-' ?> <span class="time-unit">นาที</span></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="time-card" style="border-left-color: #f6c23e;">
                <div class="text-muted small fw-bold">DOOR TO NEEDLE</div>
                <div class="time-val text-warning"><?= $times['avg_door_needle'] > 0 ? number_format($times['avg_door_needle'], 0) : '-' ?> <span class="time-unit">นาที</span></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="time-card" style="border-left-color: #36b9cc;">
                <div class="text-muted small fw-bold">DOOR TO PUNCTURE</div>
                <div class="time-val text-info"><?= $times['avg_door_puncture'] > 0 ? number_format($times['avg_door_puncture'], 0) : '-' ?> <span class="time-unit">นาที</span></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="time-card" style="border-left-color: #6610f2;">
                <div class="text-muted small fw-bold">DOOR TO CONSULT</div>
                <div class="time-val" style="color: #6610f2;"><?= $times['avg_door_consult'] > 0 ? number_format($times['avg_door_consult'], 0) : '-' ?> <span class="time-unit">นาที</span></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="time-card" style="border-left-color: #fd7e14;">
                <div class="text-muted small fw-bold">CT TO NEEDLE</div>
                <div class="time-val" style="color: #fd7e14;"><?= $times['avg_ct_to_needle'] > 0 ? number_format($times['avg_ct_to_needle'], 0) : '-' ?> <span class="time-unit">นาที</span></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="time-card" style="border-left-color: #20c997;">
                <div class="text-muted small fw-bold">PROCEDURE TIME</div>
                <div class="time-val" style="color: #20c997;"><?= $times['avg_proc_time'] > 0 ? number_format($times['avg_proc_time'], 0) : '-' ?> <span class="time-unit">นาที</span></div>
                <div class="small text-muted" style="font-size: 10px;">(Puncture to Recanalization)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="time-card" style="border-left-color: #e74a3b;">
                <div class="text-muted small fw-bold">AVG. LENGTH OF STAY</div>
                <div class="time-val text-danger"><?= $times['avg_los'] > 0 ? number_format($times['avg_los'], 1) : '-' ?> <span class="time-unit">วัน</span></div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="chart-box">
                <h6 class="fw-bold text-secondary mb-3">ช่องทางการมา (Mode of Arrival)</h6>
                <div style="height: 250px;">
                    <canvas id="chartArrival"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-box">
                <h6 class="fw-bold text-secondary mb-3">การให้ยาละลายลิ่มเลือด (Fibrinolytic Type)</h6>
                <div style="height: 250px;">
                    <canvas id="chartFibro"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-box">
                <h6 class="fw-bold text-secondary mb-3">ผลการเปิดหลอดเลือด (TICI Score)</h6>
                <div style="height: 250px;">
                    <canvas id="chartTici"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-box">
                <h6 class="fw-bold text-secondary mb-3">ผลลัพธ์การรักษา (mRS at Discharge)</h6>
                <div style="height: 250px;">
                    <canvas id="chartMrs"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Data from PHP
    const arrivalData = <?= json_encode($arrival_data) ?>;
    const fibroData = <?= json_encode($fibro_data) ?>;
    const ticiData = <?= json_encode($tici_data) ?>;
    const mrsData = <?= json_encode($mrs_data) ?>;

    // 1. Arrival Chart
    new Chart(document.getElementById('chartArrival'), {
        type: 'doughnut',
        data: {
            labels: arrivalData.map(d => d.arrival_type),
            datasets: [{
                data: arrivalData.map(d => d.count),
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'],
            }]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
    });

    // 2. Fibro Chart
    new Chart(document.getElementById('chartFibro'), {
        type: 'pie',
        data: {
            labels: fibroData.map(d => d.fibrinolytic_type),
            datasets: [{
                data: fibroData.map(d => d.count),
                backgroundColor: ['#e74a3b', '#4e73df', '#1cc88a', '#858796'],
            }]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
    });

    // 3. TICI Chart
    new Chart(document.getElementById('chartTici'), {
        type: 'bar',
        data: {
            labels: ticiData.map(d => 'Score ' + d.mt_tici_score),
            datasets: [{
                label: 'Cases',
                data: ticiData.map(d => d.count),
                backgroundColor: '#36b9cc',
                borderRadius: 5
            }]
        },
        options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
    });

    // 4. mRS Chart
    new Chart(document.getElementById('chartMrs'), {
        type: 'bar',
        data: {
            labels: mrsData.map(d => 'mRS ' + d.discharge_mrs),
            datasets: [{
                label: 'Patients',
                data: mrsData.map(d => d.count),
                backgroundColor: '#f6c23e',
                borderRadius: 5
            }]
        },
        options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
    });
</script>
<script>
    function exportPDF() {
        const element = document.getElementById('reportContent');
        
        // ตั้งค่า PDF
        const opt = {
            margin:       [10, 10, 10, 10], // ขอบกระดาษ (มม.)
            filename:     'Stroke_Dashboard_Report.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 }, // ความชัด (2 = ชัดมาก)
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' } // แนวนอน
        };

        // สั่งปริ้น
        html2pdf().set(opt).from(element).save();
    }
</script>
</body>
</html>