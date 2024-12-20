<?php
// 只处理 POST 请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if (empty($password)) {
        die('密码不能为空');
    }
    
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    header('Content-Type: application/json');
    echo json_encode([
        'hash' => $hash,
        'verify' => password_verify($password, $hash)
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>生成密码哈希</title>
    <link rel="stylesheet" href="../css/submit.css">
    <style>
        .hash-container {
            max-width: 600px;
            margin: 60px auto;
            padding: 24px;
        }
        .hash-result {
            background: #f8f9fa;
            padding: 16px;
            border-radius: 4px;
            font-family: monospace;
            word-break: break-all;
            margin-top: 16px;
            display: none;
        }
        .copy-button {
            margin-top: 8px;
        }
        .success {
            color: #0d652d;
            background: #e6f4ea;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="../index.php" class="logo">
                <img src="../img/logo.png" alt="Logo" height="30">
            </a>
        </div>
    </header>

    <main class="main">
        <div class="hash-container">
            <form id="hashForm" class="login-form">
                <h1>生成密码哈希</h1>
                <div class="input-group">
                    <label for="password">输入密码</label>
                    <input type="password" id="password" required>
                </div>
                <div class="input-group">
                    <label for="confirmPassword">确认密码</label>
                    <input type="password" id="confirmPassword" required>
                </div>
                <button type="submit" class="admin-button">生成哈希</button>
                
                <div id="hashResult" class="hash-result">
                    <div>生成的哈希值:</div>
                    <div id="hashValue" style="margin: 8px 0;"></div>
                    <div id="verifyResult" class="success hidden">验证成功 ✓</div>
                    <button id="copyButton" class="admin-button copy-button">复制哈希值</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.getElementById('hashForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                alert('两次输入的密码不一致');
                return;
            }
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `password=${encodeURIComponent(password)}`
                });
                
                const data = await response.json();
                
                const hashResult = document.getElementById('hashResult');
                const hashValue = document.getElementById('hashValue');
                const verifyResult = document.getElementById('verifyResult');
                
                hashValue.textContent = data.hash;
                hashResult.style.display = 'block';
                verifyResult.classList.toggle('hidden', !data.verify);
                
            } catch (err) {
                alert('生成哈希失败');
                console.error(err);
            }
        });

        document.getElementById('copyButton').addEventListener('click', () => {
            const hashValue = document.getElementById('hashValue').textContent;
            navigator.clipboard.writeText(
                `define('ADMIN_PASSWORD_HASH', '${hashValue}');`
            ).then(() => {
                alert('已复制到剪贴板');
            }).catch(() => {
                alert('复制失败');
            });
        });
    </script>
</body>
</html>
?> 