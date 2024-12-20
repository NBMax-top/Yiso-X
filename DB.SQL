CREATE TABLE `web_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '网站标题',
  `description` text COMMENT '网站描述',
  `url` varchar(255) NOT NULL COMMENT '网站链接',
  `auth_level` tinyint(4) DEFAULT '0' COMMENT '认证等级:0未认证,1-4不同认证',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_title` (`title`),
  KEY `idx_url` (`url`)
)

CREATE TABLE `visit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL,
  `visit_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_visit_time` (`visit_time`)
) 

CREATE TABLE `WEBSITE_SETTINGS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(255) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_setting_name` (`setting_name`)
) 

-- 默认网站设置
INSERT INTO `WEBSITE_SETTINGS` (`setting_name`, `setting_value`) VALUES ('title', '极答 - 一搜极答 - 基于 YISO 的 Fork —— YISO-X');
INSERT INTO `WEBSITE_SETTINGS` (`setting_name`, `setting_value`) VALUES ('description', '极答 - 一搜极答 - 基于 YISO 的 Fork —— YISO-X');
INSERT INTO `WEBSITE_SETTINGS` (`setting_name`, `setting_value`) VALUES ('hander', '{"1":{"NAME":"开源","URL":"https://github.com/NBMax-top/Yiso-X"},"2":{"NAME":"介绍","URL":"https://yisox.nbmax.top/about"}}');
INSERT INTO `WEBSITE_SETTINGS` (`setting_name`, `setting_value`) VALUES ('footer', '{"1":{"NAME":"关于","URL":"https://yisox.nbmax.top/about"},"2":{"NAME":"隐私","URL":"https://yisox.nbmax.top/privacy"},"3":{"NAME":"条款","URL":"https://yisox.nbmax.top/terms"}}');
INSERT INTO `WEBSITE_SETTINGS` (`setting_name`, `setting_value`) VALUES ('slogan', '一搜极答，在这里搜索。');


CREATE TABLE hotwords (
    id INT AUTO_INCREMENT PRIMARY KEY,
    keyword VARCHAR(100) NOT NULL,
    search_count INT DEFAULT 1,
    last_search DATETIME DEFAULT CURRENT_TIMESTAMP,
    create_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    update_time DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (keyword)
);