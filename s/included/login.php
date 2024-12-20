<?php
session_start();

// 如果已经登录，直接跳转到管理页面
if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header('Location: admin.php');
    exit;
}

// 处理登录请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // 从配置文件获取管理员信息
    require_once '../../config/admin-config.php';
    
    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION['admin'] = true;
        $_SESSION['login_time'] = time();
        header('Location: admin.php');
        exit;
    } else {
        $error = '用户名或密码错误';
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录</title>
    <link rel="stylesheet" href="../../css/submit.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="../../index.php" class="logo">
                <img src="../../img/logo.png" alt="Logo" height="30">
            </a>
        </div>
    </header>

    <main class="main">
        <div class="login-container">
            <form class="login-form" method="POST" action="login.php">
                <h1>管理员登录</h1>
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                <div class="input-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="admin-button">登录</button>
            </form>
        </div>
    </main>
</body>
</html> 