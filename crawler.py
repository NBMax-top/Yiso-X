import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin, urlparse
import time
import random
from datetime import datetime
import re
import argparse
from tqdm import tqdm
from collections import deque

class WebCrawler:
    def __init__(self, fast_mode=False):
        self.visited_urls = set()
        self.url_queue = deque()
        self.sql_file = 'web_data.sql'
        self.fast_mode = fast_mode
        self.headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36 PythonRequests/2.31.0 YISO-X/1.0.0'
        }
    
    def clean_text(self, text):
        if not text:
            return ''
        text = re.sub(r'[\U00010000-\U0010ffff]', '', text)
        text = re.sub(r'[\x00-\x1f\x7f-\x9f]', '', text)
        text = text.replace("'", "''").replace("\\", "\\\\")
        text = ' '.join(text.split())
        return text
    
    def crawl_page(self, url, max_depth=2):
        self.url_queue.append((url, 0))  # (url, depth)
        domain = urlparse(url).netloc
        
        with tqdm(desc="爬取进度", unit="页") as pbar:
            while self.url_queue:
                current_url, current_depth = self.url_queue.popleft()
                
                if current_depth > max_depth or current_url in self.visited_urls:
                    continue
                    
                try:
                    pbar.set_postfix({
                        "当前深度": f"{current_depth}/{max_depth}",
                        "队列": len(self.url_queue),
                        "已爬取": len(self.visited_urls)
                    })
                    
                    response = requests.get(current_url, headers=self.headers, timeout=10)
                    response.encoding = response.apparent_encoding
                    
                    if response.status_code != 200:
                        continue
                        
                    self.visited_urls.add(current_url)
                    pbar.update(1)
                    
                    soup = BeautifulSoup(response.text, 'html.parser')
                    
                    title = soup.title.string if soup.title else ''
                    title = self.clean_text(title)
                    
                    description = ''
                    meta_desc = soup.find('meta', {'name': ['description', 'Description']})
                    if meta_desc and meta_desc.get('content'):
                        description = meta_desc['content']
                    else:
                        main_content = soup.find(['article', 'main', 'div', 'body'])
                        if main_content:
                            description = main_content.get_text()[:200]
                    description = self.clean_text(description)
                    
                    if title and description:
                        sql = self.generate_sql(current_url, title, description)
                        self.save_sql(sql)
                    
                    if not self.fast_mode:
                        time.sleep(random.uniform(1, 3))
                    
                    if current_depth < max_depth:
                        for link in soup.find_all('a', href=True):
                            next_url = urljoin(current_url, link['href'])
                            if urlparse(next_url).netloc == domain and next_url not in self.visited_urls:
                                self.url_queue.append((next_url, current_depth + 1))
                                
                except Exception as e:
                    tqdm.write(f"爬取错误 {current_url}: {str(e)}")
    
    def generate_sql(self, url, title, description):
        current_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        return f"""INSERT INTO `web_info` (`title`, `description`, `url`, `auth_level`, `create_time`) VALUES (N'{title}', N'{description}', '{url}', 0, '{current_time}');"""
    
    def save_sql(self, sql):
        with open(self.sql_file, 'a', encoding='utf-8') as f:
            f.write(sql + '\n')
    
    def start(self, start_url, max_depth=2):
        print("开始爬取...")
        print(f"快速模式: {'开启' if self.fast_mode else '关闭'}")
        
        with open(self.sql_file, 'w', encoding='utf-8') as f:
            f.write('-- 网站数据 SQL\n')
            f.write('-- 生成时间: ' + datetime.now().strftime('%Y-%m-%d %H:%M:%S') + '\n')
            f.write('-- 编码: UTF-8\n\n')
            f.write('SET NAMES utf8mb4;\n')
            f.write('SET FOREIGN_KEY_CHECKS = 0;\n\n')
        
        self.crawl_page(start_url, max_depth)
        
        with open(self.sql_file, 'a', encoding='utf-8') as f:
            f.write('\nSET FOREIGN_KEY_CHECKS = 1;\n')
        
        print(f"\n爬取完成！SQL文件已生成: {self.sql_file}")
        print(f"共爬取了 {len(self.visited_urls)} 个页面")

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='YISO-X 爬虫工具 By Python')
    parser.add_argument('--fast', action='store_true', help='启用快速模式（无延时）')
    parser.add_argument('--depth', type=int, default=2, help='最大爬取深度（默认：2）')
    parser.add_argument('--url', type=str, help='起始URL')
    
    args = parser.parse_args()
    
    if not args.url:
        args.url = input("请输入起始URL (例如 https://example.com): ")
        if not args.url:
            print("错误：必须提供起始URL")
            exit(1)
    
    crawler = WebCrawler(fast_mode=args.fast)
    crawler.start(args.url, args.depth) 