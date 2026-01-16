<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - Stroke Care Registry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Cotton Candy Palette */
            --primary-color: #A0C4FF; /* ฟ้าพาสเทล */
            --secondary-color: #FFB7B2; /* ชมพูพาสเทล */
            --accent-color: #BDB2FF; /* ม่วงพาสเทล */
            --text-dark: #475569;
            --text-muted: #94a3b8;
            --font-family: 'Sarabun', sans-serif;
            --gradient-bg: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
            --gradient-primary: linear-gradient(135deg, #A0C4FF 0%, #90e0ef 100%);
            --gradient-button: linear-gradient(45deg, #A0C4FF, #BDB2FF);
        }

        body {
            font-family: var(--font-family);
            background: #fdfbfb; /* พื้นหลังสีขาวนวล */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
            position: relative;
        }

        /* --- Dynamic Background Shapes (ลูกเล่นพื้นหลัง) --- */
        .shape {
            position: absolute;
            filter: blur(50px);
            z-index: 0;
            opacity: 0.6;
            animation: float 10s infinite ease-in-out;
        }
        .shape-1 {
            top: -10%; left: -10%; width: 500px; height: 500px;
            background: radial-gradient(circle, #BDB2FF 0%, rgba(255,255,255,0) 70%);
        }
        .shape-2 {
            bottom: -10%; right: -10%; width: 400px; height: 400px;
            background: radial-gradient(circle, #FFB7B2 0%, rgba(255,255,255,0) 70%);
            animation-delay: -5s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(20px) scale(1.05); }
        }

        /* --- Login Card --- */
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 20px;
            position: relative;
            z-index: 2;
        }

        .card {
            border: 2px solid #fff;
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(160, 196, 255, 0.25); /* เงาสีฟ้าฟุ้งๆ */
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .card:hover { transform: translateY(-5px); }

        .card-header-custom {
            padding: 40px 30px 10px 30px;
            text-align: center;
        }

        .logo-box {
            width: 80px; height: 80px;
            background: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center; justify-content: center;
            box-shadow: 0 10px 25px rgba(189, 178, 255, 0.3);
            margin-bottom: 20px;
            position: relative;
        }
        
        .logo-icon {
            font-size: 2.5rem;
            background: -webkit-linear-gradient(45deg, #FFB7B2, #BDB2FF);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: heartbeat 2s infinite;
        }

        .app-title {
            color: var(--text-dark);
            font-weight: 800;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
            margin-bottom: 5px;
        }

        .app-subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .card-body { padding: 20px 40px 50px 40px; }

        /* --- Modern Inputs --- */
        .form-floating > .form-control {
            border-radius: 15px;
            border: 2px solid #f1f5f9;
            background-color: #fcfdfe;
            height: 55px;
            color: var(--text-dark);
            font-weight: 600;
        }
        
        .form-floating > .form-control:focus {
            border-color: #A0C4FF;
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(160, 196, 255, 0.2);
        }

        .form-floating > label { color: #94a3b8; padding-left: 20px; }
        
        .input-icon-right {
            position: absolute; right: 20px; top: 18px;
            color: #cbd5e1; cursor: pointer; z-index: 5;
        }

        /* --- Button --- */
        .btn-gradient {
            background: var(--gradient-button);
            border: none;
            border-radius: 50px;
            padding: 14px;
            font-size: 1.1rem;
            font-weight: 700;
            color: white;
            box-shadow: 0 10px 20px rgba(189, 178, 255, 0.4);
            transition: all 0.3s;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(189, 178, 255, 0.5);
            background: linear-gradient(45deg, #BDB2FF, #A0C4FF);
        }

        /* --- Liquid Transition Overlay (Wow Effect) --- */
        .liquid-overlay {
            position: fixed;
            bottom: 0; left: 0; width: 100%; height: 0%;
            background: linear-gradient(180deg, #A0C4FF 0%, #BDB2FF 100%);
            z-index: 9999;
            display: flex;
            align-items: center; justify-content: center;
            transition: height 1s cubic-bezier(0.86, 0, 0.07, 1); /* Ease-in-out effect */
            overflow: hidden;
        }
        
        /* Wave Effect on top of liquid */
        .liquid-overlay::before {
            content: "";
            position: absolute;
            top: -50px; left: 0; width: 200%; height: 100px;
            background: url('data:image/svg+xml;utf8,<svg viewBox="0 0 1200 120" xmlns="http://www.w3.org/2000/svg"><path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="%23A0C4FF"></path></svg>');
            background-size: 50% 100%;
            animation: wave 3s linear infinite;
        }

        .loading-content {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.5s ease 0.5s; /* Delay appearance */
            text-align: center;
            color: white;
        }

        @keyframes wave {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        
        @keyframes heartbeat {
            0% { transform: scale(1); }
            15% { transform: scale(1.2); }
            30% { transform: scale(1); }
            45% { transform: scale(1.2); }
            60% { transform: scale(1); }
        }

    </style>
</head>

<body>

    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="liquid-overlay" id="liquidLoader">
        <div class="loading-content">
            <i class="bi bi-heart-pulse-fill" style="font-size: 4rem; animation: heartbeat 1s infinite;"></i>
            <h3 class="mt-3 fw-bold">กำลังเข้าสู่ระบบ...</h3>
            <p class="small opacity-75">Stroke Care Registry System</p>
        </div>
    </div>

    <div class="login-wrapper">
        <div class="card">
            
            <div class="card-header-custom">
                <div class="logo-box">
                    <i class="bi bi-activity logo-icon"></i>
                </div>
                <h2 class="app-title">Welcome Back</h2>
                <p class="app-subtitle">เข้าสู่ระบบทะเบียนผู้ป่วยโรคหลอดเลือดสมอง</p>
            </div>

            <div class="card-body">
                <form action="process_login.php" method="post" class="needs-validation" id="loginForm" novalidate>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="id_card" name="uname" 
                               placeholder="เลขบัตรประชาชน" required maxlength="13" pattern="[0-9]{13}">
                        <label for="id_card"><i class="bi bi-person-vcard me-2"></i>เลขบัตรประชาชน</label>
                        <i class="bi bi-person input-icon-right"></i>
                        <div class="invalid-feedback ps-3">กรุณากรอกเลข 13 หลัก</div>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="password" name="psword" 
                               placeholder="รหัสผ่าน" required style="padding-right: 50px;">
                        <label for="password"><i class="bi bi-key me-2"></i>รหัสผ่าน</label>
                        <i class="bi bi-eye input-icon-right" id="togglePassword"></i>
                        <div class="invalid-feedback ps-3">กรุณากรอกรหัสผ่าน</div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-gradient">
                            เข้าสู่ระบบ (Sign In)
                        </button>
                    </div>

                </form>
            </div>
            
            <div class="text-center pb-4 text-muted small opacity-50">
                &copy; <?php echo date("Y"); ?> Cotton Candy Theme
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form & Animation Control
        const loginForm = document.getElementById('loginForm');
        const liquidLoader = document.getElementById('liquidLoader');
        const loadingContent = document.querySelector('.loading-content');

        loginForm.addEventListener('submit', function (event) {
            if (!loginForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                // ถ้าฟอร์มถูกต้อง -> เริ่ม Animation
                event.preventDefault(); 
                
                // 1. คลื่นน้ำเอ่อขึ้นมา (Slide Up)
                liquidLoader.style.height = '100%';
                
                // 2. แสดงข้อความ Loading
                loadingContent.style.opacity = '1';

                // 3. รอ 1.5 วินาที แล้ว Submit ไปหน้าถัดไป
                setTimeout(() => {
                    loginForm.submit();
                }, 1500);
            }
            loginForm.classList.add('was-validated');
        }, false);

        // Toggle Password
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        if (togglePassword && password) {
            togglePassword.addEventListener('click', function (e) {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('bi-eye');
                this.classList.toggle('bi-eye-slash');
            });
        }
    </script>
</body>
</html>