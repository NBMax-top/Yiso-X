<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/MySQL-Configs.php';

try {
    // 获取请求头
    $timestamp = $_SERVER['HTTP_X_TIMESTAMP'] ?? '';
    $nonce = $_SERVER['HTTP_X_NONCE'] ?? '';
    
    // 验证时间戳(5分钟内有效)
    if (abs(time() - $timestamp/1000) > 300) {
        throw new Exception('请求已过期');
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // 验证必要字段
    if (!isset($data['url']) || !isset($data['title']) || !isset($data['description'])) {
        throw new Exception('缺少必要字段');
    }
    
    $conn = getDBConnection();
    
    // 检查URL是否已存在
    $stmt = $conn->prepare("SELECT id FROM web_info WHERE url = ?");
    $stmt->bind_param("s", $data['url']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'code' => 400,
            'message' => '该网站已收录'
        ]);
        exit;
    }
    
    // 插入新记录
    $stmt = $conn->prepare("INSERT INTO web_info (title, description, url) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $data['title'], $data['description'], $data['url']);
    $stmt->execute();
    
    echo json_encode([
        'code' => 200,
        'message' => '提交成功'
    ]);

} catch (Exception $e) {
    http_response_code(403);
    echo json_encode([
        'code' => 403,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        closeDBConnection($conn);
    }
} 