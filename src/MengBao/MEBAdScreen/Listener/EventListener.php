<?php

declare(strict_types=1);

namespace MengBao\MEBAdScreen\Listener;

use MengBao\MEBAdScreen\Main;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

class EventListener implements Listener
{
    private Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $this->plugin->getScreenManager()->updatePlayerVisibility($player);
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        foreach ($this->plugin->getScreenManager()->getAllScreens() as $screen) {
            $screen->removeViewer($player);
        }
    }
}
