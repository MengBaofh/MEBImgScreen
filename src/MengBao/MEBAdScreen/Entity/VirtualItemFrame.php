<?php

declare(strict_types=1);

namespace MengBao\MEBAdScreen\Entity;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;

/**
 * 虚拟物品展示框实体（仅用于数据包，不实际存在于世界）
 */
class VirtualItemFrame
{
    private int $entityId;
    private Vector3 $position;
    private int $facing;
    private int $mapId;

    public function __construct(int $entityId, Vector3 $position, int $facing, int $mapId)
    {
        $this->entityId = $entityId;
        $this->position = $position;
        $this->facing = $facing;
        $this->mapId = $mapId;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function getPosition(): Vector3
    {
        return $this->position;
    }

    public function getFacing(): int
    {
        return $this->facing;
    }

    public function getMapId(): int
    {
        return $this->mapId;
    }

    /**
     * 向玩家发送生成数据包
     */
    public function sendSpawnPacket(Player $player): void
    {
        // 注意：基岩版中物品展示框是实体
        // 这里需要使用 AddActorPacket 生成虚拟实体
        // 由于 PM API 限制，这部分需要直接操作网络数据包

        // TODO: 实现完整的物品展示框生成包
        // 当前简化方案：直接发送地图数据包，不显示展示框边框
    }

    /**
     * 向玩家发送删除数据包
     */
    public function sendDespawnPacket(Player $player): void
    {
        // TODO: 发送 RemoveActorPacket
    }
}
