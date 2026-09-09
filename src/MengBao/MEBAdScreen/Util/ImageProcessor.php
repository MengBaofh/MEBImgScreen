<?php

declare(strict_types=1);

namespace MengBao\MEBAdScreen\Util;

class ImageProcessor
{
    /**
     * 加载图片并调整尺寸，返回Minecraft地图颜色索引数组
     */
    public function loadAndResize(string $path, int $targetWidth, int $targetHeight): ?array
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        switch ($ext) {
            case "png":
                $image = @imagecreatefrompng($path);
                break;
            case "jpg":
            case "jpeg":
                $image = @imagecreatefromjpeg($path);
                break;
            default:
                return null;
        }

        if ($image === false) {
            return null;
        }

        $resized = imagescale($image, $targetWidth, $targetHeight);
        imagedestroy($image);

        if ($resized === false) {
            return null;
        }

        return $this->imageToMapColors($resized);
    }

    /**
     * 加载GIF所有帧
     * @return array[] 每帧的像素数组
     */
    public function loadGifFrames(string $path, int $targetWidth, int $targetHeight): array
    {
        if (!extension_loaded("gd")) {
            return [];
        }

        // 尝试使用 Imagick（如果可用）
        if (extension_loaded("imagick")) {
            return $this->loadGifWithImagick($path, $targetWidth, $targetHeight);
        }

        // 降级到 GD（仅支持第一帧）
        return $this->loadGifWithGD($path, $targetWidth, $targetHeight);
    }

    /**
     * 使用 Imagick 加载 GIF（支持多帧）
     */
    private function loadGifWithImagick(string $path, int $targetWidth, int $targetHeight): array
    {
        try {
            $imagick = new \Imagick($path);
            $frames = [];

            // 限制最大帧数，避免内存爆炸
            $maxFrames = 60;
            $frameCount = min($imagick->getNumberImages(), $maxFrames);

            foreach ($imagick as $index => $frame) {
                if ($index >= $maxFrames) {
                    break;
                }

                // 合并图层（处理透明度）
                $frame = $frame->coalesceImages()->current();
                $frame->resizeImage($targetWidth, $targetHeight, \Imagick::FILTER_LANCZOS, 1);
                $frame->setImageFormat("png");

                // 转换为 GD 资源
                $gdImage = imagecreatefromstring($frame->getImageBlob());
                if ($gdImage !== false) {
                    $frames[] = $this->imageToMapColors($gdImage);
                    imagedestroy($gdImage);
                }
            }

            $imagick->clear();
            $imagick->destroy();

            return $frames;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 使用 GD 加载 GIF（仅第一帧）
     */
    private function loadGifWithGD(string $path, int $targetWidth, int $targetHeight): array
    {
        $image = @imagecreatefromgif($path);
        if ($image === false) {
            return [];
        }

        $resized = imagescale($image, $targetWidth, $targetHeight);
        imagedestroy($image);

        if ($resized === false) {
            return [];
        }

        $pixels = $this->imageToMapColors($resized);
        imagedestroy($resized);

        return [$pixels];
    }

    /**
     * 将GD图像转换为Minecraft地图颜色数组
     */
    private function imageToMapColors($image): array
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $pixels = [];

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $pixels[] = $this->rgbToMapColor($r, $g, $b);
            }
        }

        imagedestroy($image);
        return $pixels;
    }

    /**
     * 将RGB转换为最接近的Minecraft地图颜色ID
     */
    private function rgbToMapColor(int $r, int $g, int $b): int
    {
        static $colorMap = null;
        if ($colorMap === null) {
            $colorMap = $this->getMinecraftColorMap();
        }

        $minDistance = PHP_INT_MAX;
        $bestColor = 0;

        foreach ($colorMap as $id => $rgb) {
            // 使用欧几里得距离计算颜色差异
            $distance = pow($r - $rgb[0], 2) + pow($g - $rgb[1], 2) + pow($b - $rgb[2], 2);

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $bestColor = $id;
            }
        }

        return $bestColor;
    }

    /**
     * 完整的Minecraft基岩版地图颜色表
     * 包含62种基础颜色，每种4个亮度级别
     */
    private function getMinecraftColorMap(): array
    {
        // 基础颜色 * 4个亮度级别 (0=最暗, 1=暗, 2=正常, 3=亮)
        $baseColors = [
            // [R, G, B] 这里使用正常亮度的颜色
            [0, 0, 0],           // 0: 空气/透明
            [127, 178, 56],      // 1: 草
            [247, 233, 163],     // 2: 沙子
            [199, 199, 199],     // 3: 羊毛/床
            [255, 0, 0],         // 4: 火/岩浆
            [160, 160, 255],     // 5: 冰
            [167, 167, 167],     // 6: 金属/铁
            [0, 124, 0],         // 7: 植物/树叶
            [255, 255, 255],     // 8: 雪/白色
            [164, 168, 184],     // 9: 粘土
            [151, 109, 77],      // 10: 泥土/棕色
            [112, 112, 112],     // 11: 石头
            [64, 64, 255],       // 12: 水
            [143, 119, 72],      // 13: 木头
            [255, 252, 245],     // 14: 石英
            [216, 127, 51],      // 15: 橙色
            [178, 76, 216],      // 16: 品红色
            [102, 153, 216],     // 17: 淡蓝色
            [229, 229, 51],      // 18: 黄色
            [127, 204, 25],      // 19: 黄绿色
            [242, 127, 165],     // 20: 粉色
            [76, 76, 76],        // 21: 灰色
            [153, 153, 153],     // 22: 淡灰色
            [76, 127, 153],      // 23: 青色
            [127, 63, 178],      // 24: 紫色
            [51, 76, 178],       // 25: 蓝色
            [102, 76, 51],       // 26: 棕色
            [102, 127, 51],      // 27: 绿色
            [153, 51, 51],       // 28: 红色
            [25, 25, 25],        // 29: 黑色
            [250, 238, 77],      // 30: 金色
            [92, 219, 213],      // 31: 钻石色
            [74, 128, 255],      // 32: 青金石色
            [0, 217, 58],        // 33: 绿宝石色
            [129, 86, 49],       // 34: 黑曜石
            [112, 2, 0],         // 35: 下界
            [209, 177, 161],     // 36: 白色陶瓦
            [159, 82, 36],       // 37: 橙色陶瓦
            [149, 87, 108],      // 38: 品红色陶瓦
            [112, 108, 138],     // 39: 淡蓝色陶瓦
            [186, 133, 36],      // 40: 黄色陶瓦
            [103, 117, 53],      // 41: 黄绿色陶瓦
            [160, 77, 78],       // 42: 粉色陶瓦
            [57, 41, 35],        // 43: 灰色陶瓦
            [135, 107, 98],      // 44: 淡灰色陶瓦
            [87, 92, 92],        // 45: 青色陶瓦
            [122, 73, 88],       // 46: 紫色陶瓦
            [76, 62, 92],        // 47: 蓝色陶瓦
            [76, 50, 35],        // 48: 棕色陶瓦
            [76, 82, 42],        // 49: 绿色陶瓦
            [142, 60, 46],       // 50: 红色陶瓦
            [37, 22, 16],        // 51: 黑色陶瓦
            [189, 48, 49],       // 52: 深红色
            [148, 63, 97],       // 53: 深紫红色
            [92, 25, 29],        // 54: 猩红色
            [22, 126, 134],      // 55: 青色光源
            [58, 142, 140],      // 56: 青绿色
            [86, 44, 62],        // 57: 紫红色
            [20, 180, 133],      // 58: 绿松石色
            [100, 100, 100],     // 59: 灰色混凝土
            [216, 175, 147],     // 60: 粘土
            [127, 167, 150],     // 61: 苍白绿色
        ];

        $colorMap = [];
        $id = 0;

        foreach ($baseColors as $baseIndex => $base) {
            // 生成4个亮度级别
            for ($shade = 0; $shade < 4; $shade++) {
                $multiplier = match($shade) {
                    0 => 0.71,  // 最暗 (180/255)
                    1 => 0.86,  // 暗 (220/255)
                    2 => 1.00,  // 正常 (255/255)
                    3 => 0.53,  // 亮 (135/255) - 实际上最亮的在游戏里反而更暗
                };

                $colorMap[$id++] = [
                    (int)($base[0] * $multiplier),
                    (int)($base[1] * $multiplier),
                    (int)($base[2] * $multiplier),
                ];
            }
        }

        return $colorMap;
    }
}
