<?php

declare(strict_types=1);

namespace MengBao\MEBAdScreen\Command;

use MengBao\MEBAdScreen\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class AdScreenCommand extends Command
{
    private Main $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct(
            "adscreen",
            "管理广告屏",
            "/adscreen <create|remove|list|reload>",
            ["ads"]
        );
        $this->setPermission("adscreen.admin");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$this->testPermission($sender)) {
            return false;
        }

        if (empty($args)) {
            $sender->sendMessage("§6=== MEBAdScreen 广告屏插件 ===");
            $sender->sendMessage("§e/adscreen make <图片名> [宽] [高] §7- 快速创建（默认2x2）");
            $sender->sendMessage("§e/adscreen create <id> <宽> <高> <方向> <图片名> §7- 完整创建");
            $sender->sendMessage("§e/adscreen remove <id> §7- 删除广告屏");
            $sender->sendMessage("§e/adscreen list §7- 列出所有广告屏");
            $sender->sendMessage("§e/adscreen info <id> §7- 查看详细信息");
            $sender->sendMessage("§e/adscreen reload §7- 重载配置");
            $sender->sendMessage("");
            $sender->sendMessage("§7提示: 将图片放入 plugins/MEBAdScreen/images/");
            $sender->sendMessage("§7示例: /adscreen make logo.png 3 2");
            return true;
        }

        $sub = strtolower($args[0]);

        switch ($sub) {
            case "make":
            case "quick":
                return $this->handleQuickCreate($sender, $args);
            case "create":
                return $this->handleCreate($sender, $args);
            case "remove":
            case "delete":
            case "rm":
                return $this->handleRemove($sender, $args);
            case "list":
            case "ls":
                return $this->handleList($sender);
            case "info":
                return $this->handleInfo($sender, $args);
            case "reload":
                return $this->handleReload($sender);
            default:
                $sender->sendMessage("§c未知子命令: $sub");
                return false;
        }
    }

    private function handleQuickCreate(CommandSender $sender, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§c只有玩家能创建广告屏");
            return false;
        }

        if (count($args) < 2) {
            $sender->sendMessage("§c用法: /adscreen make <图片名> [宽] [高]");
            $sender->sendMessage("§7示例: /adscreen make banner.gif");
            $sender->sendMessage("§7示例: /adscreen make logo.png 3 2");
            return false;
        }

        $imageName = $args[1];
        $width = isset($args[2]) ? (int) $args[2] : 2;
        $height = isset($args[3]) ? (int) $args[3] : 2;

        if ($width < 1 || $width > 4 || $height < 1 || $height > 4) {
            $sender->sendMessage("§c宽高必须在1-4之间");
            return false;
        }

        // 检查图片是否存在
        $imagePath = $this->plugin->getDataFolder() . "images/" . $imageName;
        if (!file_exists($imagePath)) {
            $sender->sendMessage("§c图片不存在: $imageName");
            $sender->sendMessage("§7请将图片放入: plugins/MEBAdScreen/images/");
            return false;
        }

        // 自动生成ID
        $id = "ad_" . time() . "_" . mt_rand(1000, 9999);

        // 根据玩家朝向确定方向
        $direction = $this->getPlayerDirection($sender);

        // 获取玩家面前的位置
        $pos = $this->getPositionInFront($sender, 3);

        $sender->sendMessage("§e正在创建 {$width}×{$height} 广告屏...");

        $screen = $this->plugin->getScreenManager()->createScreen(
            $id,
            $pos->getWorld(),
            (int) $pos->x,
            (int) $pos->y,
            (int) $pos->z,
            $direction,
            $width,
            $height,
            $imageName,
            true  // 自动构建
        );

        if ($screen === null) {
            $sender->sendMessage("§c创建失败，可能达到数量上限或图片加载失败");
            return false;
        }

        $isGif = $screen->isAnimated();
        $type = $isGif ? "§a动画 ({$screen->getFrameCount()}帧)" : "§7静态";

        $sender->sendMessage("§a✔ 广告屏已创建！");
        $sender->sendMessage("§7ID: §e$id");
        $sender->sendMessage("§7尺寸: §e{$width}×{$height} §7({$screen->getWidth() * 128}×{$screen->getHeight() * 128}px)");
        $sender->sendMessage("§7类型: $type");
        $sender->sendMessage("§7位置: §e" . $pos->getFloorX() . ", " . $pos->getFloorY() . ", " . $pos->getFloorZ());

        return true;
    }

    private function handleCreate(CommandSender $sender, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§c只有玩家能创建广告屏");
            return false;
        }

        if (count($args) < 6) {
            $sender->sendMessage("§c用法: /adscreen create <id> <宽> <高> <方向> <图片名>");
            return false;
        }

        $id = $args[1];
        $width = (int) $args[2];
        $height = (int) $args[3];
        $direction = strtolower($args[4]);
        $imageName = $args[5];

        if ($width < 1 || $width > 4 || $height < 1 || $height > 4) {
            $sender->sendMessage("§c宽高必须在1-4之间");
            return false;
        }

        if (!in_array($direction, ["north", "south", "east", "west"], true)) {
            $sender->sendMessage("§c方向必须是: north, south, east, west");
            return false;
        }

        $pos = $sender->getPosition();
        $screen = $this->plugin->getScreenManager()->createScreen(
            $id,
            $pos->getWorld(),
            (int) $pos->x,
            (int) $pos->y,
            (int) $pos->z,
            $direction,
            $width,
            $height,
            $imageName
        );

        if ($screen === null) {
            $sender->sendMessage("§c创建失败，可能ID重复、达到数量上限或图片加载失败");
            return false;
        }

        $sender->sendMessage("§a已创建广告屏: $id ({$width}x{$height})");
        $sender->sendMessage("§7位置: " . $pos->getFloorX() . ", " . $pos->getFloorY() . ", " . $pos->getFloorZ());
        if ($screen->isAnimated()) {
            $sender->sendMessage("§7动画帧数: " . $screen->getFrameCount());
        }
        return true;
    }

    private function handleInfo(CommandSender $sender, array $args): bool
    {
        if (count($args) < 2) {
            $sender->sendMessage("§c用法: /adscreen info <id>");
            return false;
        }

        $id = $args[1];
        $screen = $this->plugin->getScreenManager()->getScreen($id);

        if ($screen === null) {
            $sender->sendMessage("§c广告屏不存在: $id");
            return false;
        }

        $pos = $screen->getPosition();
        $viewers = count($screen->getViewers());

        $sender->sendMessage("§6=== 广告屏信息 ===");
        $sender->sendMessage("§eID: §7$id");
        $sender->sendMessage("§e尺寸: §7{$screen->getWidth()}×{$screen->getHeight()} §8({$screen->getWidth() * 128}×{$screen->getHeight() * 128}px)");
        $sender->sendMessage("§e方向: §7{$screen->getDirection()}");
        $sender->sendMessage("§e图片: §7{$screen->getImagePath()}");
        $sender->sendMessage("§e位置: §7{$pos->getFloorX()}, {$pos->getFloorY()}, {$pos->getFloorZ()}");
        $sender->sendMessage("§e世界: §7{$screen->getWorld()->getFolderName()}");

        if ($screen->isAnimated()) {
            $sender->sendMessage("§e类型: §a动画");
            $sender->sendMessage("§e总帧数: §7{$screen->getFrameCount()}");
            $sender->sendMessage("§e当前帧: §7{$screen->getCurrentFrame()}");
        } else {
            $sender->sendMessage("§e类型: §7静态图片");
        }

        $sender->sendMessage("§e地图ID: §7" . implode(", ", array_slice($screen->getMapIds(), 0, 5)) . ($screen->getWidth() * $screen->getHeight() > 5 ? "..." : ""));
        $sender->sendMessage("§e观看者: §7$viewers 人");

        return true;
    }

    /**
     * 获取玩家面前的位置
     */
    private function getPositionInFront(Player $player, float $distance): \pocketmine\world\Position
    {
        $location = $player->getLocation();
        $directionVector = $location->getDirectionVector()->normalize()->multiply($distance);

        return $player->getWorld()->getSafeSpawn(
            $location->add($directionVector->x, 0, $directionVector->z)
        );
    }

    /**
     * 根据玩家朝向获取方向字符串
     */
    private function getPlayerDirection(Player $player): string
    {
        $yaw = $player->getLocation()->getYaw();
        $yaw = fmod($yaw, 360);
        if ($yaw < 0) {
            $yaw += 360;
        }

        // 基岩版朝向映射
        if ($yaw >= 315 || $yaw < 45) {
            return "south";  // 玩家面向南，屏幕朝南（玩家在北边看）
        } elseif ($yaw >= 45 && $yaw < 135) {
            return "west";
        } elseif ($yaw >= 135 && $yaw < 225) {
            return "north";
        } else {
            return "east";
        }
    }

    private function handleRemove(CommandSender $sender, array $args): bool
    {
        if (count($args) < 2) {
            $sender->sendMessage("§c用法: /adscreen remove <id>");
            return false;
        }

        $id = $args[1];
        if ($this->plugin->getScreenManager()->removeScreen($id)) {
            $sender->sendMessage("§a已删除广告屏: $id");
        } else {
            $sender->sendMessage("§c广告屏不存在: $id");
        }
        return true;
    }

    private function handleList(CommandSender $sender): bool
    {
        $screens = $this->plugin->getScreenManager()->getAllScreens();
        if (empty($screens)) {
            $sender->sendMessage("§e当前没有广告屏");
            return true;
        }

        $sender->sendMessage("§e=== 广告屏列表 ===");
        foreach ($screens as $screen) {
            $pos = $screen->getPosition();
            $animated = $screen->isAnimated() ? " §a(动画 {$screen->getFrameCount()}帧)" : " §7(静态)";
            $sender->sendMessage(
                "§7- §e{$screen->getId()} §7({$screen->getWidth()}x{$screen->getHeight()}) " .
                "at {$pos->getFloorX()}, {$pos->getFloorY()}, {$pos->getFloorZ()}" . $animated
            );
        }
        return true;
    }

    private function handleReload(CommandSender $sender): bool
    {
        // TODO: 实现重载逻辑
        $sender->sendMessage("§a配置已重载");
        return true;
    }
}
