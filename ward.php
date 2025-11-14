<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user_data'];
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>4. Ward - ระบบ Stroke Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <i class="bi bi-heart-pulse-fill"></i>
            <span>Stroke Care</span>
        </div>
        <a href="index.php">
            <i class="bi bi-list-task"></i> กลับไปหน้า Patient List
        </a>
        <hr class="sidebar-divider">
        <a href="form.php">
            <i class="bi bi-person-lines-fill"></i> 1. ข้อมูลทั่วไป
        </a>
        <a href="diagnosis_form.php">
            <i class="bi bi-hospital"></i> 2. ER
        </a>
        <a href="OR_Procedure_Form.php">
            <i class="bi bi-scissors"></i> 3. OR Procedure
        </a>
        <a href="ward.php" class="active">
            <i class="bi bi-building-check"></i> 4. Ward
        </a>
        <a href="follow.php">
            <i class="bi bi-calendar-check"></i> 5. Follow Up
        </a>
        <hr class="sidebar-divider">
        <a href="logout.php" class="text-danger">
            <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
        </a>
    </div>

    <div class="main">
        <div class="header-section">
            <div class="icon-container">
                <i class="bi bi-building-check"></i>
            </div>
            <div class="title-container">
                <h1>4. Ward Monitoring</h1>
                <p class="subtitle mb-0">หน้าจอหอผู้ป่วย (Flowsheet)</p>
            </div>
        </div>
        <div class="section-title">
            <i class="bi bi-graph-up"></i> 1. การเฝ้าระวัง (Monitoring)
        </div>
        <div class="mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEntryModal">
                <i class="bi bi-plus-circle"></i> เพิ่มบันทึกการเฝ้าระวัง
            </button>
        </div>
        <table class="table table-striped table-hover table-bordered">
            <thead class="table-light">
                <tr>
                    <th>วันที่/เวลา</th>
                    <th>SBP(mmHg)</th>
                    <th>DBP(mmHg)</th>
                    <th>NIHSS</th>
                    <th>GCS</th>
                </tr>
            </thead>
            <tbody id="monitoringTableBody">
                <tr>
                    <td>30/10/2025 10:00</td>
                    <td>140</td>
                    <td>...</td>
                    <td>10</td>
                    <td>E4M6V5</td>
                </tr>
            </tbody>
        </table>
        <div class="section-title">
            <i class="bi bi-clipboard2-data"></i> 2. การตรวจติดตาม
        </div>
        <div class="row g-3">
            <div class="col-md-3 mb-3">
                <label for="ctFirstDay" class="form-label">ส่งตรวจ CT brain</label>
                <input type="date" class="form-control " id="ctFirstDay" >
                <input type="time" name="" id="" class="form-control mt-2">
            </div>
            <div class="col-3 mb-3">
                <label class="form-label fw-bold" for="ct_result_ward">ผล:</label>
                <input type="text" class="form-control">
            </div>
        </div>
        <div class="section-title">
            <i class="bi bi-person-check"></i> 3. อาการก่อนจำหน่าย
        </div>
        <div class="row mb-4">
            <div class="col-md-2">
                <label for="discharge_check_date">ประจำวันที่</label>
                <input type="date" class="form-control" id="discharge_check_date" >
            </div>
            <div class="col-md-2 mt-auto">
                <input type="time" name="" id="" class="form-control">
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-3">
                <label for="mrsDischarge" class="form-label">mRS (ณ วันจำหน่าย)</label>
                <select class="form-select" id="mrsDischarge">
                    <option value="" selected disabled>-- กรุณาเลือก --</option>
                    <option value="0">0 - No symptoms</option>
                    <option value="1">1 - No significant disability</option>
                    <option value="2">2 - Slight disability</option>
                    <option value="3">3 - Moderate disability</option>
                    <option value="4">4 - Moderately severe disability</option>
                    <option value="5">5 - Severe disability</option>
                    <option value="6">6 - Dead</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="barthel" class="form-label">Barthel Index</label>
                <input type="number" class="form-control" id="barthel">
            </div>
            <div class="col-md-3">
                <label for="hrs" class="form-label">HRS</label>
                <input type="number" class="form-control" id="hrs">
            </div>
        </div>
        <div class="section-title">
            <i class="bi bi-person-check"></i> 4. สภาพจำหน่าย
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">การวางแผนจำหน่าย</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="discharge_planning" id="plan_came" value="came">
                    <label class="form-check-label" for="plan_came">
                        มา
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="discharge_planning" id="plan_not_came" value="not_came" checked>
                    <label class="form-check-label" for="plan_not_came">
                        ไม่มา
                    </label>
                </div>
                <div class="mt-3" id="discharge_date_field" style="display: none;">
                    <label for="discharge_date_input" class="form-label">วันที่:</label>
                    <input type="text" class="form-control datepicker-no-time" id="discharge_date_input" name="discharge_date" placeholder="วัน/เดือน/ปี">
                </div>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-bold">สถานะจำหน่าย</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="discharge_status" id="status_recovery" value="recovery">
                    <label class="form-check-label" for="status_recovery">
                        complete recovery
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="discharge_status" id="status_improve" value="improve">
                    <label class="form-check-label" for="status_improve">
                        improve
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="discharge_status" id="status_disability" value="disability">
                    <label class="form-check-label" for="status_disability">
                        Disability
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="discharge_status" id="status_refer" value="refer">
                    <label class="form-check-label" for="status_refer">
                        Refer back
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="discharge_status" id="status_against" value="against">
                    <label class="form-check-label" for="status_against">
                        against advice
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="discharge_status" id="status_death" value="death">
                    <label class="form-check-label" for="status_death">
                        Death
                    </label>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="addEntryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">➕ เพิ่มบันทึกการเฝ้าระวังใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="wardEntryForm">
                        <div class="mb-3">
                            <label for="modalDateTime" class="form-label">วันที่/เวลา (Date/Time)</label>
                            <input type="datetime-local" class="form-control " id="modalDateTime" placeholder="วัน/เดือน/ปี">
                        </div>
                        <div class="mb-3">
                            <label for="modalSBP" class="form-label">SBP (mmHg)</label>
                            <input type="number" class="form-control" id="modalSBP">
                        </div>
                        <div class="mb-3">
                            <label for="modalDBP" class="form-label">DBP(mmHg)</label>
                            <input type="number" class="form-control" id="modalDBP">
                        </div>
                        <div class="mb-3">
                            <label for="modalNIHSS" class="form-label">NIHSS (ประเมินซ้ำ)</label>
                            <input type="number" class="form-control" id="modalNIHSS">
                        </div>
                        <div class="mb-3">
                            <label for="modalGCS" class="form-label">GCS (E_M_V_)</label>
                            <input type="text" class="form-control" id="modalGCS" placeholder="เช่น E4M6V5 (15)">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-primary" id="saveWardEntry">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/th.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/buddhistYear.js"></script>
    <script>
        flatpickr(".datepicker", {
            enableTime: true,
            dateFormat: "d/m/Y H:i",
            time_24hr: true,
            locale: "th",
            plugins: [new window.flatpickr.plugins.buddhistYear()],
            monthSelectorType: "dropdown"
        });
        flatpickr(".datepicker-no-time", {
            dateFormat: "d/m/Y",
            locale: "th",
            plugins: [new window.flatpickr.plugins.buddhistYear()],
            monthSelectorType: "dropdown"
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // --- 1. โค้ดสำหรับ Modal ---
            const addEntryModal = new bootstrap.Modal(document.getElementById('addEntryModal'));
            const saveButton = document.getElementById('saveWardEntry');
            const tableBody = document.getElementById('monitoringTableBody');
            const dateTimeInput = document.getElementById('modalDateTime');
            const sbpInput = document.getElementById('modalSBP');
            const nihssInput = document.getElementById('modalNIHSS');
            const gcsInput = document.getElementById('modalGCS');

            // (*** 2. แก้ไขส่วนนี้ ***)
            saveButton.addEventListener('click', function() {
                const dateTimeValue = dateTimeInput.value;
                if (!dateTimeValue) {
                    // (*** เปลี่ยนจาก alert(...) เป็น Swal.fire(...) ***)
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: 'กรุณากรอก วันที่/เวลา ด้วยครับ',
                        icon: 'error'
                    });
                    return;
                }
                const sbpValue = sbpInput.value || '-';
                const nihssValue = nihssInput.value || '-';
                const gcsValue = gcsInput.value || '-';
                const formattedDate = new Date(dateTimeValue).toLocaleString('th-TH', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }).replace(',', '');

                tableBody.innerHTML += `
                    <tr>
                        <td>${formattedDate}</td>
                        <td>${sbpValue}</td>
                        <td>${nihssValue}</td>
                        <td>${gcsValue}</td>
                    </tr>`;

                dateTimeInput.value = '';
                sbpInput.value = '';
                nihssInput.value = '';
                gcsInput.value = '';
                
                // (*** 3. ซ่อน Modal ก่อน แล้วค่อยโชว์ Popup "สำเร็จ" ***)
                addEntryModal.hide();

                Swal.fire({
                    title: 'บันทึกแล้ว!',
                    text: 'บันทึกการเฝ้าระวังเรียบร้อย',
                    icon: 'success',
                    timer: 1500, // (ให้ Popup หายไปเองใน 1.5 วินาที)
                    showConfirmButton: false
                });
            });

            // --- 2. โค้ดสำหรับซ่อน/แสดง วันที่จำหน่าย (เหมือนเดิม) ---
            const dischargeRadios = document.querySelectorAll('input[name="discharge_planning"]');
            const dischargeDateField = document.getElementById('discharge_date_field');
            const dischargeDateInput = document.getElementById('discharge_date_input');
            
            function updateDischargeField() {
                const selectedValue = document.querySelector('input[name="discharge_planning"]:checked');
                
                if (selectedValue && selectedValue.value === 'came') {
                    dischargeDateField.style.display = 'block';
                } else {
                    dischargeDateField.style.display = 'none';
                    dischargeDateInput.value = ''; 
                }
            }

            dischargeRadios.forEach(radio => {
                radio.addEventListener('change', updateDischargeField);
            });
            updateDischargeField();
        });
    </script>
</body>
</html>