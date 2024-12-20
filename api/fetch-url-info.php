<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/MySQL-Configs.php';

// 开启错误日志
error_reporting(E_ALL);
ini_set('display_errors', 1);

function curl_get_info($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    
    // 设置随机IP
    $random_ip = "222.222.".mt_rand(0, 254).".".mt_rand(0, 254);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "X-FORWARDED-FOR:$random_ip", 
        "CLIENT-IP:$random_ip"
    ));
    
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 10000);
    
    $result = curl_exec($ch);
    
    if ($result === false) {
        error_log('CURL错误: ' . curl_error($ch));
    }
    
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    return ['result' => $result, 'info' => $info];
}

try {
    $raw_input = file_get_contents('php://input');
    error_log('收到请求数据: ' . $raw_input);
    
    $data = json_decode($raw_input, true);
    
    if (!isset($data['url'])) {
        throw new Exception('缺少URL参数');
    }
    
    $url = $data['url'];
    error_log('正在获取URL: ' . $url);
    
    $curl_data = curl_get_info($url);
    
    // 处理重定向
    if (!empty($curl_data['info']['redirect_url'])) {
        $curl_data = curl_get_info($curl_data['info']['redirect_url']);
    }
    
    // 提取标题和描述
    $html = $curl_data['result'];
    $title = '';
    $description = '';
    
    // 提取标题
    if (preg_match('/<title>(.*?)<\/title>/i', $html, $matches)) {
        $title = trim($matches[1]);
    }
    
    // 提取描述
    if (preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^>]*?)["\'].*?>/i', $html, $matches)) {
        $description = trim($matches[1]);
    } else {
        // 如果没有描述标签,提取正文前200个字符作为描述
        $text = strip_tags($html);
        $description = trim(substr($text, 0, 200));
    }
    
    echo json_encode([
        'code' => 200,
        'message' => 'success',
        'data' => [
            'title' => $title,
            'description' => $description
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('处理失败: ' . $e->getMessage());
    echo json_encode([
        'code' => 500,
        'message' => '获取失败: ' . $e->getMessage(),
        'data' => null
    ]);
} 