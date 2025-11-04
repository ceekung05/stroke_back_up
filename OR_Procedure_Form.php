<?php
// 1. [ต้องมี] เริ่ม session เพื่อ "ปลุก" ข้อมูลที่เก็บไว้
session_start(); 
$user = $_SESSION['user_data']; 

?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ฟอร์มบันทึกการผ่าตัด (OR Form)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom CSS เพื่อให้ได้สไตล์แบบในรูปตัวอย่าง
        */
        .nav-card {
      background-color: #ffffff;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
      /* เงาจางๆ */
      transition: all 0.3s ease;
      /* ทำให้ขยับได้อย่างนุ่มนวล */
      display: flex;
      /* จัดไอคอนกับข้อความให้อยู่แนวเดียวกัน */
      align-items: center;
      padding: 24px;
      text-decoration: none;
      color: #333;
      height: 100%;
      /* ทำให้การ์ดสูงเท่ากัน */
    }

    .navbar-custom {
      background-color: #4a559d;
      /* สีม่วงน้ำเงินจากรูป (โดยประมาณ) */
    }
    .nav-card:hover {
      transform: translateY(-5px);
      /* ขยับขึ้นเล็กน้อย */
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    /* 5. วงกลมไอคอน */
    .nav-card .icon-circle {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 20px;
      font-size: 24px;
    }
     /* 1. ทำให้ "หน้าแรก" (ที่เป็นลิงก์ <a>) เป็นสีเทา */
        .breadcrumb-item a {
            color: #6c757d;
            text-decoration: none;
        }

        /* 2. ทำให้ลิงก์ "หน้าแรก" เปลี่ยนเป็นสีน้ำเงินเมื่อชี้ */
        .breadcrumb-item a:hover {
            color: #0d6efd;
        }

        /* 3. ทำให้หน้าปัจจุบัน (active) เป็นสีเข้ม */
        .breadcrumb-item.active {
            color: #2689ebff;
        }
    </style>
</head>

<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm navbar-custom">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">
        <i class="fas fa-brain me-2"></i>
        ระบบส่งต่อผู้ป่วยโรคหลอดเลือดสมอง (Stroke)
      </a>
      <div class="d-flex">
        <span class="navbar-text text-white d-flex align-items-center">
          <i class="fas fa-user-circle fa-2x me-3"></i>
          <span>
            <strong>ชื่อ-สกุล:</strong> <?php echo htmlspecialchars($user['HR_FNAME']); ?>
          </span>
        </span>
      </div>
    </div>
  </nav>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
            <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="fas fa-home me-1"></i> หน้าแรก</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            3.OR
                        </li>
                    </ol>
                </nav>

                <div class="card shadow-sm">
                    <div class="card-header navbar-custom text-white">
                        <h4 class="mb-0">📝 ฟอร์มบันทึกการผ่าตัด/หัตถการ (OR Procedure Form)</h4>
                    </div>
                    <div class="card-body p-4">
                        <form>

                            <fieldset class="border p-3 rounded mb-4">
                                <legend class="float-none w-auto px-2 h5">ประเภทหัตถการ</legend>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="procType" id="procTypeMT" value="mt">
                                    <label class="form-check-label fs-5" for="procTypeMT">
                                        1. Mechanical Thrombectomy (สำหรับ Ischemic Stroke)
                                    </label>
                                </div>
                                <hr class="my-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="procType" id="procTypeHemo" value="hemo">
                                    <label class="form-check-label fs-5" for="procTypeHemo">
                                        2. Neurosurgery (สำหรับ Hemorrhagic Stroke)
                                    </label>
                                </div>
                            </fieldset>

                            <div id="mtProcedure" class="d-none">
                                <fieldset class="border p-3 rounded mb-4">
                                    <legend class="float-none w-auto px-2 h5 text-primary">A. บันทึก Mechanical Thrombectomy (MT)</legend>
                                    <div class="mb-3">
                                        <label for="occlusionLocation" class="form-label fw-bold">1. ตันตรงไหน (Location of Occlusion)</label>
                                        <input type="text" class="form-control" id="occlusionLocation" placeholder="เช่น M1, ICA, Basilar">
                                    </div>

                                    <div class="mb-3">
                                        <label for="ticiScore" class="form-label">TICI Score (ผลลัพธ์การเปิดเส้นเลือด)</label>
                                        <select class="form-select" id="ticiScore">
                                            <option selected>-- เลือกผลลัพธ์ --</option>
                                            <option value="0">0 - No perfusion</option>
                                            <option value="1">1 - Minimal perfusion</option>
                                            <option value="2a">2a - Partial ( < 50%)</option>
                                            <option value="2b">2b - Partial ( > 50%)</option>
                                            <option value="3">3 - Complete perfusion</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="procedureTechnique" class="form-label fw-bold">2. วิธีเปิดหลอดเลือด (Procedure Technique)</label>
                                        <textarea class="form-control" id="procedureTechnique" rows="3" placeholder="เช่น Stent retriever, Aspiration, หรือ Combined"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="" class="form-label fw-bold">เปิดกี่ครั้ง</label>
                                    <input type="number" name="" id="" class="form-number-input rounded">
                                    </div>
                                    <label class="form-label fw-bold">ยา Post-Procedure (วิธีรักษา...)</label>
                                    <div class="d-flex gap-3 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="medAspirin">
                                            <label class="form-check-label" for="medAspirin">Aspirin</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="medStartAlone">
                                            <label class="form-check-label" for="medStartAlone">start alone</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="medSul">
                                            <label class="form-check-label" for="medSul">Sul... (เช่น Clopidogrel)</label>
                                        </div>
                                    </div>
                                    
                                    
                                    <label class="form-label fw-bold">ยา Peri-Procedure (ยา... case)</label>
                                    <div class="d-flex gap-3 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="medIntegrilin">
                                            <label class="form-check-label" for="medIntegrilin">Integrilin</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="medNinodine">
                                            <label class="form-check-label" for="medNinodine">Ninodine / Nimodipine</label>
                                        </div>
                                    </div>

                                    <label class="form-label fw-bold">ข้อมูลหัตถการ</label>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="xrayDose" class="form-label">Dose X-ray</label>
                                            <input type="text" class="form-control" id="xrayDose">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="coneBeamCT" class="form-label">Cone Beam CT</label>
                                            <input type="text" class="form-control" id="coneBeamCT">
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            <div id="hemoProcedure" class="d-none">
                                <fieldset class="border p-3 rounded mb-4">
                                    <legend class="float-none w-auto px-2 h5 text-danger">B. บันทึก Neurosurgery (Hemorrhagic)</legend>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="hemoLocation" class="form-label">Location (ตำแหน่งเลือดออก)</label>
                                            <input type="text" class="form-control" id="hemoLocation">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="hemoCC" class="form-label">Hemorrhage (CC) (ปริมาตรเลือด)</label>
                                            <input type="number" class="form-control" id="hemoCC">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="ivhCheck">
                                                <label class="form-check-label" for="ivhCheck">IVH?</label>
                                            </div>
                                        </div>
                                    </div>

                                    <label class="form-label fw-bold">ผ่า? (หัตถการที่ทำ)</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="procCranio">
                                            <label class="form-check-label" for="procCranio">cranio (Craniotomy)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="procCraniectomy">
                                            <label class="form-check-label" for="procCraniectomy">craniectomy</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="procVentriculostomy">
                                            <label class="form-check-label" for="procVentriculostomy">ventriculostomy</label>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            <fieldset class="border p-3 rounded">
                                <legend class="float-none w-auto px-2 h5">ภาวะแทรกซ้อน (Complications)</legend>
                                <div class="mb-3">
                                    <label for="complicationLog" class="form-label">บันทึกภาวะแทรกซ้อน</label>
                                    <textarea class="form-control" id="complicationLog" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="contrastReaction" class="form-label">CONTRAST...</label>
                                    <input type="text" class="form-control" id="contrastReaction" placeholder="บันทึกปฏิกิริยาต่อสารทึบรังสี (ถ้ามี)">
                                </div>
                                </Fie>

                                <hr class="my-4">
                                <!-- <div class="d-grid">
                                    <button type="submit" class="btn btn-success btn-lg">✔️ บันทึกและส่งต่อหอผู้ป่วย (Save & Send to Ward)</button>
                                </div> -->

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript เพื่อซ่อน/แสดงผล ตามประเภทหัตถการ
        const radioMT = document.getElementById('procTypeMT');
        const radioHemo = document.getElementById('procTypeHemo');
        const mtProcedure = document.getElementById('mtProcedure');
        const hemoProcedure = document.getElementById('hemoProcedure');

        radioMT.addEventListener('change', () => {
            if (radioMT.checked) {
                mtProcedure.classList.remove('d-none');
                hemoProcedure.classList.add('d-none');
            }
        });

        radioHemo.addEventListener('change', () => {
            if (radioHemo.checked) {
                mtProcedure.classList.add('d-none');
                hemoProcedure.classList.remove('d-none');
            }
        });
    </script>
</body>

</html>