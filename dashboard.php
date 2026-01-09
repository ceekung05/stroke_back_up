<?php
session_start();
require_once 'connectdb.php';

// Check Login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// --- 1. DATA CALCULATION (คำนวณตัวเลขทั้งหมด) ---

// Helper Function
function getAvgDuration($conn, $start_col, $end_col, $table_join = "") {
    $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, $start_col, $end_col)) as avg_min 
            FROM tbl_stroke_admission adm 
            $table_join 
            WHERE $start_col IS NOT NULL AND $end_col IS NOT NULL 
            AND $end_col > $start_col"; 
    $res = $conn->query($sql);
    return round($res->fetch_assoc()['avg_min'] ?? 0);
}

// [Time KPIs] (ค่าเฉลี่ยเวลาเดิม)
$avg_door2ct = getAvgDuration($conn, 'adm.hospital_arrival_datetime', 'er.ctnc_datetime', 'LEFT JOIN tbl_er er ON adm.id = er.admission_id');
$avg_door2neuro = getAvgDuration($conn, 'adm.hospital_arrival_datetime', 'er.consult_neuro_datetime', 'LEFT JOIN tbl_er er ON adm.id = er.admission_id');
$avg_door2inter = getAvgDuration($conn, 'adm.hospital_arrival_datetime', 'er.consult_intervention_datetime', 'LEFT JOIN tbl_er er ON adm.id = er.admission_id');
$avg_door2puncture = getAvgDuration($conn, 'adm.hospital_arrival_datetime', 'op.mt_puncture_datetime', 'LEFT JOIN tbl_or_procedure op ON adm.id = op.admission_id');
$avg_onset2recanal = getAvgDuration($conn, 'adm.onset_datetime', 'op.mt_recanalization_datetime', 'LEFT JOIN tbl_or_procedure op ON adm.id = op.admission_id');

// Door to Needle (Special Logic)
$sql_needle = "SELECT AVG(TIMESTAMPDIFF(MINUTE, adm.hospital_arrival_datetime, CONCAT(DATE(adm.hospital_arrival_datetime), ' ', er.tpa_start_time))) as avg_min 
               FROM tbl_stroke_admission adm LEFT JOIN tbl_er er ON adm.id = er.admission_id 
               WHERE er.fibrinolytic_type IN ('rtpa','sk','tnk') AND er.tpa_start_time IS NOT NULL";
$res_needle = $conn->query($sql_needle);
$avg_door2needle = round($res_needle->fetch_assoc()['avg_min'] ?? 0);

// --- [NEW] FORMULA CALCULATIONS (สูตรคำนวณร้อยละตามเกณฑ์มาตรฐาน) ---

// 1. สูตร % Door to Needle < 60 นาที (เป้าหมาย: > 50% ของคนไข้ทั้งหมด)
$sql_d2n_kpi = "SELECT 
                    COUNT(*) as total_cases,
                    SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, adm.hospital_arrival_datetime, CONCAT(DATE(adm.hospital_arrival_datetime), ' ', er.tpa_start_time)) <= 60 THEN 1 ELSE 0 END) as pass_cases
                FROM tbl_stroke_admission adm 
                JOIN tbl_er er ON adm.id = er.admission_id 
                WHERE er.fibrinolytic_type IN ('rtpa','sk','tnk') AND er.tpa_start_time IS NOT NULL";
$res_d2n_kpi = $conn->query($sql_d2n_kpi)->fetch_assoc();
$d2n_total = $res_d2n_kpi['total_cases'] > 0 ? $res_d2n_kpi['total_cases'] : 1;
$d2n_rate = round(($res_d2n_kpi['pass_cases'] / $d2n_total) * 100, 1);

// 2. สูตร % Door to Puncture < 90 นาที (เป้าหมาย: > 50%)
$sql_d2p_kpi = "SELECT 
                    COUNT(*) as total_cases,
                    SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, adm.hospital_arrival_datetime, op.mt_puncture_datetime) <= 90 THEN 1 ELSE 0 END) as pass_cases
                FROM tbl_stroke_admission adm 
                JOIN tbl_or_procedure op ON adm.id = op.admission_id 
                WHERE op.procedure_type = 'mt' AND op.mt_puncture_datetime IS NOT NULL";
$res_d2p_kpi = $conn->query($sql_d2p_kpi)->fetch_assoc();
$d2p_total = $res_d2p_kpi['total_cases'] > 0 ? $res_d2p_kpi['total_cases'] : 1;
$d2p_rate = round(($res_d2p_kpi['pass_cases'] / $d2p_total) * 100, 1);

// 3. สูตร % Successful Re-canalization (TICI Score 2b หรือ 3)
$sql_tici_kpi = "SELECT 
                    COUNT(*) as total_cases,
                    SUM(CASE WHEN mt_tici_score IN ('2b', '3') THEN 1 ELSE 0 END) as success_cases
                 FROM tbl_or_procedure 
                 WHERE procedure_type = 'mt' AND mt_tici_score IS NOT NULL";
$res_tici_kpi = $conn->query($sql_tici_kpi)->fetch_assoc();
$tici_total = $res_tici_kpi['total_cases'] > 0 ? $res_tici_kpi['total_cases'] : 1;
$tici_rate = round(($res_tici_kpi['success_cases'] / $tici_total) * 100, 1);

// 4. สูตร % Good Outcome at Discharge (mRS 0-2)
$sql_mrs_kpi = "SELECT 
                    COUNT(*) as total_cases,
                    SUM(CASE WHEN discharge_mrs <= 2 THEN 1 ELSE 0 END) as good_cases
                FROM tbl_ward 
                WHERE discharge_mrs IS NOT NULL";
$res_mrs_kpi = $conn->query($sql_mrs_kpi)->fetch_assoc();
$mrs_total = $res_mrs_kpi['total_cases'] > 0 ? $res_mrs_kpi['total_cases'] : 1;
$mrs_rate = round(($res_mrs_kpi['good_cases'] / $mrs_total) * 100, 1);


// [Overview Counts]
$res_total = $conn->query("SELECT COUNT(*) as total FROM tbl_stroke_admission");
$total_patients = $res_total->fetch_assoc()['total'] ?? 0;
$res_type = $conn->query("SELECT ct_result, COUNT(*) as count FROM tbl_er GROUP BY ct_result");
$type_data = ['ischemic' => 0, 'hemorrhagic' => 0];
while ($row = $res_type->fetch_assoc()) { if(isset($type_data[$row['ct_result']])) $type_data[$row['ct_result']] = $row['count']; }

// [Chart Data Queries]
// 1. Occlusion Site
$sql_occ = "SELECT occlusion_site, COUNT(*) as count FROM tbl_er WHERE occlusion_site IS NOT NULL AND occlusion_site != '' GROUP BY occlusion_site";
$res_occ = $conn->query($sql_occ); $occ_labels = []; $occ_counts = [];
while($row = $res_occ->fetch_assoc()){ $occ_labels[] = $row['occlusion_site']; $occ_counts[] = $row['count']; }

// 2. TICI Score
$sql_tici = "SELECT mt_tici_score, COUNT(*) as count FROM tbl_or_procedure WHERE mt_tici_score IS NOT NULL GROUP BY mt_tici_score ORDER BY mt_tici_score";
$res_tici = $conn->query($sql_tici); $tici_labels = []; $tici_counts = [];
while($row = $res_tici->fetch_assoc()){ $tici_labels[] = "TICI ".$row['mt_tici_score']; $tici_counts[] = $row['count']; }

// 3. Fibrinolytic Type
$sql_drug = "SELECT fibrinolytic_type, COUNT(*) as count FROM tbl_er WHERE fibrinolytic_type IS NOT NULL AND fibrinolytic_type != 'no' GROUP BY fibrinolytic_type";
$res_drug = $conn->query($sql_drug); $drug_labels = []; $drug_counts = [];
while($row = $res_drug->fetch_assoc()){ $drug_labels[] = strtoupper($row['fibrinolytic_type']); $drug_counts[] = $row['count']; }

// 4. Arrival Type
$sql_arr = "SELECT arrival_type, COUNT(*) as count FROM tbl_stroke_admission WHERE arrival_type IS NOT NULL GROUP BY arrival_type";
$res_arr = $conn->query($sql_arr); $arr_labels = []; $arr_counts = [];
while($row = $res_arr->fetch_assoc()){ $arr_labels[] = strtoupper($row['arrival_type']); $arr_counts[] = $row['count']; }

// 5. Complications
$sql_comp = "SELECT complication_details, COUNT(*) as count FROM tbl_or_procedure WHERE complication_details IS NOT NULL AND complication_details != 'ไม่มีภาวะแทรกซ้อน' AND complication_details != '' GROUP BY complication_details";
$res_comp = $conn->query($sql_comp); $comp_labels = []; $comp_counts = [];
while($row = $res_comp->fetch_assoc()){ $comp_labels[] = $row['complication_details']; $comp_counts[] = $row['count']; }

// 6. Hemorrhagic Location
$sql_hemo = "SELECT hemo_location, COUNT(*) as count FROM tbl_or_procedure WHERE procedure_type = 'hemo' AND hemo_location IS NOT NULL AND hemo_location != '' GROUP BY hemo_location";
$res_hemo = $conn->query($sql_hemo); $hemo_labels = []; $hemo_counts = [];
while($row = $res_hemo->fetch_assoc()){ $hemo_labels[] = $row['hemo_location']; $hemo_counts[] = $row['count']; }

// 7. mRS Outcomes
$mrs_dc_data = array_fill(0, 7, 0);
$res_mrs_dc = $conn->query("SELECT discharge_mrs, COUNT(*) as count FROM tbl_ward WHERE discharge_mrs IS NOT NULL GROUP BY discharge_mrs");
while($row = $res_mrs_dc->fetch_assoc()){ $mrs_dc_data[$row['discharge_mrs']] = $row['count']; }

$mrs_3mo_data = array_fill(0, 7, 0);
$res_mrs_3mo = $conn->query("SELECT mrs_score, COUNT(*) as count FROM tbl_followup WHERE followup_label LIKE '%3 เดือน%' AND status = 'attended' AND mrs_score IS NOT NULL GROUP BY mrs_score");
while($row = $res_mrs_3mo->fetch_assoc()){ $mrs_3mo_data[$row['mrs_score']] = $row['count']; }

// 8. Recent Table
$sql_recent = "SELECT adm.id, adm.onset_datetime, pat.hn, pat.flname, er.ct_result, ward.discharge_status 
               FROM tbl_stroke_admission adm 
               LEFT JOIN tbl_patient pat ON adm.patient_hn = pat.hn 
               LEFT JOIN tbl_er er ON adm.id = er.admission_id 
               LEFT JOIN tbl_ward ward ON adm.id = ward.admission_id 
               ORDER BY adm.created_at DESC LIMIT 5";
$res_recent = $conn->query($sql_recent);
$recent_patients = [];
while($row = $res_recent->fetch_assoc()) { $recent_patients[] = $row; }

// --- Prepare Data for JavaScript (Export & Charts) ---
$export_kpi = [
    ['KPI Name', 'Value (Minutes)', 'Target'],
    ['Door to CT', $avg_door2ct, '< 25'],
    ['Door to Neuro', $avg_door2neuro, '< 15'],
    ['Door to Intervention', $avg_door2inter, '-'],
    ['Door to Needle', $avg_door2needle, '< 60'],
    ['Door to Puncture', $avg_door2puncture, '< 90'],
    ['Onset to Recanalization', $avg_onset2recanal, '-'],
    ['Total Patients', $total_patients, '-']
];

$export_patients = [];
foreach($recent_patients as $p) {
    $export_patients[] = [
        'HN' => $p['hn'],
        'Name' => $p['flname'],
        'Onset Date' => $p['onset_datetime'],
        'Type' => $p['ct_result'],
        'Status' => $p['discharge_status']
    ];
}

$json_export_kpi = json_encode($export_kpi);
$json_export_patients = json_encode($export_patients);

// JSON for Charts
$json_occ_labels = json_encode($occ_labels); $json_occ_counts = json_encode($occ_counts);
$json_tici_labels = json_encode($tici_labels); $json_tici_counts = json_encode($tici_counts);
$json_drug_labels = json_encode($drug_labels); $json_drug_counts = json_encode($drug_counts);
$json_arr_labels = json_encode($arr_labels); $json_arr_counts = json_encode($arr_counts);
$json_comp_labels = json_encode($comp_labels); $json_comp_counts = json_encode($comp_counts);
$json_hemo_labels = json_encode($hemo_labels); $json_hemo_counts = json_encode($hemo_counts);
$json_mrs_dc = json_encode($mrs_dc_data); $json_mrs_3mo = json_encode($mrs_3mo_data);

?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stroke Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css"> 

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script> <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script> <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script> <style>
        :root {
            --primary-bg: #f3f6f9; --card-bg: #ffffff;
            --accent-blue: #0984e3; --accent-green: #00b894;
            --accent-red: #d63031; --accent-yellow: #fdcb6e; --accent-purple: #6c5ce7;
            --card-radius: 12px;
        }
        body { background-color: var(--primary-bg); font-family: 'Sarabun', sans-serif; }
        .main { padding: 2rem; min-height: 100vh; }
        
        /* KPI Cards */
        .kpi-card {
            background: var(--card-bg); border-radius: var(--card-radius); padding: 1.2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03); height: 100%; border-left: 5px solid transparent;
            transition: transform 0.2s;
        }
        .kpi-card:hover { transform: translateY(-3px); }
        .kpi-card.blue { border-color: var(--accent-blue); }
        .kpi-card.green { border-color: var(--accent-green); }
        .kpi-card.red { border-color: var(--accent-red); }
        .kpi-card.yellow { border-color: var(--accent-yellow); }
        .kpi-card.purple { border-color: var(--accent-purple); }
        
        .kpi-title { font-size: 0.85rem; color: #636e72; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .kpi-value { font-size: 2rem; font-weight: 700; color: #2d3436; margin: 8px 0; }
        .kpi-unit { font-size: 0.8rem; color: #b2bec3; }
        .kpi-target { font-size: 0.75rem; margin-top: 8px; padding-top: 8px; border-top: 1px solid #f1f2f6; display: flex; justify-content: space-between; }

        /* Chart Cards */
        .chart-card {
            background: #fff; border-radius: var(--card-radius); padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03); height: 100%;
        }
        .chart-title { font-weight: 700; color: #2d3436; margin-bottom: 1rem; border-bottom: 1px solid #f1f2f6; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .canvas-wrapper { position: relative; height: 220px; width: 100%; }

        /* Badge Soft */
        .badge-soft { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .bg-soft-blue { background: rgba(9, 132, 227, 0.1); color: var(--accent-blue); }
        .bg-soft-red { background: rgba(214, 48, 49, 0.1); color: var(--accent-red); }
        .bg-soft-green { background: rgba(0, 184, 148, 0.1); color: var(--accent-green); }

        /* Export Button */
        .btn-export { background: #fff; border: 1px solid #dfe6e9; color: #2d3436; font-weight: 600; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .btn-export:hover { background: #f8f9fa; color: var(--accent-blue); border-color: var(--accent-blue); }
        
        /* Print/PDF Mode */
        @media print {
            .sidebar, .btn-export, .no-print { display: none !important; }
            .main { margin-left: 0 !important; padding: 0 !important; background: #fff; }
            .card, .kpi-card, .chart-card { box-shadow: none !important; border: 1px solid #eee !important; break-inside: avoid; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="bi bi-heart-pulse-fill"></i>
            <span>Stroke Care</span>
        </div>
        <hr class="">
        <a href="dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="index.php"><i class="bi bi-list-task"></i> รายชื่อผู้ป่วย (Patient List)</a>
        <hr class="sidebar-divider">
        <a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>

    <div class="main" id="dashboard-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-bold text-dark mb-0">Executive Dashboard</h2>
                <p class="text-muted mb-0">Stroke Fast Track Performance Indicators</p>
            </div>
            <div class="d-flex gap-2 no-print">
                <button onclick="exportExcel()" class="btn btn-export rounded-pill px-3">
                    <i class="bi bi-file-earmark-excel text-success me-2"></i>Export Excel
                </button>
                <button onclick="exportPDF()" class="btn btn-export rounded-pill px-3">
                    <i class="bi bi-file-earmark-pdf text-danger me-2"></i>Export PDF
                </button>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12"><h6 class="text-secondary fw-bold text-uppercase small"><i class="bi bi-stopwatch me-2"></i>Time Performance (Minutes)</h6></div>
            
            <div class="col-md-2 col-6">
                <div class="kpi-card blue">
                    <div class="kpi-title">Door to CT</div>
                    <div class="kpi-value"><?= $avg_door2ct ?></div>
                    <div class="kpi-target"><span>Goal:</span> <span class="text-success fw-bold">< 25</span></div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="kpi-card blue">
                    <div class="kpi-title">Door to Neuro</div>
                    <div class="kpi-value"><?= $avg_door2neuro ?></div>
                    <div class="kpi-target"><span>Goal:</span> <span class="text-success fw-bold">< 15</span></div>
                </div>
            </div>
             <div class="col-md-2 col-6">
                <div class="kpi-card purple">
                    <div class="kpi-title">Door to Interv.</div>
                    <div class="kpi-value"><?= $avg_door2inter ?></div>
                    <div class="kpi-target"><span>Goal:</span> <span>N/A</span></div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="kpi-card yellow">
                    <div class="kpi-title">Door to Needle</div>
                    <div class="kpi-value"><?= $avg_door2needle ?></div>
                    <div class="kpi-target"><span>Goal:</span> <span class="text-success fw-bold">< 60</span></div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="kpi-card red">
                    <div class="kpi-title">Door to Puncture</div>
                    <div class="kpi-value"><?= $avg_door2puncture ?></div>
                    <div class="kpi-target"><span>Goal:</span> <span class="text-success fw-bold">< 90</span></div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="kpi-card green">
                    <div class="kpi-title">Onset to Recanal.</div>
                    <div class="kpi-value"><?= $avg_onset2recanal ?></div>
                    <div class="kpi-target"><span>Goal:</span> <span class="text-success fw-bold">Fastest</span></div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12"><h6 class="text-secondary fw-bold text-uppercase small"><i class="bi bi-bullseye me-2"></i>Performance Indicators (Compliance Rates)</h6></div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0 text-secondary" style="font-size: 0.9rem;">DTN < 60 mins</h6>
                        <span class="badge <?= $d2n_rate >= 50 ? 'bg-success' : 'bg-warning text-dark' ?> rounded-pill"><?= $d2n_rate ?>%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar <?= $d2n_rate >= 50 ? 'bg-success' : 'bg-warning' ?>" role="progressbar" style="width: <?= $d2n_rate ?>%"></div>
                    </div>
                    <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                        <i class="bi bi-check-circle me-1"></i>ผ่านเกณฑ์: <?= $res_d2n_kpi['pass_cases'] ?> / <?= $res_d2n_kpi['total_cases'] ?> เคส
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0 text-secondary" style="font-size: 0.9rem;">DTP < 90 mins</h6>
                        <span class="badge <?= $d2p_rate >= 50 ? 'bg-success' : 'bg-danger' ?> rounded-pill"><?= $d2p_rate ?>%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar <?= $d2p_rate >= 50 ? 'bg-success' : 'bg-danger' ?>" role="progressbar" style="width: <?= $d2p_rate ?>%"></div>
                    </div>
                    <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                         <i class="bi bi-check-circle me-1"></i>ผ่านเกณฑ์: <?= $res_d2p_kpi['pass_cases'] ?> / <?= $res_d2p_kpi['total_cases'] ?> เคส
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0 text-secondary" style="font-size: 0.9rem;">TICI Score 2b-3</h6>
                        <span class="badge bg-primary rounded-pill"><?= $tici_rate ?>%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $tici_rate ?>%"></div>
                    </div>
                    <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                         <i class="bi bi-trophy me-1"></i>สำเร็จ: <?= $res_tici_kpi['success_cases'] ?> / <?= $res_tici_kpi['total_cases'] ?> เคส
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0 text-secondary" style="font-size: 0.9rem;">Good Outcome (mRS 0-2)</h6>
                        <span class="badge bg-info text-dark rounded-pill"><?= $mrs_rate ?>%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $mrs_rate ?>%"></div>
                    </div>
                    <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                         <i class="bi bi-emoji-smile me-1"></i>อาการดี: <?= $res_mrs_kpi['good_cases'] ?> / <?= $res_mrs_kpi['total_cases'] ?> เคส
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="chart-title">
                        <span><i class="bi bi-bar-chart-fill me-2 text-primary"></i>mRS Outcome</span>
                        <span class="badge bg-light text-muted border fw-normal">Discharge vs 3 Mo.</span>
                    </div>
                    <div class="canvas-wrapper"><canvas id="mrsChart"></canvas></div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="chart-card">
                    <div class="chart-title"><span><i class="bi bi-diagram-3 me-2 text-info"></i>Occlusion Site</span></div>
                    <div class="canvas-wrapper"><canvas id="occChart"></canvas></div>
                </div>
            </div>
             <div class="col-lg-3">
                <div class="chart-card">
                    <div class="chart-title"><span><i class="bi bi-ambulance me-2 text-warning"></i>Arrival Type</span></div>
                    <div class="canvas-wrapper"><canvas id="arrChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="chart-card">
                    <div class="chart-title">Fibrinolytic Agent</div>
                    <div class="canvas-wrapper"><canvas id="drugChart"></canvas></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="chart-card">
                    <div class="chart-title">TICI Score</div>
                    <div class="canvas-wrapper"><canvas id="ticiChart"></canvas></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="chart-card">
                    <div class="chart-title">Complications</div>
                    <div class="canvas-wrapper"><canvas id="compChart"></canvas></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="chart-card">
                    <div class="chart-title">Hemo. Location</div>
                    <div class="canvas-wrapper"><canvas id="hemoChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm p-4 rounded-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-secondary mb-0"><i class="bi bi-table me-2"></i>Latest Admissions</h6>
                <span class="badge bg-primary bg-opacity-10 text-primary">Total: <?= number_format($total_patients) ?> cases</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light text-secondary">
                        <tr><th>HN</th><th>Name</th><th>Onset Date</th><th>Type</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_patients as $pt): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= $pt['hn'] ?></td>
                            <td><?= $pt['flname'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($pt['onset_datetime'])) ?></td>
                            <td>
                                <?php if($pt['ct_result']=='ischemic'): ?><span class="badge-soft bg-soft-blue">Ischemic</span>
                                <?php elseif($pt['ct_result']=='hemorrhagic'): ?><span class="badge-soft bg-soft-red">Hemorrhagic</span>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= ucfirst($pt['discharge_status'] ?? 'Active') ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="text-center text-muted small mt-5 pt-4 border-top">
            Generated by Stroke Care System on <?= date('d/m/Y H:i') ?>
        </div>

    </div>

    <script>
        const exportKPI = <?= $json_export_kpi ?>;
        const exportPatients = <?= $json_export_patients ?>;
    </script>

    <script>
        // 1. Export Excel
        function exportExcel() {
            // Create Workbook
            const wb = XLSX.utils.book_new();
            
            // Sheet 1: KPIs
            const ws_kpi = XLSX.utils.aoa_to_sheet(exportKPI);
            XLSX.utils.book_append_sheet(wb, ws_kpi, "Summary KPIs");

            // Sheet 2: Patients
            const ws_pat = XLSX.utils.json_to_sheet(exportPatients);
            XLSX.utils.book_append_sheet(wb, ws_pat, "Recent Patients");

            // Download
            XLSX.writeFile(wb, "Stroke_Dashboard_Report.xlsx");
        }

        // 2. Export PDF
        async function exportPDF() {
            const { jsPDF } = window.jspdf;
            const content = document.getElementById('dashboard-content');
            
            // ซ่อนปุ่มก่อน Export
            document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');
            document.querySelector('.sidebar').style.display = 'none'; // ซ่อน Sidebar ชั่วคราว
            content.style.marginLeft = '0'; // ขยับเนื้อหาไปซ้ายสุด
            
            // Capture Screen
            const canvas = await html2canvas(content, { scale: 2 });
            const imgData = canvas.toDataURL('image/png');
            
            // Create PDF (A4)
            const pdf = new jsPDF('p', 'mm', 'a4');
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
            
            pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
            pdf.save("Stroke_Executive_Report.pdf");

            // คืนค่าการแสดงผล
            document.querySelectorAll('.no-print').forEach(el => el.style.display = 'flex');
            document.querySelector('.sidebar').style.display = 'block';
            content.style.marginLeft = '250px';
        }

        // 3. Render Charts
        Chart.defaults.font.family = "'Sarabun', sans-serif";
        Chart.defaults.color = '#636e72';
        const commonOpt = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 15 } } } };

        new Chart(document.getElementById('mrsChart'), {
            type: 'bar',
            data: {
                labels: ['0','1','2','3','4','5','6 (Dead)'],
                datasets: [
                    { label: 'Discharge', data: <?= $json_mrs_dc ?>, backgroundColor: '#b2bec3' },
                    { label: '3 Month', data: <?= $json_mrs_3mo ?>, backgroundColor: '#00b894' }
                ]
            }, options: commonOpt
        });

        new Chart(document.getElementById('occChart'), {
            type: 'bar',
            data: { labels: <?= $json_occ_labels ?>, datasets: [{ label: 'Cases', data: <?= $json_occ_counts ?>, backgroundColor: '#0984e3' }] },
            options: { ...commonOpt, indexAxis: 'y', plugins: { legend: { display: false } } }
        });

        new Chart(document.getElementById('arrChart'), {
            type: 'doughnut',
            data: { labels: <?= $json_arr_labels ?>, datasets: [{ data: <?= $json_arr_counts ?>, backgroundColor: ['#fdcb6e','#0984e3','#636e72','#d63031'] }] },
            options: { ...commonOpt, cutout: '60%' }
        });

        new Chart(document.getElementById('drugChart'), {
            type: 'pie',
            data: { labels: <?= $json_drug_labels ?>, datasets: [{ data: <?= $json_drug_counts ?>, backgroundColor: ['#6c5ce7','#e84393','#00cec9'] }] },
            options: commonOpt
        });

        new Chart(document.getElementById('ticiChart'), {
            type: 'doughnut',
            data: { labels: <?= $json_tici_labels ?>, datasets: [{ data: <?= $json_tici_counts ?>, backgroundColor: ['#d63031','#e17055','#fdcb6e','#00b894','#2d3436'] }] },
            options: { ...commonOpt, cutout: '50%' }
        });

        new Chart(document.getElementById('compChart'), {
            type: 'bar',
            data: { labels: <?= $json_comp_labels ?>, datasets: [{ label: 'Cases', data: <?= $json_comp_counts ?>, backgroundColor: '#d63031' }] },
            options: { ...commonOpt, plugins: { legend: { display: false } }, scales: { x: { display: false } } }
        });

        new Chart(document.getElementById('hemoChart'), {
            type: 'bar',
            data: { labels: <?= $json_hemo_labels ?>, datasets: [{ label: 'Cases', data: <?= $json_hemo_counts ?>, backgroundColor: '#e17055' }] },
            options: { ...commonOpt, indexAxis: 'y', plugins: { legend: { display: false } } }
        });
    </script>
</body>
</html>