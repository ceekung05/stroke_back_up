<?php
session_start();
require_once 'connectdb.php'; 

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user_data'];

// --- ส่วนจัดการตัวกรอง (Filter & Search) ---
$search_text = $_GET['search'] ?? '';
$filter_year = $_GET['year'] ?? ''; 

// สร้างเงื่อนไข SQL (WHERE clause)
$conditions = ["1=1"];
$params = [];
$types = "";

// 1. กรองตามคำค้นหา (HN, AN หรือ ชื่อ-สกุล)
if (!empty($search_text)) {
    // เพิ่มการค้นหาด้วย AN (adm.patient_an)
    $conditions[] = "(adm.patient_hn LIKE ? OR adm.patient_an LIKE ? OR pat.flname LIKE ?)";
    $search_param = "%{$search_text}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

// 2. กรองตามปี (ใช้ created_at ของ Admission)
if (!empty($filter_year)) {
    $conditions[] = "YEAR(adm.created_at) = ?";
    $params[] = $filter_year;
    $types .= "s";
}

// รวมเงื่อนไข
$where_sql = "WHERE " . implode(" AND ", $conditions);

// --- [แก้ไขจุดที่ 1] เพิ่มการดึงค่า patient_an และ date_admit ---
$sql = "SELECT 
            adm.id AS admission_id,
            adm.patient_hn,
            adm.patient_an,   /* <-- เพิ่ม AN */
            adm.date_admit,   /* <-- เพิ่ม Date Admit */
            adm.created_at, 
            pat.flname,
            er.ct_result,
            ward.discharge_status
        FROM tbl_stroke_admission adm
        LEFT JOIN tbl_patient pat ON adm.patient_hn = pat.hn
        LEFT JOIN tbl_er er ON adm.id = er.admission_id
        LEFT JOIN tbl_ward ward ON adm.id = ward.admission_id
        $where_sql
        ORDER BY adm.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// ดึงรายการปีมาทำ Dropdown
$year_sql = "SELECT DISTINCT YEAR(created_at) as year_val FROM tbl_stroke_admission ORDER BY year_val DESC";
$year_query = $conn->query($year_sql);
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Patient List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="bi bi-heart-pulse-fill"></i>
            <span>Stroke Care</span>
        </div>
        <hr class="sidebar-divider">
        <a href="dashboard.php" >
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        
        <a href="index.php" class="active">
            <i class="bi bi-list-task"></i> รายชื่อผู้ป่วย (Patient List)
        </a>
        
        <hr class="sidebar-divider">
        
        <a href="form.php">
            <i class="bi bi-person-plus-fill"></i> ลงทะเบียนผู้ป่วยใหม่
        </a>

        <div style="flex-grow: 1;"></div> <hr class="sidebar-divider">
        <a href="logout.php" class="text-danger">
            <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
        </a>
    </div>

    <div class="main">
        
        <div class="header-section">
            <div class="icon-container">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="title-container">
                <h1>Patient Registry</h1>
                <p class="subtitle mb-0">ทะเบียนรายชื่อผู้ป่วยโรคหลอดเลือดสมอง</p>
            </div>
            <div class="ms-auto">
                <a href="form.php" class="btn btn-primary shadow-sm rounded-pill px-4 py-2">
                    <i class="bi bi-plus-lg me-2"></i> ลงทะเบียนผู้ป่วยใหม่
                </a>
            </div>
        </div>

        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 15px;">
            <div class="card-body p-3">
                <form method="GET" action="index.php" class="row g-2 align-items-center">
                    <div class="col-md-2">
                        <select name="year" class="form-select border-primary" onchange="this.form.submit()">
                            <option value="">-- ปีทั้งหมด --</option>
                            <?php while($y = $year_query->fetch_assoc()): ?>
                                <option value="<?= $y['year_val'] ?>" <?= ($filter_year == $y['year_val']) ? 'selected' : '' ?>>
                                    ปี <?= $y['year_val'] + 543 ?> (<?= $y['year_val'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 border-primary"><i class="bi bi-search text-primary"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 border-primary" placeholder="ค้นหา HN, AN หรือ ชื่อ-นามสกุล..." value="<?= htmlspecialchars($search_text) ?>">
                            <button class="btn btn-primary" type="submit">ค้นหา</button>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php if(!empty($search_text) || !empty($filter_year)): ?>
                            <a href="index.php" class="btn btn-outline-danger btn-sm rounded-pill">
                                <i class="bi bi-x-circle"></i> ล้างตัวกรอง
                            </a>
                        <?php endif; ?>
                        <span class="text-muted small ms-2">พบข้อมูล <?= $result->num_rows ?> รายการ</span>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-form p-0 overflow-hidden"> 
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <!-- [แก้ไขจุดที่ 2] เปลี่ยนชื่อหัวตาราง -->
                            <th class="ps-4 py-3">HN / AN</th>
                            <th class="py-3">ชื่อ-นามสกุล</th>
                            <th class="py-3">ประเภท (Diagnosis)</th>
                            <th class="py-3">Date Admit</th> <!-- เปลี่ยนจาก Onset Date -->
                            <th class="py-3">วันที่ลงทะเบียน</th> 
                            <th class="py-3">สถานะ (Status)</th>
                            <th class="text-center py-3">จัดการ</th>
                            <th class="text-center py-3">ติดตามอาการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): 
                                // สถานะ
                                $status_badge = '';
                                if(!empty($row['discharge_status'])) {
                                    $status_badge = '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3"><i class="bi bi-check-circle-fill me-1"></i> Discharged</span>';
                                } else {
                                    $status_badge = '<span class="badge bg-warning bg-opacity-10 text-warning-emphasis rounded-pill px-3"><i class="bi bi-hospital me-1"></i> Admitted</span>';
                                }

                                // [แก้ไขจุดที่ 3] เปลี่ยนการแสดงผลวันที่เป็น Date Admit
                                $date_admit_show = !empty($row['date_admit']) ? date('d/m/Y', strtotime($row['date_admit'])) : '<span class="text-muted">-</span>';
                                
                                // วันที่ลงทะเบียน
                                $regis_date = !empty($row['created_at']) ? date('d/m/Y', strtotime($row['created_at'])) : '-';

                                // CT Result Badge
                                $ct_badge = '<span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Pending</span>';
                                if(isset($row['ct_result'])) {
                                    if($row['ct_result'] == 'ischemic') {
                                        $ct_badge = '<span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">Ischemic</span>';
                                    } elseif($row['ct_result'] == 'hemorrhagic') {
                                        $ct_badge = '<span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Hemorrhagic</span>';
                                    }
                                }
                            ?>
                            <tr style="cursor: pointer;" onclick="window.location='form.php?admission_id=<?= $row['admission_id'] ?>'">
                                <!-- [แก้ไขจุดที่ 4] แสดง HN และ AN คู่กัน -->
                                <td class="ps-4">
                                    <div class="fw-bold text-primary">HN: <?= htmlspecialchars($row['patient_hn']) ?></div>
                                    <div class="small text-muted" style="font-size: 0.85rem;">
                                        <i class="bi bi-hash"></i> AN: <?= !empty($row['patient_an']) ? htmlspecialchars($row['patient_an']) : '-' ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($row['flname']) ?></div>
                                </td>
                                <td><?= $ct_badge ?></td>
                                
                                <!-- แสดง Date Admit -->
                                <td class="text-primary fw-bold small"><?= $date_admit_show ?></td>
                                
                                <td class="text-muted small"><?= $regis_date ?></td> 
                                <td><?= $status_badge ?></td>
                                <td class="text-center">
                                    <a href="form.php?admission_id=<?= $row['admission_id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="bi bi-pencil-square me-1"></i> ดูข้อมูล
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a href="follow.php?admission_id=<?= $row['admission_id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="bi bi-pencil-square me-1"></i> ติดตามอาการ
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted"> <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                    <?php if(!empty($search_text) || !empty($filter_year)): ?>
                                        ไม่พบข้อมูลที่ค้นหา
                                    <?php else: ?>
                                        ยังไม่มีข้อมูลผู้ป่วยในระบบ
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>