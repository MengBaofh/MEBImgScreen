<?php

declare(strict_types=1);

namespace MengBao\MEBAdScreen;

use MengBao\MEBAdScreen\Command\AdScreenCommand;
use MengBao\MEBAdScreen\Listener\EventListener;
use MengBao\MEBAdScreen\Manager\ScreenManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase
{
    private const VERSION = "1.0.0";

    private Config $config;
    private ScreenManager $screenManager;

    public function onLoad(): void
    {
    }

    public function onEnable(): void
    {
        @mkdir($this->getDataFolder(), 0777, true);
        @mkdir($this->getDataFolder() . "screens/", 0777, true);
        @mkdir($this->getDataFolder() . "images/", 0777, true);
        @mkdir($this->getDataFolder() . "cache/", 0777, true);

        $this->loadConfig();
        $this->screenManager = new ScreenManager($this);

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getCommandMap()->register("adscreen", new AdScreenCommand($this));

        $this->getLogger()->info("§aMEBAdScreen v" . self::VERSION . " 已启用");
        $this->getLogger()->info("§e将图片/GIF放入 plugins/MEBAdScreen/images/ 目录");
    }

    public function onDisable(): void
    {
        if (isset($this->screenManager)) {
            $this->screenManager->shutdown();
        }
    }

    private function loadConfig(): void
    {
        $this->config = new Config(
            $this->getDataFolder() . "config.yml",
            Config::YAML,
            [
                "版本" => self::VERSION,
                "最大可视距离" => 64,
                "距离检测间隔(s)" => 1.0,
                "GIF帧间隔(tick)" => 4,
                "异步渲染" => true,
                "最大屏幕数" => 50,
                "地图起始ID" => 1000,
                "性能模式" => [
                    "启用" => true,
                    "玩家少于N人时全速" => 5,
                    "玩家多时降低帧率" => true
                ]
            ]
        );
    }

    public function getScreenManager(): ScreenManager
    {
        return $this->screenManager;
    }

    public function getPluginConfig(): Config
    {
        return $this->config;
    }
}
