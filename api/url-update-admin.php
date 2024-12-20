<?php
// 禁用错误显示
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
require_once '../config/MySQL-Configs.php';

// 验证管理员权限
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    http_response_code(403);
    die(json_encode([
        'code' => 403,
        'message' => '无权限访问'
    ]));
}

// 获取和验证请求头
$timestamp = $_SERVER['HTTP_X_TIMESTAMP'] ?? '';
$nonce = $_SERVER['HTTP_X_NONCE'] ?? '';
$isAdmin = $_SERVER['HTTP_X_ADMIN'] ?? '';

if (!$timestamp || !$nonce || $isAdmin !== 'true') {
    http_response_code(400);
    die(json_encode([
        'code' => 400,
        'message' => '无效的请求头'
    ]));
}

try {
    $input = file_get_contents('php://input');
    if (!$input) {
        throw new Exception('无效的请求数据');
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON 解析失败: ' . json_last_error_msg());
    }
    
    $url = $data['url'] ?? '';
    $title = $data['title'] ?? '';
    $description = $data['description'] ?? '';

    if (empty($url) || empty($title) || empty($description)) {
        throw new Exception('参数不完整');
    }

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) {
        throw new Exception("数据库连接失败");
    }
    mysqli_set_charset($conn, "utf8mb4");
    
    // 检查URL是否已存在
    $stmt = mysqli_prepare($conn, "SELECT id FROM web_info WHERE url = ?");
    if (!$stmt) {
        throw new Exception("预处理语句创建失败");
    }
    
    mysqli_stmt_bind_param($stmt, "s", $url);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("查询执行失败");
    }
    
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // 更新现有记录
        $stmt = mysqli_prepare($conn, "UPDATE web_info SET title = ?, description = ?, update_time = NOW() WHERE url = ?");
        mysqli_stmt_bind_param($stmt, "sss", $title, $description, $url);
    } else {
        // 插入新记录
        $stmt = mysqli_prepare($conn, "INSERT INTO web_info (url, title, description, create_time, update_time) VALUES (?, ?, ?, NOW(), NOW())");
        mysqli_stmt_bind_param($stmt, "sss", $url, $title, $description);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("操作失败");
    }

    echo json_encode([
        'code' => 200,
        'message' => '提交成功'
    ]);

} catch (Exception $e) {
    error_log('URL更新失败: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'code' => 500,
        'message' => '服务器错误'
    ]);
} finally {
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
    }
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?> 