<?php
session_start();
require_once '../../config/admin-config.php';

function checkAdminAuth() {
    // 检查是否登录
    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header('Location: login.php');
        exit;
    }

    // 检查会话是否过期
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > SESSION_LIFETIME)) {
        session_destroy();
        header('Location: login.php?expired=1');
        exit;
    }

    // 更新最后活动时间
    $_SESSION['login_time'] = time();
}

// 注销功能
function adminLogout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

// 处理注销请求
if (isset($_GET['logout'])) {
    adminLogout();
}
?> 