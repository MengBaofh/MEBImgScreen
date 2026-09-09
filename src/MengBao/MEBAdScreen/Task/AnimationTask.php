<?php

declare(strict_types=1);

namespace MengBao\MEBAdScreen\Task;

use MengBao\MEBAdScreen\Main;
use MengBao\MEBAdScreen\Manager\ScreenManager;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\utils\BinaryStream;
use pocketmine\scheduler\Task;

class AnimationTask extends Task
{
    private Main $plugin;
    private ScreenManager $manager;

    public function __construct(Main $plugin, ScreenManager $manager)
    {
        $this->plugin = $plugin;
        $this->manager = $manager;
    }

    public function onRun(): void
    {
        $performanceMode = $this->plugin->getPluginConfig()->getNested("性能模式.启用", true);
        $playerThreshold = (int) $this->plugin->getPluginConfig()->getNested("性能模式.玩家少于N人时全速", 5);
        $playerCount = count($this->plugin->getServer()->getOnlinePlayers());

        $shouldSkip = false;
        if ($performanceMode && $playerCount > $playerThreshold) {
            static $skipCounter = 0;
            $shouldSkip = (++$skipCounter % 2) === 1;
        }

        foreach ($this->manager->getAllScreens() as $screen) {
            if (!$screen->isAnimated()) {
                continue;
            }

            $viewers = $screen->getViewers();
            if (empty($viewers)) {
                continue;
            }

            if ($shouldSkip) {
                continue;
            }

            $screen->nextFrame();
            $this->sendMapPackets($screen);
        }
    }

    private function sendMapPackets($screen): void
    {
        $viewers = $screen->getViewers();
        $currentFrame = $screen->getCurrentFrame();
        $mapRenderer = $this->manager->getMapRenderer();

        foreach ($screen->getMapIds() as $mapId) {
            $pixels = $mapRenderer->getMapData($mapId, $currentFrame);
            if ($pixels === null) {
                continue;
            }

            $pk = new ClientboundMapItemDataPacket();
            $pk->mapId = $mapId;
            $pk->scale = 0;
            $pk->width = 128;
            $pk->height = 128;
            $pk->dimensionId = 0;
            $pk->colors = $pixels;

            foreach ($viewers as $player) {
                $player->getNetworkSession()->sendDataPacket($pk);
            }
        }
    }
}
