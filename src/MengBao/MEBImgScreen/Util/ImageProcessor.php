<?php

declare(strict_types=1);

namespace MengBao\MEBAdScreen\Util;

class ImageProcessor
{
    /**
     * 加载图片并调整尺寸，返回 RGB 数组
     * @return array|null Array of ['r' => int, 'g' => int, 'b' => int]
     */
    public function loadAndResizeForBlocks(string $path, int $targetWidth, int $targetHeight): ?array
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

        return $this->imageToRgbArray($resized);
    }

    /**
     * 将 GD 图像转换为 RGB 数组
     */
    private function imageToRgbArray($image): array
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

                $pixels[] = ['r' => $r, 'g' => $g, 'b' => $b];
            }
        }

        imagedestroy($image);
        return $pixels;
    }
}
