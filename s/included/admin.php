<?php
// 引入数据库配置
require_once '../../config/MySQL-Configs.php';
// 引入管理员验证
require_once 'admin-auth.php';
checkAdminAuth();

// 获取网站设置
try {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) {
        throw new Exception("数据库连接失败: " . mysqli_connect_error());
    }
    mysqli_set_charset($conn, "utf8mb4");
    
    $sql = "SELECT setting_name, setting_value FROM WEBSITE_SETTINGS";
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        throw new Exception("查询失败: " . mysqli_error($conn));
    }
    
    $settings = [];
    while($row = mysqli_fetch_assoc($result)) {
        $settings[$row['setting_name']] = $row['setting_value'];
    }
    
    $site_title = $settings['title'] ?? '一搜 - 管理后台';
    
} catch (Exception $e) {
    error_log('获取网站设置失败: ' . $e->getMessage());
    $site_title = '一搜 - 管理后台';
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        mysqli_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理后台 - <?php echo $site_title; ?></title>
    <link rel="stylesheet" href="../../css/submit.css">
</head>
<body>
    <header class="admin-header">
        <div class="header-content">
            <a href="../../index.php" class="logo">
                <img src="../../img/logo.png" alt="Logo" height="30">
            </a>
            <div class="admin-nav">
                <span class="admin-welcome">欢迎, 管理员</span>
                <a href="admin-auth.php?logout=1" class="admin-button">注销</a>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="submit-container">
            <h1>管理后台</h1>
            <div class="admin-form">
                <div class="input-group">
                    <label>网站地址</label>
                    <input type="url" id="urlInput" placeholder="请输入网站地址 (https://example.com)">
                </div>
                <div class="input-group">
                    <label>网站标题</label>
                    <input type="text" id="titleInput" placeholder="请输入网站标题">
                </div>
                <div class="input-group">
                    <label>网站描述</label>
                    <textarea id="descInput" placeholder="请输入网站描述"></textarea>
                </div>
                <div class="button-group">
                    <button id="submitBtn" class="admin-button">提交收录</button>
                </div>
            </div>
        </div>
    </main>

    <script>
        window.API_BASE = '../../api';
        
        document.addEventListener('DOMContentLoaded', () => {
            const urlInput = document.getElementById('urlInput');
            const titleInput = document.getElementById('titleInput');
            const descInput = document.getElementById('descInput');
            const submitBtn = document.getElementById('submitBtn');

            async function securePost(url, data) {
                const timestamp = Date.now();
                const nonce = Math.random().toString(36).substring(2);
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Timestamp': timestamp,
                        'X-Nonce': nonce,
                        'X-Admin': 'true'
                    },
                    credentials: 'same-origin',  // 发送 session cookie
                    body: JSON.stringify(data)
                });

                if (response.status === 403) {
                    // 如果会话过期，跳转到登录页
                    window.location.href = 'login.php?expired=1';
                    return;
                }

                return response.json();
            }

            submitBtn.addEventListener('click', async () => {
                const url = urlInput.value.trim();
                const title = titleInput.value.trim();
                const description = descInput.value.trim();

                if (!url || !title || !description) {
                    alert('请填写完整信息');
                    return;
                }

                try {
                    submitBtn.disabled = true;
                    submitBtn.textContent = '提交中...';
                    
                    const data = await securePost(`${window.API_BASE}/url-update-admin.php`, {
                        url,
                        title,
                        description
                    });
                    
                    if (data.code === 200) {
                        alert('提交成功');
                        // 清空表单
                        urlInput.value = '';
                        titleInput.value = '';
                        descInput.value = '';
                    } else {
                        alert(data.message || '提交失败');
                    }
                } catch (err) {
                    console.error('错误详情:', err);
                    alert('提交失败');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = '提交收录';
                }
            });

            // 自动保存草稿
            let draftTimeout;
            const saveDraft = () => {
                const draft = {
                    url: urlInput.value,
                    title: titleInput.value,
                    description: descInput.value
                };
                localStorage.setItem('adminDraft', JSON.stringify(draft));
            };

            [urlInput, titleInput, descInput].forEach(input => {
                input.addEventListener('input', () => {
                    clearTimeout(draftTimeout);
                    draftTimeout = setTimeout(saveDraft, 1000);
                });
            });

            // 恢复草稿
            const draft = localStorage.getItem('adminDraft');
            if (draft) {
                try {
                    const { url, title, description } = JSON.parse(draft);
                    urlInput.value = url || '';
                    titleInput.value = title || '';
                    descInput.value = description || '';
                } catch (e) {
                    console.error('恢复草稿失败:', e);
                }
            }
        });
    </script>
</body>
</html>