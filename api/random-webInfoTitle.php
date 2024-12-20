<?php
header('Content-Type: application/json');
require_once '../config/MySQL-Configs.php';

try {
    // 获取数据库连接
    $conn = getDBConnection();
    
    // 随机获取一个网站标题
    $result = $conn->query("SELECT title FROM web_info ORDER BY RAND() LIMIT 1");
    
    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode([
            'code' => 200,
            'message' => 'success',
            'data' => $row['title']
        ]);
    } else {
        echo json_encode([
            'code' => 404,
            'message' => '没有找到网站数据'
        ]);
    }
    
    // 关闭结果集
    $result->close();
    // 关闭连接
    closeDBConnection($conn);
    
} catch(Exception $e) {
    echo json_encode([
        'code' => 500,
        'message' => '数据库错误: ' . $e->getMessage()
    ]);
} 