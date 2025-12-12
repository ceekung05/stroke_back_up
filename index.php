<?php
session_start();
require_once 'connectdb.php'; 

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user_data'];

// ดึงข้อมูล
$sql = "SELECT 
            adm.id AS admission_id,
            adm.patient_hn,
            adm.onset_datetime,
            pat.flname,
            er.ct_result,
            ward.discharge_status
        FROM tbl_stroke_admission adm
        LEFT JOIN tbl_patient pat ON adm.patient_hn = pat.hn
        LEFT JOIN tbl_er er ON adm.id = er.admission_id
        LEFT JOIN tbl_ward ward ON adm.id = ward.admission_id
        ORDER BY adm.created_at DESC";
$result = $conn->query($sql);
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
        <div class="sidebar-header"><i class="bi bi-heart-pulse-fill"></i> <span>Stroke Care</span></div>
        <a href="index.php" class="active"><i class="bi bi-list-task"></i> Patient List</a>
        <a href="form.php"><i class="bi bi-person-plus-fill"></i> บันทึกข้อมูลแรกรับ</a>
        <hr class="sidebar-divider">
        <a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a>
    </div>

    <div class="main">
        <div class="header-section">
            <div class="icon-container"><i class="bi bi-list-task"></i></div>
            <div class="title-container">
                <h1>Patient List</h1>
                <p class="subtitle mb-0">รายชื่อผู้ป่วย</p>
            </div>
        </div>
        <div class="mb-3">
            <a href="form.php" class="btn btn-primary btn-lg"><i class="bi bi-person-plus-fill"></i> เพิ่มผู้ป่วยใหม่</a>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>HN</th><th>ชื่อ-สกุล</th><th>Type</th><th>Date</th><th>Status</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): 
                                $status = !empty($row['discharge_status']) ? '<span class="badge bg-success">Discharged</span>' : '<span class="badge bg-warning text-dark">Admitted</span>';
                                $onset = !empty($row['onset_datetime']) ? date('d/m/Y H:i', strtotime($row['onset_datetime'])) : '-';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['patient_hn']) ?></td>
                                <td><?= htmlspecialchars($row['flname']) ?></td>
                                <td><?= htmlspecialchars($row['ct_result'] ?? '-') ?></td>
                                <td><?= $onset ?></td>
                                <td><?= $status ?></td>
                                <td>
                                    <a href="form.php?admission_id=<?= $row['admission_id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil-square"></i> แก้ไข
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center">ไม่มีข้อมูล</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>