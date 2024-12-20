<?php
// MySQL数据库连接配置
define('DB_HOST', 'localhost');  
define('DB_USER', 'yisox');
define('DB_PASS', 'yisox'); 
define('DB_NAME', 'yisox');
define('DB_PORT', '3306');

// 创建数据库连接
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($conn->connect_error) {
        die("连接失败: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
    return $conn;
}

// 关闭数据库连接
function closeDBConnection($conn) {
    $conn->close(); 
}
?> 