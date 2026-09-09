<?php

declare(strict_types=1);

namespace MengBao\MEBAdScreen;

use MengBao\MEBAdScreen\Command\ImgScreenCommand;
use MengBao\MEBAdScreen\Manager\ScreenManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase
{
    private const VERSION = "1.0.0";

    private Config $config;
    private ScreenManager $screenManager;
    private ImgScreenCommand $commandHandler;

    public function onLoad(): void
    {
    }

    public function onEnable(): void
    {
        @mkdir($this->getDataFolder(), 0777, true);
        @mkdir($this->getDataFolder() . "screens/", 0777, true);
        @mkdir($this->getDataFolder() . "images/", 0777, true);

        $this->loadConfig();
        $this->screenManager = new ScreenManager($this);
        $this->commandHandler = new ImgScreenCommand($this);

        $this->getLogger()->info("§aMEBImgScreen v" . self::VERSION . " 已启用");
        $this->getLogger()->info("§e将图片放入 plugins/MEBAdScreen/images/ 目录");
    }

    public function onDisable(): void
    {
        if (isset($this->screenManager)) {
            $this->screenManager->shutdown();
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($command->getName() !== "imgscreen") {
            return false;
        }
        return $this->commandHandler->execute($sender, $label, $args);
    }

    private function loadConfig(): void
    {
        $this->config = new Config(
            $this->getDataFolder() . "config.yml",
            Config::YAML,
            [
                "版本" => self::VERSION,
                "最大屏幕数" => 50
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
