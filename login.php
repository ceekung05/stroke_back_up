<?php
// 1. เริ่ม session (ต้องอยู่อันแรกเสมอ)
session_start();

// 2. 🛡️ โค้ดยาม (เช็กว่าล็อกอินอยู่)
// ถ้าล็อกอินอยู่แล้ว ให้เด้งไป index ทันที
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: index.php");
    exit;
}

// 3. 🧠 คำสั่งห้ามจำ (Cache Control)
// บังคับให้เบราว์เซอร์ต้องมาถามเซิร์ฟเวอร์ใหม่ทุกครั้งที่เปิดหน้านี้
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Modern & Playful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #A8B2FF, #F3C9FF);
            /* Gradient background */
            background-size: 200% 200%;
            animation: gradientAnimation 15s ease infinite;
            overflow: hidden; /* Prevent scroll if there are minor overflows */
            color: #333;
        }

        @keyframes gradientAnimation {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .login-wrapper {
            position: relative;
            width: 100%;
            max-width: 450px;
            margin: auto;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.95); /* Slightly transparent white */
            border-radius: 1.5rem; /* More rounded corners */
            backdrop-filter: blur(10px); /* Frosted glass effect */
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1); /* Deeper shadow */
            position: relative;
            z-index: 2;
            transition: transform 0.3s ease-in-out;
        }

        .card:hover {
            transform: translateY(-5px); /* Lift card on hover */
        }

        .card-illustration {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 1.5rem;
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            bottom: 15px;
            z-index: 1;
            transform: rotate(-3deg); /* Stacked effect */
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        }
        .card-illustration-2 {
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 1.5rem;
            position: absolute;
            top: 30px;
            left: 30px;
            right: 30px;
            bottom: 30px;
            z-index: 0;
            transform: rotate(3deg); /* Stacked effect */
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.06);
        }


        .card-body {
            padding: 3rem; /* More padding */
        }

        h1 {
            font-weight: 700;
            color: #333;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        h1 i {
            margin-right: 10px;
            color: #FF7582; /* Icon color from gradient */
        }

        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 1px solid #ddd;
            border-radius: 0.75rem; /* More rounded input */
            padding: 0.75rem 1.25rem;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: #6DD5FA;
            box-shadow: 0 0 0 0.25rem rgba(109, 213, 250, 0.25);
            background-color: #fcfdff;
        }

        .btn-primary {
            background: linear-gradient(135deg, #A8B2FF, #F3C9FF);
            border: none;
            border-radius: 0.75rem;
            padding: 0.85rem 1.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            color: white; /* Ensure text is white */
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
            filter: brightness(1.1); /* Slightly brighter on hover */
            background-position: right center; /* Animate gradient */
        }

        .invalid-feedback {
            font-size: 0.875em;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card-body {
                padding: 2rem;
            }
            h1 {
                font-size: 2rem;
            }
            .form-control {
                padding: 0.65rem 1rem;
            }
            .btn-primary {
                padding: 0.75rem 1.25rem;
                font-size: 1rem;
            }
        }
    </style>
    <script type="text/javascript">
        // 1. เมื่อหน้านี้ (login.php) โหลดเสร็จ
        history.pushState(null, null, location.href);

        // 2. เราจะดักฟัง event เมื่อมีการกดปุ่ม Back (popstate)
        window.addEventListener('popstate', function(event) {
            history.pushState(null, null, location.href);
        });

        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</head>

<body>
    <div class="login-wrapper">
        <div class="card-illustration"></div>
        <div class="card-illustration-2"></div>
        <div class="card">
            <div class="card-body">

                <h1 class="text-center mb-4">
                    <i class="fas fa-lock"></i> Login
                </h1>

                <form action="process_login.php" method="post" class="needs-validation" novalidate>

                    <div class="mb-3">
                        <label for="id_card" class="form-label">Username</label>
                        <input type="text" class="form-control" id="id_card" name="uname" placeholder="กรอกเลขบัตรประชาชน 13 หลัก" required maxlength="13" pattern="[0-9]{13}">
                        <div class="invalid-feedback">
                            กรุณากรอกเลขบัตรประชาชน 13 หลักให้ถูกต้อง
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="psword" placeholder="กรอกรหัสผ่าน" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword" style="border-top-right-radius: 0.75rem; border-bottom-right-radius: 0.75rem;">
                                <i class="fas fa-eye"></i>
                            </button>
                            <div class="invalid-feedback">
                                กรุณากรอกรหัสผ่าน
                            </div>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">
                            Sign In
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Bootstrap form validation
        (function() {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
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
            togglePassword.addEventListener('click', function(e) {
                // toggle the type attribute
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                // toggle the eye / eye-slash icon
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }
    </script>
</body>

</html>
