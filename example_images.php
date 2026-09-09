<?php

/**
 * 示例图片生成器
 * 运行此脚本可生成测试用的示例图片
 *
 * 用法：php example_images.php
 */

if (!extension_loaded('gd')) {
    die("需要 GD 扩展\n");
}

$outputDir = __DIR__ . "/images/";
@mkdir($outputDir, 0777, true);

echo "正在生成示例图片...\n\n";

// 1. 欢迎屏 - 256x256
function createWelcome(): void
{
    global $outputDir;

    $img = imagecreatetruecolor(256, 256);

    // 渐变背景
    for ($y = 0; $y < 256; $y++) {
        $color = imagecolorallocate($img, 30, 30 + ($y / 2), 100 + ($y / 2));
        imagefilledrectangle($img, 0, $y, 256, $y + 1, $img);
    }

    // 文字
    $white = imagecolorallocate($img, 255, 255, 255);
    $yellow = imagecolorallocate($img, 255, 215, 0);

    imagestring($img, 5, 70, 100, "WELCOME", $yellow);
    imagestring($img, 3, 50, 130, "to our server!", $white);

    imagepng($img, $outputDir . "welcome.png");
    imagedestroy($img);

    echo "✓ welcome.png (256×256)\n";
}

// 2. 规则公告 - 384x256
function createRules(): void
{
    global $outputDir;

    $img = imagecreatetruecolor(384, 256);

    // 背景
    $bg = imagecolorallocate($img, 40, 40, 40);
    imagefilledrectangle($img, 0, 0, 384, 256, $bg);

    // 边框
    $border = imagecolorallocate($img, 255, 165, 0);
    imagerectangle($img, 5, 5, 378, 250, $border);

    // 标题
    $white = imagecolorallocate($img, 255, 255, 255);
    imagestring($img, 5, 130, 20, "SERVER RULES", $white);

    // 规则列表
    $rules = [
        "1. Be respectful",
        "2. No griefing",
        "3. No cheating",
        "4. Have fun!"
    ];

    $y = 60;
    foreach ($rules as $rule) {
        imagestring($img, 3, 40, $y, $rule, $white);
        $y += 30;
    }

    imagepng($img, $outputDir . "rules.png");
    imagedestroy($img);

    echo "✓ rules.png (384×256)\n";
}

// 3. 商店广告 - 256x256
function createShop(): void
{
    global $outputDir;

    $img = imagecreatetruecolor(256, 256);

    // 红色背景
    $red = imagecolorallocate($img, 200, 0, 0);
    imagefilledrectangle($img, 0, 0, 256, 256, $red);

    // 黄色圆圈
    $yellow = imagecolorallocate($img, 255, 255, 0);
    imagefilledellipse($img, 128, 128, 180, 180, $yellow);

    // 文字
    $black = imagecolorallocate($img, 0, 0, 0);
    imagestring($img, 5, 80, 100, "50% OFF", $black);
    imagestring($img, 4, 70, 130, "Limited Time", $black);

    imagepng($img, $outputDir . "shop_sale.png");
    imagedestroy($img);

    echo "✓ shop_sale.png (256×256)\n";
}

// 4. 彩色测试图 - 512x512
function createColorTest(): void
{
    global $outputDir;

    $img = imagecreatetruecolor(512, 512);

    // 彩虹色块
    $colors = [
        [255, 0, 0],    // 红
        [255, 127, 0],  // 橙
        [255, 255, 0],  // 黄
        [0, 255, 0],    // 绿
        [0, 0, 255],    // 蓝
        [75, 0, 130],   // 靛
        [148, 0, 211],  // 紫
    ];

    $blockHeight = 512 / count($colors);

    foreach ($colors as $i => $rgb) {
        $color = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
        imagefilledrectangle($img, 0, $i * $blockHeight, 512, ($i + 1) * $blockHeight, $color);
    }

    // 标题
    $white = imagecolorallocate($img, 255, 255, 255);
    imagestring($img, 5, 180, 240, "COLOR TEST", $white);

    imagepng($img, $outputDir . "colortest.png");
    imagedestroy($img);

    echo "✓ colortest.png (512×512)\n";
}

// 5. 简单 "GIF"（实际是多张PNG，演示用）
function createAnimationFrames(): void
{
    global $outputDir;

    $frames = 8;

    for ($f = 0; $f < $frames; $f++) {
        $img = imagecreatetruecolor(256, 256);

        // 黑色背景
        $bg = imagecolorallocate($img, 0, 0, 0);
        imagefilledrectangle($img, 0, 0, 256, 256, $bg);

        // 旋转的圆
        $angle = ($f / $frames) * 360;
        $x = 128 + cos(deg2rad($angle)) * 60;
        $y = 128 + sin(deg2rad($angle)) * 60;

        $color = imagecolorallocate($img, 0, 255, 255);
        imagefilledellipse($img, $x, $y, 40, 40, $color);

        imagepng($img, $outputDir . "anim_frame_{$f}.png");
        imagedestroy($img);
    }

    echo "✓ anim_frame_0.png ~ anim_frame_7.png (动画帧示例)\n";
}

// 6. Logo 示例 - 128x128
function createLogo(): void
{
    global $outputDir;

    $img = imagecreatetruecolor(128, 128);

    // 透明背景（实际PNG会保存透明）
    $bg = imagecolorallocate($img, 255, 255, 255);
    imagefilledrectangle($img, 0, 0, 128, 128, $bg);

    // 简单的 "M" 字母
    $blue = imagecolorallocate($img, 0, 100, 200);
    $points = [
        20, 100,   // 左下
        20, 30,    // 左上
        64, 70,    // 中间
        108, 30,   // 右上
        108, 100,  // 右下
    ];

    imagefilledpolygon($img, $points, 5, $blue);

    imagepng($img, $outputDir . "logo.png");
    imagedestroy($img);

    echo "✓ logo.png (128×128)\n";
}

// 执行生成
createWelcome();
createRules();
createShop();
createColorTest();
createAnimationFrames();
createLogo();

echo "\n✓ 所有示例图片已生成到: {$outputDir}\n";
echo "\n现在可以使用命令测试：\n";
echo "  /adscreen make welcome.png 2 2\n";
echo "  /adscreen make rules.png 3 2\n";
echo "  /adscreen make shop_sale.png 2 2\n";
echo "  /adscreen make colortest.png 4 4\n";
echo "  /adscreen make logo.png 1 1\n";
