<?php
header('Content-Type: application/json');
require_once '../config/MySQL-Configs.php';

$conn = getDBConnection();
$date = date('Y-m-d');

// 获取今日访问量
$sql = "SELECT COUNT(*) as count FROM visit_logs WHERE DATE(visit_time) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

// 记录本次访问
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
$sql = "INSERT INTO visit_logs (ip, visit_time) VALUES (?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $ip);
$stmt->execute();

echo json_encode([
    'code' => 200,
    'msg' => 'success',
    'data' => [
        'count' => $result['count'],
        'date' => $date
    ]
]);

closeDBConnection($conn); 