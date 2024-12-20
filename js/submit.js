document.addEventListener('DOMContentLoaded', () => {
    const urlInput = document.getElementById('urlInput');
    const captchaInput = document.getElementById('captchaInput');
    const fetchBtn = document.getElementById('fetchBtn');
    const submitBtn = document.getElementById('submitBtn');
    const resultContainer = document.getElementById('resultContainer');
    const titleInput = document.getElementById('titleInput');
    const descInput = document.getElementById('descInput');

    // 简化的安全请求函数
    async function securePost(url, data) {
        const timestamp = Date.now();
        const nonce = Math.random().toString(36).substring(2);
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Timestamp': timestamp,
                'X-Nonce': nonce
            },
            body: JSON.stringify({
                ...data,
                captcha: captchaInput.value
            })
        });
        return response.json();
    }

    // 获取网站信息
    fetchBtn.addEventListener('click', async () => {
        const url = urlInput.value.trim();
        if (!url) {
            alert('请输入网站地址');
            return;
        }

        try {
            fetchBtn.disabled = true;
            fetchBtn.textContent = '获取中...';
            
            const data = await securePost(`${window.API_BASE}/fetch-url-info.php`, { url });
            
            if (data.code === 200) {
                titleInput.value = data.data.title;
                descInput.value = data.data.description;
                resultContainer.style.display = 'block';
            } else {
                alert(data.message);
            }
        } catch (err) {
            console.error('错误详情:', err);
            alert('获取信息失败');
        } finally {
            fetchBtn.disabled = false;
            fetchBtn.textContent = '获取信息';
        }
    });

    // 提交收录
    submitBtn.addEventListener('click', async () => {
        try {
            submitBtn.disabled = true;
            submitBtn.textContent = '提交中...';
            
            const data = await securePost(`${window.API_BASE}/submit-url.php`, {
                url: urlInput.value.trim(),
                title: titleInput.value.trim(),
                description: descInput.value.trim()
            });
            
            alert(data.message);
            
            if (data.code === 200) {
                location.reload();
            }
        } catch (err) {
            console.error('错误详情:', err);
            alert('提交失败');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = '提交收录';
        }
    });
}); 