<?php

declare(strict_types=1);

namespace MengBao\MEBAdScreen\Util;

use MengBao\MEBAdScreen\Main;
use MengBao\MEBAdScreen\Struct\AdScreen;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\world\World;

/**
 * 使用彩色方块构建图像屏幕
 */
class BlockScreenBuilder
{
    private ColorBlockMapper $colorMapper;
    private Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $this->colorMapper = new ColorBlockMapper();
    }

    /**
     * 构建方块像素屏幕
     * @param array $imagePixels 图片像素数据 [r, g, b]
     */
    public function buildScreen(AdScreen $screen, array $imagePixels): bool
    {
        $world = $screen->getWorld();
        $basePos = $screen->getPosition();
        $width = $screen->getWidth();
        $height = $screen->getHeight();
        $direction = strtolower($screen->getDirection());

        $pixelIndex = 0;

        // 从上到下（Y 从大到小），从左到右（X 或 Z 根据方向）
        for ($row = 0; $row < $height; $row++) {
            for ($col = 0; $col < $width; $col++) {
                if ($pixelIndex >= count($imagePixels)) {
                    $this->plugin->getLogger()->warning("Image pixels exhausted");
                    break 2;
                }

                $pixel = $imagePixels[$pixelIndex++];
                $block = $this->colorMapper->getClosestBlock(
                    $pixel['r'],
                    $pixel['g'],
                    $pixel['b']
                );

                // 计算实际方块位置（考虑方向和上下翻转）
                $pos = $this->calculateBlockPosition($basePos, $col, $row, $width, $height, $direction);
                $world->setBlock($pos, $block);
            }
        }

        $screenId = $screen->getId();
        $this->plugin->getLogger()->info("Built block screen {$screenId}: {$width}x{$height}");
        return true;
    }

    /**
     * 根据方向计算方块位置
     */
    private function calculateBlockPosition(
        \pocketmine\math\Vector3 $basePos,
        int $col,
        int $row,
        int $width,
        int $height,
        string $direction
    ): \pocketmine\math\Vector3 {
        // 图片坐标: row=0 是顶部，需要映射到 Minecraft 的 Y 坐标
        // Minecraft: Y 越大越高，所以需要翻转
        $y = $basePos->y + ($height - 1 - $row); // 翻转 Y 轴

        switch ($direction) {
            case "north": // 玩家在南边看，屏幕朝北，X 向右，Z 不变
                return new \pocketmine\math\Vector3(
                    $basePos->x + $col,
                    $y,
                    $basePos->z
                );

            case "south": // 玩家在北边看，屏幕朝南，X 向左（需要翻转）
                return new \pocketmine\math\Vector3(
                    $basePos->x + ($width - 1 - $col),
                    $y,
                    $basePos->z
                );

            case "east": // 玩家在西边看，屏幕朝东，Z 向右
                return new \pocketmine\math\Vector3(
                    $basePos->x,
                    $y,
                    $basePos->z + $col
                );

            case "west": // 玩家在东边看，屏幕朝西，Z 向左（需要翻转）
                return new \pocketmine\math\Vector3(
                    $basePos->x,
                    $y,
                    $basePos->z + ($width - 1 - $col)
                );

            default:
                return new \pocketmine\math\Vector3($basePos->x + $col, $y, $basePos->z);
        }
    }

    /**
     * 移除屏幕
     */
    public function removeScreen(AdScreen $screen): void
    {
        $world = $screen->getWorld();
        $basePos = $screen->getPosition();
        $width = $screen->getWidth();
        $height = $screen->getHeight();
        $direction = strtolower($screen->getDirection());

        for ($row = 0; $row < $height; $row++) {
            for ($col = 0; $col < $width; $col++) {
                $pos = $this->calculateBlockPosition($basePos, $col, $row, $width, $height, $direction);
                $world->setBlock($pos, \pocketmine\block\VanillaBlocks::AIR());
            }
        }
    }
}
