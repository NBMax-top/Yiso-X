<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/MySQL-Configs.php';

// 获取搜索参数
$query = $_GET['q'] ?? '';
$page = intval($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

if (empty($query)) {
    echo json_encode([
        'code' => 400,
        'message' => '搜索关键词不能为空',
        'data' => null
    ]);
    exit;
}

try {
    $conn = getDBConnection();
    
    // 构建搜索条件
    $search_param = "%{$query}%";
    
    // 计算总结果数
    $count_sql = "SELECT COUNT(*) as total FROM web_info 
                  WHERE title LIKE ? OR description LIKE ?";
    $stmt = $conn->prepare($count_sql);
    $stmt->bind_param('ss', $search_param, $search_param);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    
    // 搜索查询 - 使用 UNION ALL 和子查询优化
    $search_sql = "
        SELECT * FROM (
            SELECT id, title, description, url, auth_level, create_time,
                   CASE 
                       WHEN auth_level = 1 THEN 5
                       WHEN auth_level = 2 THEN 4
                       WHEN auth_level = 3 THEN 3
                       WHEN auth_level = 4 THEN 2
                       WHEN auth_level = 5 THEN 1
                       ELSE 0
                   END as sort_weight,
                   CASE 
                       WHEN title LIKE ? THEN 1
                       WHEN description LIKE ? THEN 2
                       ELSE 3
                   END as match_weight
            FROM web_info 
            WHERE title LIKE ? OR description LIKE ?
        ) as results
        ORDER BY sort_weight DESC, 
                 match_weight ASC,
                 create_time DESC
        LIMIT ? OFFSET ?";
                   
    $stmt = $conn->prepare($search_sql);
    $stmt->bind_param('ssssii', 
        $search_param, $search_param,  // 标题和描述匹配权重
        $search_param, $search_param,  // 搜索条件
        $limit, $offset               // 分页参数
    );
    
    $start_time = microtime(true);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $search_time = round(microtime(true) - $start_time, 3);
    
    // 格式化结果
    $formatted_results = array_map(function($row) {
        // 提取匹配的关键词片段作为摘要
        $description = strip_tags($row['description']);
        $snippet = mb_substr($description, 0, 200) . '...';
        
        return [
            'title' => htmlspecialchars($row['title']),
            'url' => htmlspecialchars($row['url']),
            'snippet' => $snippet,
            'auth_level' => intval($row['auth_level']),
            'create_time' => $row['create_time']
        ];
    }, $results);
    
    echo json_encode([
        'code' => 200,
        'message' => 'success',
        'data' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'search_time' => $search_time,
            'results' => $formatted_results
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'code' => 500,
        'message' => '服务器错误: ' . $e->getMessage(),
        'data' => null
    ]);
} finally {
    if (isset($conn)) {
        closeDBConnection($conn);
    }
}
?>