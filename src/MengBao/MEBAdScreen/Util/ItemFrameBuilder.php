<?php

declare(strict_types=1);

namespace MengBao\MEBAdScreen\Util;

use MengBao\MEBAdScreen\Struct\AdScreen;
use pocketmine\block\Block;
use pocketmine\block\ItemFrame;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\FilledMap;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\world\World;

class ItemFrameBuilder
{
    /**
     * 在指定位置生成物品展示框网格
     */
    public function buildFrameGrid(AdScreen $screen): bool
    {
        $world = $screen->getWorld();
        $basePos = $screen->getPosition();
        $direction = $screen->getDirection();
        $width = $screen->getWidth();
        $height = $screen->getHeight();
        $mapIds = $screen->getMapIds();

        $facing = $this->directionToFacing($direction);
        if ($facing === null) {
            return false;
        }

        // 确保背板方块存在
        $this->placeBackingWall($world, $basePos, $width, $height, $facing);

        // 放置物品展示框和地图
        $mapIndex = 0;
        for ($row = 0; $row < $height; $row++) {
            for ($col = 0; $col < $width; $col++) {
                $framePos = $this->calculateFramePosition($basePos, $col, $row, $direction);
                $mapId = $mapIds[$mapIndex++];

                $this->placeItemFrame($world, $framePos, $facing, $mapId);
            }
        }

        return true;
    }

    /**
     * 放置背板墙（物品展示框需要依附在方块上）
     */
    private function placeBackingWall(World $world, Vector3 $basePos, int $width, int $height, int $facing): void
    {
        $backBlock = VanillaBlocks::BLACK_CONCRETE();

        for ($row = 0; $row < $height; $row++) {
            for ($col = 0; $col < $width; $col++) {
                $blockPos = $basePos->add($col, $row, 0);
                $world->setBlock($blockPos, $backBlock);
            }
        }
    }

    /**
     * 计算物品展示框的位置（在背板前方）
     */
    private function calculateFramePosition(Vector3 $basePos, int $col, int $row, string $direction): Vector3
    {
        $x = $basePos->x;
        $y = $basePos->y;
        $z = $basePos->z;

        switch ($direction) {
            case "north":  // 朝北：玩家在南边看，Z-
                return new Vector3($x + $col, $y + $row, $z);
            case "south":  // 朝南：玩家在北边看，Z+
                return new Vector3($x + $col, $y + $row, $z);
            case "west":   // 朝西：玩家在东边看，X-
                return new Vector3($x, $y + $row, $z + $col);
            case "east":   // 朝东：玩家在西边看，X+
                return new Vector3($x, $y + $row, $z + $col);
            default:
                return $basePos;
        }
    }

    /**
     * 放置单个物品展示框和地图
     */
    private function placeItemFrame(World $world, Vector3 $pos, int $facing, int $mapId): void
    {
        // 注意：PocketMine 中物品展示框是实体，不是方块
        // 我们需要使用实体系统来放置
        // 这里先占位，稍后完善实体生成

        // TODO: 生成 ItemFrameEntity
        // 目前方案：通过数据包模拟，不在世界实际放置实体
    }

    /**
     * 移除广告屏的所有物品展示框
     */
    public function removeFrameGrid(AdScreen $screen): void
    {
        $world = $screen->getWorld();
        $basePos = $screen->getPosition();
        $width = $screen->getWidth();
        $height = $screen->getHeight();

        // 移除背板
        for ($row = 0; $row < $height; $row++) {
            for ($col = 0; $col < $width; $col++) {
                $blockPos = $basePos->add($col, $row, 0);
                $world->setBlock($blockPos, VanillaBlocks::AIR());
            }
        }

        // TODO: 移除实体
    }

    private function directionToFacing(string $direction): ?int
    {
        return match(strtolower($direction)) {
            "north" => Facing::NORTH,
            "south" => Facing::SOUTH,
            "east" => Facing::EAST,
            "west" => Facing::WEST,
            "up" => Facing::UP,
            "down" => Facing::DOWN,
            default => null,
        };
    }

    /**
     * 根据朝向获取反方向（物品展示框朝向玩家）
     */
    private function getOppositeFacing(int $facing): int
    {
        return Facing::opposite($facing);
    }
}
