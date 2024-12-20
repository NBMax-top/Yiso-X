<?php
require_once '../../config/MySQL-Configs.php';

// 获取网站设置
try {
    $conn = getDBConnection();
    $sql = "SELECT setting_name, setting_value FROM WEBSITE_SETTINGS";
    $result = $conn->query($sql);
    
    $settings = [];
    while($row = $result->fetch_assoc()) {
        $settings[$row['setting_name']] = $row['setting_value'];
    }
    
    $site_title = $settings['title'] ?? '一搜 - 提交收录';
    
} catch (Exception $e) {
    error_log('获取网站设置失败: ' . $e->getMessage());
} finally {
    if (isset($conn)) {
        closeDBConnection($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>提交收录(自动化) - <?php echo $site_title; ?></title>
    <link rel="stylesheet" href="../../css/submit.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="../../index.html" class="logo">
                <img src="../../img/logo.png" alt="Logo" height="30">
            </a>
        </div>
    </header>

    <main class="main">
        <div class="submit-container">
            <h1>提交网站</h1>
            <div class="submit-form">
                <div class="input-group">
                    <input type="url" id="urlInput" placeholder="请输入网站地址 (https://example.com)">
                    <div class="captcha-group">
                        <input type="text" id="captchaInput" placeholder="验证码">
                        <img id="captchaImage" src="webAuthenticationCode.php" alt="验证码" onclick="this.src='webAuthenticationCode.php?t='+Math.random()">
                    </div>
                    <div class="button-group">
                        <button id="fetchBtn">获取信息</button>
                    </div>
                </div>
                
                <div id="resultContainer" class="result-container" style="display:none">
                    <div class="input-group">
                        <label>网站标题</label>
                        <input type="text" id="titleInput" readonly>
                    </div>
                    <div class="input-group">
                        <label>网站描述</label>
                        <textarea id="descInput" readonly></textarea>
                    </div>
                    <button id="submitBtn" class="submit-button">提交收录</button>
                </div>
            </div>
        </div>
    </main>

    <script>
        // 设置API基础路径
        window.API_BASE = '../../api';
    </script>
    <script src="../../js/submit.js"></script>
</body>
</html>