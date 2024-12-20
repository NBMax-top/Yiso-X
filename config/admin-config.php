<?php
// 管理员账号配置
define('ADMIN_USERNAME', 'admin');
// 使用 password_hash('your_password', PASSWORD_DEFAULT) 生成密码哈希
define('ADMIN_PASSWORD_HASH', '$2y$12$XgltzTELD0cYXOdXVRmGpOpwRIJAF1Z381hhG5K0ixIqF24xfwCqa');  // 替换为实际的密码哈希

// 会话配置
define('SESSION_LIFETIME', 3600); // 1小时
define('SESSION_NAME', 'YISO_ADMIN');

// 会话配置需要在 session_start() 之前设置
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
    session_name(SESSION_NAME);
    session_start();
}
?> 