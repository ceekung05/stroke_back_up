<?php
header('Content-Type: application/json');

// รับค่าจากหน้าบ้าน
$type = $_POST['type'] ?? 'hn'; // 'hn' หรือ 'an'
$keyword = $_POST['keyword'] ?? '';

if (empty($keyword)) {
    echo json_encode([]);
    exit;
}

// URL หลักของ API
$url = "http://172.16.99.200/api/pmk/get_data/";

// เตรียมข้อมูลที่จะส่ง (Payload)
$params = [];

if ($type === 'an') {
    // กรณีค้นหาด้วย AN (ตามรูปที่ 2: fnc=ipd)
    $params = [
        'fnc' => 'ipd',
        'an' => $keyword
    ];
} else {
    // กรณีค้นหาด้วย HN (ตามรูปที่ 1: fnc=patients_opd_ipd)
    $params = [
        'fnc' => 'patients_opd_ipd',
        'hn' => $keyword
    ];
}

// เริ่มต้น CURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);          // ** เปลี่ยนเป็น POST **
curl_setopt($ch, CURLOPT_POSTFIELDS, $params); // ** ส่งค่าแบบ Form-Data **
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);

// ตรวจสอบ Error ของ CURL
if(curl_errno($ch)){
    echo json_encode(['error' => 'Curl error: ' . curl_error($ch)]);
} else {
    // ส่งผลลัพธ์กลับไปให้ JavaScript
    echo $response;
}

curl_close($ch);
?>