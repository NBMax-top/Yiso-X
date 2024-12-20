<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/MySQL-Configs.php';

// 获取分页参数
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

try {
    $conn = getDBConnection();
    
    // 获取总记录数
    $count_sql = "SELECT COUNT(*) as total FROM WEBSITE_SETTINGS";
    $total = $conn->query($count_sql)->fetch_assoc()['total'];
    
    // 分页查询设置
    $sql = "SELECT setting_name, setting_value 
            FROM WEBSITE_SETTINGS 
            ORDER BY setting_name 
            LIMIT ? OFFSET ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $settings = [];
    while($row = $result->fetch_assoc()) {
        $settings[$row['setting_name']] = $row['setting_value'];
    }
    
    echo json_encode([
        'code' => 200,
        'message' => 'success',
        'data' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit),
            'settings' => $settings
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'code' => 500,
        'message' => '获取设置失败: ' . $e->getMessage(),
        'data' => null
    ]);
} finally {
    if (isset($conn)) {
        closeDBConnection($conn);
    }
}
?> 