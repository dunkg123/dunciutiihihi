<?php
header('Content-Type: application/json; charset=utf-8');

function validatePlateNumber($bienso) {
    $bienso = str_replace(['-', '.', ' '], '', $bienso);
    return preg_match('/^\d{2}[A-Z]\d{5,6}$/', $bienso);
}

function fetchData($bienso) {
    $url = 'https://api.checkphatnguoi.vn/phatnguoi';
    $ch = curl_init();
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['bienso' => $bienso]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true
    ];
    
    curl_setopt_array($ch, $options);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => 'Lỗi kết nối: ' . $error];
    }
    
    if ($httpCode !== 200) {
        return ['error' => 'Máy chủ trả về mã lỗi: ' . $httpCode];
    }
    
    $data = json_decode($response, true);
    return $data['data'] ?? [];
}

try {
    if (!isset($_POST['bienso'])) {
        throw new Exception('Thiếu thông tin biển số xe');
    }
    
    $bienso = trim($_POST['bienso']);
    
    if (empty($bienso)) {
        throw new Exception('Vui lòng nhập biển số xe');
    }
    
    if (!validatePlateNumber($bienso)) {
        throw new Exception('Định dạng biển số không hợp lệ');
    }
    
    $result = fetchData($bienso);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}