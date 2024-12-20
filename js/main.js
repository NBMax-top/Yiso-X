// 访问统计相关
async function getVisitStats() {
    try {
        const response = await fetch('/api/stats.php');
        const data = await response.json();
        if(data.code === 200) {
            document.getElementById('visitCount').innerText = `今天访问量: ${data.data.count}`;
        }
    } catch(err) {
        console.log('获取访问统计失败');
    }
}

// 时间显示
function updateDateTime() {
    const date = new Date();
    const dateStr = `${date.getFullYear()}/${date.getMonth()+1}/${date.getDate()} ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()}`;
    document.getElementById('currentTime').innerText = dateStr;
}

// 最近收录
async function getRecentSites() {
    try {
        const response = await fetch('/api/recent.php');
        const data = await response.json();
        if(data.code === 200) {
            renderRecentSites(data.data);
        }
    } catch(err) {
        console.log('获取最近收录失败');
    }
}

// 获取网站设置
async function getWebsiteSettings() {
    try {
        const response = await fetch('/api/settings.php');
        const data = await response.json();
        
        if(data.code === 200) {
            const settings = data.data.settings;
            
            // 渲染顶栏
            const header = document.querySelector('.header');
            const headerLinks = JSON.parse(settings.hander || '{"1":{"NAME":"首页","URL":"./index.html"}}');
            let headerHtml = '';
            
            Object.values(headerLinks).forEach(link => {
                headerHtml += `<a href="${link.URL}" ${link.URL.startsWith('http') ? 'target="_blank"' : ''}>${link.NAME}</a>`;
            });
            header.innerHTML = headerHtml;

            // 渲染底栏
            const footerLinks = document.querySelector('.footer-links');
            const footerData = JSON.parse(settings.footer || '{"1":{"NAME":"帮助","URL":"./help/TheREADMEForUser.html"}}');
            let footerHtml = '';
            
            Object.values(footerData).forEach(link => {
                footerHtml += `<a href="${link.URL}" ${link.URL.startsWith('http') ? 'target="_blank"' : ''}>${link.NAME}</a>`;
            });
            footerLinks.innerHTML = footerHtml;

            // 设置网站标题
            document.title = settings.title || '极答 - 一搜极答';
            
            // 设置搜索框占位符的打字机效果
            const searchInput = document.querySelector('.search-input');
            const searchForm = document.querySelector('.search-bar');
            const slogan = settings.slogan || '一搜极答，在这里搜索';
            let isInputFocused = false;
            let currentPlaceholder = '';
            
            searchInput.addEventListener('focus', () => {
                isInputFocused = true;
                searchInput.placeholder = currentPlaceholder || slogan;
            });
            
            searchInput.addEventListener('blur', () => {
                isInputFocused = false;
            });
            
            // 监听表单提交
            searchForm.addEventListener('submit', (e) => {
                if (!searchInput.value.trim() && currentPlaceholder) {
                    e.preventDefault();
                    searchInput.value = currentPlaceholder;
                    searchForm.submit();
                }
            });
            
            // 修改打字机效果函数
            async function typeWriter(text) {
                let charIndex = 0;
                currentPlaceholder = text.fullText || text;
                const displayText = text.displayText || text;
                searchInput.placeholder = '';
                
                return new Promise((resolve) => {
                    function type() {
                        if(charIndex < displayText.length && !isInputFocused) {
                            searchInput.placeholder = displayText.substring(0, charIndex + 1);
                            charIndex++;
                            setTimeout(type, 100);
                        } else {
                            resolve();
                        }
                    }
                    type();
                });
            }
            
            // 获取一言API内容
            async function getYiyan() {
                try {
                    const response = await fetch('https://v.api.aa1.cn/api/yiyan/index.php');
                    const text = await response.text();
                    
                    const match = text.match(/<p>(.*?)<\/p>/);
                    if (match && match[1]) {
                        const yiyan = match[1].trim();
                        
                        // 智能截断
                        const searchInput = document.querySelector('.search-input');
                        const maxChars = Math.floor(searchInput.offsetWidth / 16); // 假设每个字符16px
                        
                        if (yiyan.length > maxChars) {
                            const truncated = yiyan.substring(0, maxChars);
                            // 查找最后一个标点符号位置
                            const lastPunct = truncated.search(/[，。！？、；,.!?;\s][^，。！？、；,.!?;\s]*$/);
                            
                            return {
                                fullText: yiyan,  // 完整文本用于搜索
                                displayText: lastPunct > maxChars * 0.6 ? 
                                    truncated.substring(0, lastPunct + 1) + '...' : 
                                    truncated + '...'  // 截断文本用于显示
                            };
                        }
                        
                        return {
                            fullText: yiyan,
                            displayText: yiyan
                        };
                    }
                    return {
                        fullText: slogan,
                        displayText: slogan
                    };
                } catch(err) {
                    console.error('获取一言失败:', err);
                    return {
                        fullText: slogan,
                        displayText: slogan
                    };
                }
            }
            
            // 循环展示打字效果
            async function loopTyping() {
                // 第一次显示默认标语
                await typeWriter({ fullText: slogan, displayText: slogan });
                await new Promise(resolve => setTimeout(resolve, 3000));
                
                // 之后循环显示一言
                while(true) {
                    if (!isInputFocused) {
                        const yiyan = await getYiyan();
                        await typeWriter(yiyan);
                        await new Promise(resolve => setTimeout(resolve, 3000));
                    } else {
                        await new Promise(resolve => setTimeout(resolve, 1000));
                    }
                }
            }
            
            // 开始循环
            loopTyping();

            // 试试手气按钮
            const luckyBtn = document.getElementById('luckyBtn');
            luckyBtn.addEventListener('click', async () => {
                try {
                    // 优先尝试获取随机网站标题
                    const response = await fetch('./api/random-webInfoTitle.php');
                    const data = await response.json();
                    
                    if (data.code === 200 && data.data) {
                        // 如果成功获取到随机标题，直接使用
                        window.location.href = `./s/index.php?searchInput=${encodeURIComponent(data.data)}`;
                    } else {
                        // 如果获取失败，回退到一言
                        const yiyan = await getYiyan();
                        window.location.href = `./s/index.php?searchInput=${encodeURIComponent(yiyan.fullText)}`;
                    }
                } catch (err) {
                    console.error('试试手气出错:', err);
                    // 如果都失败了，使用当前占位符
                    if (currentPlaceholder) {
                        window.location.href = `./s/index.php?searchInput=${encodeURIComponent(currentPlaceholder)}`;
                    }
                }
            });
        }
    } catch(err) {
        console.error('获取网站设置失败:', err);
        // 使用默认值
        const searchInput = document.querySelector('.search-input');
        const slogan = '一搜极答，在这里搜索';
        searchInput.placeholder = slogan;
        
        // 即使设置加载失败，也启动打字机效果
        startTypingEffect(searchInput, slogan);
    }
}

// 启动打字机效果的辅助函数
async function startTypingEffect(input, defaultSlogan) {
    let isInputFocused = false;
    input.addEventListener('focus', () => {
        isInputFocused = true;
    });
    
    input.addEventListener('blur', () => {
        isInputFocused = false;
    });
    
    while(true) {
        if (!isInputFocused) {
            try {
                const response = await fetch('https://v.api.aa1.cn/api/yiyan/index.php');
                const text = await response.text();
                const match = text.match(/<p>(.*?)<\/p>/);
                const yiyan = match && match[1] ? match[1].trim() : defaultSlogan;
                
                input.placeholder = '';
                for(let i = 0; i < yiyan.length; i++) {
                    if (!isInputFocused) {
                        input.placeholder = yiyan.substring(0, i + 1);
                        await new Promise(resolve => setTimeout(resolve, 100));
                    }
                }
                await new Promise(resolve => setTimeout(resolve, 3000));
            } catch(err) {
                console.error('一言获取失败:', err);
                await new Promise(resolve => setTimeout(resolve, 1000));
            }
        } else {
            await new Promise(resolve => setTimeout(resolve, 1000));
        }
    }
}

// 页面加载完成后执行
document.addEventListener('DOMContentLoaded', getWebsiteSettings); 