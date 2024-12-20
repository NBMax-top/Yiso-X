document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('.search-input');
    const resultsList = document.getElementById('resultsList');
    const loading = document.getElementById('loading');
    const searchStats = document.querySelector('.search-stats');

    // 搜索函数
    async function performSearch(query) {
        loading.style.display = 'block';
        resultsList.innerHTML = '';
        
        try {
            // 并行发送搜索请求和更新热搜词
            const [searchResponse, hotwordResponse] = await Promise.all([
                fetch(`/api/search.php?q=${encodeURIComponent(query)}`),
                fetch(`/api/add-hotword.php?q=${encodeURIComponent(query)}`)
            ]);
            
            const data = await searchResponse.json();
            
            if (data.code === 200) {
                // 更新搜索统计信息
                searchStats.textContent = `找到约 ${data.data.total} 个结果 (用时 ${data.data.search_time} 秒)`;
                
                // 显示搜索结果
                displayResults(data.data.results);
            } else {
                resultsList.innerHTML = `<div class="no-results">搜索出错: ${data.message}</div>`;
            }
        } catch (error) {
            console.error('搜索出错:', error);
            resultsList.innerHTML = '<div class="no-results">搜索服务暂时不可用，请稍后再试</div>';
        } finally {
            loading.style.display = 'none';
        }
    }

    // 显示结果
    function displayResults(results) {
        if (results.length === 0) {
            resultsList.innerHTML = '<div class="no-results">没有找到相关结果</div>';
            return;
        }

        resultsList.innerHTML = results.map(result => `
            <div class="search-result">
                <div class="result-header">
                    ${getAuthBadge(result.auth_level)}
                    <a href="${result.url}" class="result-title" target="_blank">${result.title}</a>
                </div>
                <div class="result-url">${formatUrl(result.url)}</div>
                <div class="result-snippet">${result.snippet}</div>
                <div class="result-meta">
                    <span class="result-date">${formatDate(result.create_time)}</span>
                </div>
            </div>
        `).join('');
    }

    // 格式化URL显示
    function formatUrl(url) {
        try {
            const urlObj = new URL(url);
            return urlObj.hostname + urlObj.pathname;
        } catch {
            return url;
        }
    }

    // 格式化日期
    function formatDate(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return date.toLocaleDateString('zh-CN', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    // 获取认证标识
    function getAuthBadge(authLevel) {
        if (!authLevel) return '';
        const badges = {
            1: '<span class="auth-badge level-1">官方</span>',
            2: '<span class="auth-badge level-2">企业</span>',
            3: '<span class="auth-badge level-3">个人</span>',
            4: '<span class="auth-badge level-4">特殊</span>'
        };
        return badges[authLevel] || '';
    }

    // 初始搜索
    const urlParams = new URLSearchParams(window.location.search);
    const initialQuery = urlParams.get('searchInput');
    if (initialQuery) {
        searchInput.value = initialQuery;
        performSearch(initialQuery);
    }
}); 