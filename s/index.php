<?php
    // 不提示错误信息
    error_reporting(0);
    require_once '../config/MySQL-Configs.php';

    // 获取网站设置和热搜词
    try {
        $conn = getDBConnection();
        
        // 获取网站设置
        $sql = "SELECT setting_name, setting_value FROM WEBSITE_SETTINGS";
        $result = $conn->query($sql);
        
        $settings = [];
        while($row = $result->fetch_assoc()) {
            $settings[$row['setting_name']] = $row['setting_value'];
        }
        
        $site_title = $settings['title'] ?? '一搜 - 一搜就知道';
        $header_links = json_decode($settings['hander'] ?? '{}', true);
        $footer_links = json_decode($settings['footer'] ?? '{}', true);
        
        // 获取热搜词
        $hotwords_sql = "SELECT keyword, search_count 
                         FROM hotwords 
                         WHERE last_search >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                         ORDER BY search_count DESC 
                         LIMIT 10";
        $hotwords_result = $conn->query($hotwords_sql);
        $hotwords = [];
        while($row = $hotwords_result->fetch_assoc()) {
            $hotwords[] = $row;
        }
        
        // 获取随机 placeholder
        $placeholder_sql = "SELECT keyword 
                           FROM hotwords 
                           WHERE last_search >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                           ORDER BY RAND() 
                           LIMIT 1";
        $placeholder_result = $conn->query($placeholder_sql);
        $placeholder = $placeholder_result->fetch_assoc()['keyword'] ?? '一搜就有，一搜就知道';
        
    } catch (Exception $e) {
        error_log('获取数据失败: ' . $e->getMessage());
    } finally {
        if (isset($conn)) {
            closeDBConnection($conn);
        }
    }

    // 判断是否进行安全验证 - 防CC
//    $verification = verificationForm();

//    $userIf = null;
//    if ($_COOKIE['verification'] == null) {
//        header('Location: ../index.html');
//        $userIf = null;
//    }

//    function verificationForm () {
//        if ($_POST['verificationCode'] != null) {
//            setcookie("verification", "true", 0);
//        }
//    }

    // 把HTML代码转换成字符串 (代码是在网上找的)
    function htmlToString ($content){								//定义自定义函数的名称
        $content = htmlspecialchars($content);                //转换文本中的特殊字符
        $content = str_ireplace(chr(13),"<br>",$content);		//替换文本中的换行符
        $content = str_ireplace(chr(32)," ",$content);		//替换文本中的 
        $content = str_ireplace("[_[","<",$content);			//替换文本中的小于号
        $content = str_ireplace(")_)",">",$content);			//替换文本中的大于号
        $content = str_ireplace("|_|"," ",$content);				//替换文本中的空格
        return trim($content);								//删除文本中首尾的空格
    }

    /* search */
    // 配置搜索引擎数据库文件路径地址
    $webDatabasePath = "./database/webInfo/webinfo.yiso";
    
    // 网站标题
    function formInfo ($searchInfo) {
        if ($searchInfo != null) {
            // 设置搜索信息 - 搜索框
            // 设置网页标题信息 - 网页标题
            echo "<script>
                var searchInput = document.getElementsByClassName('searchInput')[0];
                searchInput.value = '".$searchInfo."';
                
                var pageTitle = document.getElementsByTagName('title')[0];
                pageTitle.innerText = '".$searchInfo." - 一搜';
            </script>";

            // 返回逻辑值
            $formInfoIf = true;
            return $formInfoIf;
        } else {
            // 返回逻辑值
            $formInfoIf = false;
            return $formInfoIf;
        }
    }

    // 判断是否有数据
    $searchInfo = $_GET['searchInput'] ?? '';
    $formInfo = formInfo($searchInfo);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $searchInfo ? $searchInfo.' - '.$site_title : $site_title; ?></title>

    <link rel="stylesheet" href="../css/search.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="header-left">
                <a href="../index.html" class="logo">
                    <img src="../img/logo.png" alt="Yiso" height="30">
                </a>
                <div class="search-box">
                    <form action="./index.php" method="get" class="search-bar">
                        <input type="text" name="searchInput" class="search-input" 
                               value="<?php echo htmlspecialchars($searchInfo); ?>" 
                               placeholder="<?php echo htmlspecialchars($placeholder); ?>">
                        <button type="submit" class="colored-search-button">
                            <div class="colored-search-icon"></div>
                        </button>
                    </form>
                </div>
            </div>
            <div class="header-right">
                <?php foreach($header_links as $link): ?>
                    <a href="<?php echo htmlspecialchars($link['URL']); ?>" 
                       target="_blank"><?php echo htmlspecialchars($link['NAME']); ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </header>

    <?php if ($searchInfo === ''): ?>
    <main class="empty-search">
        <div class="search-suggestions">
            <?php if (!empty($hotwords)): ?>
                <div class="trending-searches">
                    <div class="trending-header">
                        <div class="trending-icon"></div>
                        <span>热门搜索</span>
                    </div>
                    <div class="trending-list">
                        <?php foreach(array_slice($hotwords, 0, 5) as $index => $word): ?>
                        <a href="?searchInput=<?php echo urlencode($word['keyword']); ?>" 
                           class="trending-item rank-<?php echo $index + 1; ?>">
                            <div class="trending-rank"><?php echo $index + 1; ?></div>
                            <span class="trending-keyword"><?php echo htmlspecialchars($word['keyword']); ?></span>
                            <span class="trending-count">
                                <?php 
                                $count = $word['search_count'];
                                if ($count > 10000) {
                                    echo round($count/10000, 1) . '万';
                                } else {
                                    echo number_format($count);
                                }
                                ?> 次搜索
                            </span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php else: ?>
    <nav class="search-nav">
        <div class="search-stats"></div>
    </nav>
    <main class="search-results">
        <div id="resultsList"></div>
        <div id="pagination" class="pagination"></div>
        <div id="loading" class="loading">
            <div class="loading-spinner"></div>
            <span>搜索中...</span>
        </div>
    </main>

    <script>
    // 添加分页相关变量
    let currentPage = 1;
    const pageSize = 10;

    // 更新搜索函数
    async function search(page = 1) {
        const query = document.querySelector('.search-input').value;
        if(!query) return;
        
        document.getElementById('loading').style.display = 'flex';
        document.getElementById('resultsList').innerHTML = '';
        
        try {
            const response = await fetch(`../api/search.php?q=${encodeURIComponent(query)}&page=${page}`);
            const data = await response.json();
            
            if(data.code === 200) {
                renderResults(data.data.results);
                renderPagination(data.data.total, page);
                updateSearchStats(data.data);
            }
        } catch(err) {
            console.error('搜索出错:', err);
        } finally {
            document.getElementById('loading').style.display = 'none';
        }
    }

    // 渲染搜索结果
    function renderResults(results) {
        const resultsList = document.getElementById('resultsList');
        resultsList.innerHTML = results.map(result => {
            // 获取认证等级对应的文本
            let authText = '';
            let authLevel = '';
            switch(result.auth_level) {
                case 1:
                    authText = '官方认证';
                    authLevel = 'auth-level-1';
                    break;
                case 2:
                    authText = '政府认证';
                    authLevel = 'auth-level-2';
                    break;
                case 3:
                    authText = '企业认证';
                    authLevel = 'auth-level-3';
                    break;
                case 4:
                    authText = '个人认证';
                    authLevel = 'auth-level-4';
                    break;
                case 5:
                    authText = '自营认证';
                    authLevel = 'auth-level-5';
                    break;
            }
            
            return `
                <div class="search-result">
                    <h3>
                        <a href="${result.url}" target="_blank">${result.title}</a>
                        ${result.auth_level > 0 ? 
                            `<span class="auth-badge ${authLevel}">${authText}</span>` 
                            : ''}
                    </h3>
                    <div class="result-url">${result.url}</div>
                    <div class="result-snippet">${result.snippet}</div>
                </div>
            `;
        }).join('');
    }

    // 渲染分页
    function renderPagination(total, currentPage) {
        const totalPages = Math.ceil(total / pageSize);
        const pagination = document.getElementById('pagination');
        pagination.innerHTML = '';
        
        // 上一页按钮
        const prevButton = document.createElement('button');
        prevButton.className = `pagination-button ${currentPage === 1 ? 'disabled' : ''}`;
        prevButton.textContent = '上一页';
        prevButton.onclick = () => currentPage > 1 && search(currentPage - 1);
        pagination.appendChild(prevButton);
        
        // 页码信息
        const pageInfo = document.createElement('span');
        pageInfo.className = 'pagination-info';
        pageInfo.textContent = `第 ${currentPage}/${totalPages} 页`;
        pagination.appendChild(pageInfo);
        
        // 下一页按钮
        const nextButton = document.createElement('button');
        nextButton.className = `pagination-button ${currentPage === totalPages ? 'disabled' : ''}`;
        nextButton.textContent = '下一页';
        nextButton.onclick = () => currentPage < totalPages && search(currentPage + 1);
        pagination.appendChild(nextButton);
    }

    // 更新搜索统计信息
    function updateSearchStats(data) {
        const stats = document.querySelector('.search-stats');
        stats.innerHTML = `找到约 ${data.total} 个结果 (用时 ${data.search_time} 秒)`;
    }

    // 初始搜索
    search(1);
    </script>
    <?php endif; ?>

    <footer class="footer">
        <div class="footer-links">
            <?php foreach($footer_links as $link): ?>
                <a href="<?php echo htmlspecialchars($link['URL']); ?>" 
                   target="_blank"><?php echo htmlspecialchars($link['NAME']); ?></a>
            <?php endforeach; ?>
        </div>
    </footer>
</body>
</html>