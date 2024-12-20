<?php
    // 生成验证码 (方法 Function)
    function verifyCode () {
        // 设置页面类型
        header("Content-Type: image/png");

        // 画布宽高
        $width = 120;   // 增加宽度
        $height = 40;   // 与输入框等高

        // 创建画布
        $image = imagecreatetruecolor($width, $height);
        
        // 设置颜色
        $bgColor = imagecolorallocate($image, 245, 245, 245);  // 浅灰背景
        $textColor = imagecolorallocate($image, 66, 133, 244); // Google蓝
        $lineColor = imagecolorallocate($image, 200, 200, 200); // 加深干扰线颜色
        
        // 填充背景
        imagefill($image, 0, 0, $bgColor);
        
        // 添加干扰线 (增加数量和随机性)
        for ($i = 0; $i < 6; $i++) {  // 增加到6条线
            $lineX1 = mt_rand(0, $width);
            $lineY1 = mt_rand(0, $height);
            $lineX2 = mt_rand(0, $width);
            $lineY2 = mt_rand(0, $height);
            // 使用虚线效果
            if ($i % 2 == 0) {
                imagedashedline($image, $lineX1, $lineY1, $lineX2, $lineY2, $lineColor);
            } else {
                imageline($image, $lineX1, $lineY1, $lineX2, $lineY2, $lineColor);
            }
        }
        
        // 添加干扰点 (增加数量)
        for ($i = 0; $i < 100; $i++) {  // 增加到100个点
            $pointX = mt_rand(0, $width);
            $pointY = mt_rand(0, $height);
            imagesetpixel($image, $pointX, $pointY, $lineColor);
        }

        // 生成验证码文本
        $code = '';
        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'; // 去掉容易混淆的字符
        for ($i = 0; $i < 4; $i++) {
            $code .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        
        // 确保字体文件存在
        $fontFile = __DIR__ . '/../../fonts/arial.ttf';
        if (!file_exists($fontFile)) {
            die('字体文件不存在');
        }
        
        // 写入验证码文本
        $fontSize = 20;  // 调整字体大小
        
        // 使用 arial.ttf
        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            $angle = mt_rand(-15, 15);  // 随机倾斜角度
            $x = 15 + ($i * 25);  // 调整间距
            $y = 28;  // 调整垂直位置
            
            // 添加文字阴影效果
            $shadowColor = imagecolorallocate($image, 180, 180, 180);
            imagettftext($image, $fontSize, $angle, $x+1, $y+1, $shadowColor, $fontFile, $char);
            
            // 添加主文字
            imagettftext($image, $fontSize, $angle, $x, $y, $textColor, $fontFile, $char);
        }
        
        // 保存验证码到 cookie
        setcookie("yanZhengMa", $code, time() + 300, "/"); // 5分钟有效期
        
        // 输出图片
        imagepng($image);
        imagedestroy($image);
    }

    // 执行
    verifyCode();
?>