<?php
// (ไฟล์ api_caller.php)

// 1. ตั้งค่า Header ว่าไฟล์นี้จะตอบกลับเป็น JSON เสมอ
header('Content-Type: application/json');

// 2. ตรวจสอบว่ามีการส่ง POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $hn_from_form = $_POST['hn'] ?? ''; 
    $fnc_value = 'patients_opd_ipd';

    if (empty($hn_from_form)) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'HN is required']);
        exit;
    }

    // 3. เตรียมข้อมูลยิง cURL
    $postData = [
        'fnc' => $fnc_value,
        'hn'  => $hn_from_form
    ];

    // --- (ส่วน cURL) ---
    $url = 'http://172.16.99.200/api/pmk/get_data/';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 5 วินาที
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 วินาที

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    // --- (จบส่วน cURL) ---

    // 4. ส่งข้อมูลกลับให้ JavaScript
    if ($response === false) {
        // ถ้า cURL error (เช่น เชื่อมต่อ API จริงไม่ได้)
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'API connection failed: ' . $error]);
    } else if ($http_code != 200) {
        // ถ้า API จริงตอบกลับมาว่า Error (เช่น 404, 500)
        http_response_code(502); // Bad Gateway
        echo json_encode(['error' => 'API returned non-200 status: ' . $http_code]);
    } else {
        // ถ้าสำเร็จ: ส่ง JSON ที่ได้จาก API กลับไปตรงๆ
        echo $response;
    }

} else {
    // ถ้าเข้าหน้าตรงๆ
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Method not allowed. Please use POST.']);
}
?>