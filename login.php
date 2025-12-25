<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Stroke Care Registry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #1a237e; /* Deep Navy - Medical Theme */
            --accent-color: #d32f2f;  /* Alert Red */
            --text-color: #263238;
            --font-family: 'Sarabun', sans-serif;
        }

        body {
            font-family: var(--font-family);
            background: linear-gradient(135deg, #1a237e 0%, #0d1546 100%); /* พื้นหลังไล่สีน้ำเงินเข้ม */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
            position: relative;
        }

        /* Decoration Circles (ฉากหลังจางๆ เพื่อมิติ) */
        .circle-bg {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            z-index: 0;
        }
        .circle-1 { width: 400px; height: 400px; top: -100px; left: -100px; }
        .circle-2 { width: 300px; height: 300px; bottom: -50px; right: -50px; }

        .login-wrapper {
            width: 100%;
            max-width: 450px;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        /* Card Style */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
            background-color: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .card-header-custom {
            background: white;
            padding: 40px 30px 10px 30px;
            text-align: center;
        }

        .logo-icon {
            font-size: 3.5rem;
            color: var(--accent-color);
            margin-bottom: 15px;
            display: inline-block;
            animation: pulse 2s infinite;
        }

        .app-title {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.6rem;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }

        .app-subtitle {
            color: #78909c;
            font-size: 0.95rem;
            font-weight: 400;
        }

        .card-body {
            padding: 30px 40px 40px 40px;
        }

        /* Form Controls */
        .form-label {
            font-weight: 600;
            color: #455a64;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #cfd8dc;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(26, 35, 126, 0.1);
        }

        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #cfd8dc;
            color: #78909c;
        }

        /* Button */
        .btn-primary {
            background: var(--primary-color); /* Solid Blue */
            border: none;
            border-radius: 50px;
            padding: 12px;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            box-shadow: 0 4px 10px rgba(26, 35, 126, 0.3);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #283593;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(26, 35, 126, 0.4);
        }

        .invalid-feedback {
            font-size: 0.85rem;
        }

        /* Animation */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

    </style>
</head>

<body>

    <div class="circle-bg circle-1"></div>
    <div class="circle-bg circle-2"></div>

    <div class="login-wrapper">
        <div class="card">
            
            <div class="card-header-custom">
                <i class="bi bi-heart-pulse-fill logo-icon"></i>
                <h2 class="app-title">Stroke Care Registry</h2>
                <p class="app-subtitle">ระบบทะเบียนผู้ป่วยโรคหลอดเลือดสมอง</p>
            </div>

            <div class="card-body">
                <form action="process_login.php" method="post" class="needs-validation" novalidate>

                    <div class="mb-3">
                        <label for="id_card" class="form-label">เลขบัตรประชาชน (Username)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                            <input type="text" class="form-control" id="id_card" name="uname" 
                                   placeholder="กรอกเลข 13 หลัก" 
                                   required maxlength="13" pattern="[0-9]{13}">
                            <div class="invalid-feedback">
                                กรุณากรอกเลขบัตรประชาชน 13 หลักให้ถูกต้อง
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">รหัสผ่าน (Password)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="password" name="psword" 
                                   placeholder="กรอกรหัสผ่าน" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword" 
                                    style="border-color: #cfd8dc;">
                                <i class="bi bi-eye"></i>
                            </button>
                            <div class="invalid-feedback">
                                กรุณากรอกรหัสผ่าน
                            </div>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">
                            เข้าสู่ระบบ (Sign In)
                        </button>
                    </div>

                </form>
            </div>
            
            <div class="text-center pb-4 text-muted small" style="opacity: 0.6;">
                &copy; <?php echo date("Y"); ?> Hospital System. All rights reserved.
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form Validation Loop
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()

        // Toggle Password Visibility
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        if (togglePassword && password) {
            togglePassword.addEventListener('click', function (e) {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            });
        }

        // Prevent Back Button
        history.pushState(null, null, location.href);
        window.addEventListener('popstate', function(event) {
            history.pushState(null, null, location.href);
        });
    </script>
</body>
</html>