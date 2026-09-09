<?php

declare(strict_types=1);

namespace MengBao\MEBAdScreen\Manager;

use MengBao\MEBAdScreen\Main;
use MengBao\MEBAdScreen\Struct\AdScreen;
use MengBao\MEBAdScreen\Util\ImageProcessor;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\Item;

class MapRenderer
{
    private Main $plugin;
    private ImageProcessor $imageProcessor;

    /** @var array<int, array<int, int>> 缓存地图ID -> 像素数据 */
    private array $mapCache = [];

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $this->imageProcessor = new ImageProcessor();
    }

    public function renderScreen(AdScreen $screen): bool
    {
        $imagePath = $this->plugin->getDataFolder() . "images/" . $screen->getImagePath();
        if (!file_exists($imagePath)) {
            $this->plugin->getLogger()->error("图片不存在: " . $imagePath);
            return false;
        }

        $isGif = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)) === "gif";

        if ($isGif) {
            return $this->renderGif($screen, $imagePath);
        } else {
            return $this->renderStatic($screen, $imagePath);
        }
    }

    private function renderStatic(AdScreen $screen, string $imagePath): bool
    {
        $width = $screen->getWidth() * 128;
        $height = $screen->getHeight() * 128;

        $pixels = $this->imageProcessor->loadAndResize($imagePath, $width, $height);
        if ($pixels === null) {
            return false;
        }

        $mapIds = $screen->getMapIds();
        $index = 0;

        for ($row = 0; $row < $screen->getHeight(); $row++) {
            for ($col = 0; $col < $screen->getWidth(); $col++) {
                $mapId = $mapIds[$index++];
                $mapPixels = $this->extractMapSection($pixels, $col, $row, $width);
                $this->mapCache[$mapId][0] = $mapPixels;
                $screen->setFrameCount(1);
            }
        }

        $this->plugin->getLogger()->info("§a已渲染静态图: " . $screen->getId());
        return true;
    }

    private function renderGif(AdScreen $screen, string $imagePath): bool
    {
        $width = $screen->getWidth() * 128;
        $height = $screen->getHeight() * 128;

        $frames = $this->imageProcessor->loadGifFrames($imagePath, $width, $height);
        if (empty($frames)) {
            $this->plugin->getLogger()->error("GIF解析失败");
            return false;
        }

        $screen->setFrameCount(count($frames));
        $mapIds = $screen->getMapIds();

        foreach ($frames as $frameIndex => $pixels) {
            $index = 0;
            for ($row = 0; $row < $screen->getHeight(); $row++) {
                for ($col = 0; $col < $screen->getWidth(); $col++) {
                    $mapId = $mapIds[$index++];
                    $mapPixels = $this->extractMapSection($pixels, $col, $row, $width);
                    $this->mapCache[$mapId][$frameIndex] = $mapPixels;
                }
            }
        }

        $this->plugin->getLogger()->info("§a已渲染GIF: " . $screen->getId() . " (" . count($frames) . " 帧)");
        return true;
    }

    /**
     * 从完整图像中提取一块 128x128 的地图区域
     */
    private function extractMapSection(array $pixels, int $col, int $row, int $totalWidth): array
    {
        $section = [];
        for ($y = 0; $y < 128; $y++) {
            for ($x = 0; $x < 128; $x++) {
                $srcX = $col * 128 + $x;
                $srcY = $row * 128 + $y;
                $srcIndex = $srcY * $totalWidth + $srcX;
                $section[] = $pixels[$srcIndex] ?? 0;
            }
        }
        return $section;
    }

    /**
     * 获取指定地图ID和帧的像素数据
     */
    public function getMapData(int $mapId, int $frame): ?array
    {
        return $this->mapCache[$mapId][$frame] ?? null;
    }

    public function clearCache(): void
    {
        $this->mapCache = [];
    }
}
