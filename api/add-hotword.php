<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/MySQL-Configs.php';

try {
    // 获取搜索关键词
    $keyword = $_GET['q'] ?? '';
    if (empty($keyword)) {
        throw new Exception('搜索关键词不能为空');
    }

    $conn = getDBConnection();
    
    // 清理24小时前的数据
    $cleanup_sql = "DELETE FROM hotwords WHERE last_search < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    if (!$conn->query($cleanup_sql)) {
        error_log('清理热搜词失败: ' . $conn->error);
    }
    
    // 更新或插入热搜词
    $stmt = $conn->prepare("INSERT INTO hotwords (keyword, search_count, last_search) 
                           VALUES (?, 1, NOW())
                           ON DUPLICATE KEY UPDATE 
                           search_count = search_count + 1,
                           last_search = NOW()");
    
    $stmt->bind_param('s', $keyword);
    
    if (!$stmt->execute()) {
        throw new Exception('更新热搜词失败');
    }

    echo json_encode([
        'code' => 200,
        'message' => '热搜词更新成功'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'code' => 500,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        closeDBConnection($conn);
    }
} 