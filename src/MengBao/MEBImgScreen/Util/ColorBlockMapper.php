<?php

declare(strict_types=1);

namespace MengBao\MEBImgScreen\Util;

use pocketmine\block\Block;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;

/**
 * 将 RGB 颜色映射到 Minecraft 方块
 */
class ColorBlockMapper
{
    /** @var array<int, array{block: Block, r: int, g: int, b: int}> */
    private array $colorPalette = [];

    public function __construct()
    {
        $this->buildColorPalette();
    }

    /**
     * 构建颜色调色板（使用混凝土、羊毛、玻璃）
     */
    private function buildColorPalette(): void
    {
        // 基础 RGB 值来自 Minecraft Wiki
        $dyeColors = [
            ['color' => DyeColor::WHITE(), 'rgb' => [235, 235, 235]],
            ['color' => DyeColor::ORANGE(), 'rgb' => [216, 127, 51]],
            ['color' => DyeColor::MAGENTA(), 'rgb' => [178, 76, 216]],
            ['color' => DyeColor::LIGHT_BLUE(), 'rgb' => [102, 153, 216]],
            ['color' => DyeColor::YELLOW(), 'rgb' => [229, 229, 51]],
            ['color' => DyeColor::LIME(), 'rgb' => [127, 204, 25]],
            ['color' => DyeColor::PINK(), 'rgb' => [242, 127, 165]],
            ['color' => DyeColor::GRAY(), 'rgb' => [76, 76, 76]],
            ['color' => DyeColor::LIGHT_GRAY(), 'rgb' => [153, 153, 153]],
            ['color' => DyeColor::CYAN(), 'rgb' => [76, 127, 153]],
            ['color' => DyeColor::PURPLE(), 'rgb' => [127, 63, 178]],
            ['color' => DyeColor::BLUE(), 'rgb' => [51, 76, 178]],
            ['color' => DyeColor::BROWN(), 'rgb' => [102, 76, 51]],
            ['color' => DyeColor::GREEN(), 'rgb' => [102, 127, 51]],
            ['color' => DyeColor::RED(), 'rgb' => [153, 51, 51]],
            ['color' => DyeColor::BLACK(), 'rgb' => [25, 25, 25]],
        ];

        foreach ($dyeColors as $entry) {
            $dye = $entry['color'];
            $rgb = $entry['rgb'];

            // 混凝土（最鲜艳）
            $this->colorPalette[] = [
                'block' => VanillaBlocks::CONCRETE()->setColor($dye),
                'r' => $rgb[0],
                'g' => $rgb[1],
                'b' => $rgb[2],
            ];

            // 羊毛（稍微柔和）
            $woolRgb = [
                (int)($rgb[0] * 0.9),
                (int)($rgb[1] * 0.9),
                (int)($rgb[2] * 0.9),
            ];
            $this->colorPalette[] = [
                'block' => VanillaBlocks::WOOL()->setColor($dye),
                'r' => $woolRgb[0],
                'g' => $woolRgb[1],
                'b' => $woolRgb[2],
            ];
        }

        // 添加一些非染色方块以扩展颜色范围
        $extraBlocks = [
            [VanillaBlocks::STONE(), 125, 125, 125],
            [VanillaBlocks::DIRT(), 134, 96, 67],
            [VanillaBlocks::GRASS(), 127, 178, 56],
            [VanillaBlocks::SAND(), 220, 212, 160],
            [VanillaBlocks::GRAVEL(), 132, 130, 127],
            [VanillaBlocks::GOLD(), 250, 238, 77],
            [VanillaBlocks::IRON(), 216, 216, 216],
            [VanillaBlocks::DIAMOND(), 92, 219, 213],
            [VanillaBlocks::EMERALD(), 0, 217, 58],
            [VanillaBlocks::LAPIS_LAZULI(), 31, 67, 140],
            [VanillaBlocks::OBSIDIAN(), 20, 18, 29],
            [VanillaBlocks::NETHERRACK(), 112, 47, 47],
            [VanillaBlocks::GLOWSTONE(), 245, 220, 150],
        ];

        foreach ($extraBlocks as $data) {
            $this->colorPalette[] = [
                'block' => $data[0],
                'r' => $data[1],
                'g' => $data[2],
                'b' => $data[3],
            ];
        }
    }

    /**
     * 找到最接近目标颜色的方块
     */
    public function getClosestBlock(int $r, int $g, int $b): Block
    {
        $minDistance = PHP_INT_MAX;
        $closestBlock = VanillaBlocks::STONE();

        foreach ($this->colorPalette as $entry) {
            // 使用欧几里得距离计算颜色差异
            $distance = pow($r - $entry['r'], 2) +
                       pow($g - $entry['g'], 2) +
                       pow($b - $entry['b'], 2);

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestBlock = $entry['block'];
            }
        }

        return $closestBlock;
    }

    /**
     * 获取调色板大小
     */
    public function getPaletteSize(): int
    {
        return count($this->colorPalette);
    }
}

