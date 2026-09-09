<?php

declare(strict_types=1);

namespace MengBao\MEBAdScreen\Task;

use MengBao\MEBAdScreen\Main;
use MengBao\MEBAdScreen\Manager\ScreenManager;
use pocketmine\scheduler\Task;

class PlayerDistanceCheckTask extends Task
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
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $this->manager->updatePlayerVisibility($player);
        }
    }
}
