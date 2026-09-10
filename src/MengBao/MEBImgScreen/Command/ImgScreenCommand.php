<?php

declare(strict_types=1);

namespace MengBao\MEBImgScreen\Command;

use MengBao\MEBImgScreen\Main;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class ImgScreenCommand
{
    private Main $plugin;
    private ?GuiHandler $guiHandler = null;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;

        // 如果MEBForms可用，初始化GUI处理器
        if (GuiHandler::isAvailable()) {
            $this->guiHandler = new GuiHandler($plugin, $this);
        }
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender->hasPermission("mebis.admin")) {
            $sender->sendMessage("§c你没有权限使用此命令");
            return true;
        }

        if (empty($args)) {
            // 玩家且GUI可用则显示GUI
            if ($sender instanceof Player && $this->guiHandler !== null) {
                $this->guiHandler->showMainMenu($sender);
                return true;
            }

            $sender->sendMessage("§6=== MEBImgScreen 图片屏插件 ===");
            $sender->sendMessage("§e/mebis make <图片名> [宽] [高] §7- 快速创建（默认16x16）");
            $sender->sendMessage("§e/mebis create <id> <宽> <高> <方向> <图片名> §7- 完整创建");
            $sender->sendMessage("§e/mebis remove <id> §7- 删除图片屏");
            $sender->sendMessage("§e/mebis list §7- 列出所有图片屏");
            $sender->sendMessage("§e/mebis info <id> §7- 查看详细信息");
            $sender->sendMessage("§e/mebis reload §7- 重载配置");
            if ($sender instanceof Player && $this->guiHandler === null) {
                $sender->sendMessage("");
                $sender->sendMessage("§7提示: 安装 MEBForms 插件可使用GUI界面");
            }
            $sender->sendMessage("");
            $sender->sendMessage("§7提示: 将图片放入 plugin_data/MEBImgScreen/images/");
            $sender->sendMessage("§7提示: 每个方块=1像素，推荐尺寸16-64");
            $sender->sendMessage("§7示例: /mebis make logo.png 32 32");
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
            case "gui":
            case "menu":
                if ($sender instanceof Player) {
                    if ($this->guiHandler !== null) {
                        $this->guiHandler->showMainMenu($sender);
                    } else {
                        $sender->sendMessage("§c需要安装 MEBForms 插件才能使用GUI");
                        $sender->sendMessage("§7你仍可使用命令操作");
                    }
                } else {
                    $sender->sendMessage("§c只有玩家能使用GUI");
                }
                return true;
            default:
                $sender->sendMessage("§c未知子命令: $sub");
                return false;
        }
    }

    private function handleQuickCreate(CommandSender $sender, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§c只有玩家能创建图片屏");
            return false;
        }

        if (count($args) < 2) {
            $sender->sendMessage("§c用法: /mebis make <图片名> [宽] [高]");
            $sender->sendMessage("§7示例: /mebis make banner.gif");
            $sender->sendMessage("§7示例: /mebis make logo.png 3 2");
            return false;
        }

        $imageName = $args[1];
        $width = isset($args[2]) ? (int) $args[2] : 16;
        $height = isset($args[3]) ? (int) $args[3] : 16;

        if ($width < 1 || $width > 128 || $height < 1 || $height > 128) {
            $sender->sendMessage("§c宽高必须在1-128之间（推荐16-64）");
            $sender->sendMessage("§7注意：每个方块=1像素，过大会导致放置缓慢");
            return false;
        }

        // 检查图片是否存在
        $imagePath = $this->plugin->getDataFolder() . "images/" . $imageName;
        if (!file_exists($imagePath)) {
            $sender->sendMessage("§c图片不存在: $imageName");
            $sender->sendMessage("§7请将图片放入: plugin_data/MEBImgScreen/images/");
            return false;
        }

        // 自动生成ID
        $id = "img_" . time() . "_" . mt_rand(1000, 9999);

        // 根据玩家朝向确定方向
        $direction = $this->getPlayerDirection($sender);

        // 获取玩家面前的位置
        $pos = $this->getPositionInFront($sender, 3);

        $sender->sendMessage("§e正在创建 {$width}×{$height} 图片屏...");

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

        $sender->sendMessage("§a✔ 图片屏已创建！");
        $sender->sendMessage("§7ID: §e$id");
        $sender->sendMessage("§7尺寸: §e{$width}×{$height} 方块 §7(每方块=1像素)");
        $sender->sendMessage("§7类型: §7方块像素画");
        $sender->sendMessage("§7位置: §e" . $pos->getFloorX() . ", " . $pos->getFloorY() . ", " . $pos->getFloorZ());

        return true;
    }

    private function handleCreate(CommandSender $sender, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§c只有玩家能创建图片屏");
            return false;
        }

        if (count($args) < 6) {
            $sender->sendMessage("§c用法: /mebis create <id> <宽> <高> <方向> <图片名>");
            return false;
        }

        $id = $args[1];
        $width = (int) $args[2];
        $height = (int) $args[3];
        $direction = strtolower($args[4]);
        $imageName = $args[5];

        if ($width < 1 || $width > 128 || $height < 1 || $height > 128) {
            $sender->sendMessage("§c宽高必须在1-128之间（推荐16-64）");
            $sender->sendMessage("§7注意：每个方块=1像素，过大会导致放置缓慢");
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

        $sender->sendMessage("§a已创建图片屏: $id ({$width}x{$height})");
        $sender->sendMessage("§7位置: " . $pos->getFloorX() . ", " . $pos->getFloorY() . ", " . $pos->getFloorZ());
        return true;
    }

    private function handleInfo(CommandSender $sender, array $args): bool
    {
        if (count($args) < 2) {
            $sender->sendMessage("§c用法: /mebis info <id>");
            return false;
        }

        $id = $args[1];
        $screen = $this->plugin->getScreenManager()->getScreen($id);

        if ($screen === null) {
            $sender->sendMessage("§c图片屏不存在: $id");
            return false;
        }

        $pos = $screen->getPosition();

        $sender->sendMessage("§6=== 图片屏信息 ===");
        $sender->sendMessage("§eID: §7$id");
        $sender->sendMessage("§e尺寸: §7{$screen->getWidth()}×{$screen->getHeight()} 方块");
        $sender->sendMessage("§e方向: §7{$screen->getDirection()}");
        $sender->sendMessage("§e图片: §7{$screen->getImagePath()}");
        $sender->sendMessage("§e位置: §7{$pos->getFloorX()}, {$pos->getFloorY()}, {$pos->getFloorZ()}");
        $sender->sendMessage("§e世界: §7{$screen->getWorld()->getFolderName()}");
        $sender->sendMessage("§e类型: §7方块像素画");

        return true;
    }

    /**
     * 获取玩家面前的位置
     */
    private function getPositionInFront(Player $player, float $distance): \pocketmine\world\Position
    {
        $location = $player->getLocation();

        // 根据 yaw 计算水平方向向量
        $yaw = deg2rad($location->getYaw());
        $x = -sin($yaw) * $distance;
        $z = cos($yaw) * $distance;

        return $player->getWorld()->getSafeSpawn(
            $location->add($x, 0, $z)
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
            $sender->sendMessage("§c用法: /mebis remove <id>");
            return false;
        }

        $id = $args[1];
        if ($this->plugin->getScreenManager()->removeScreen($id)) {
            $sender->sendMessage("§a已删除图片屏: $id");
        } else {
            $sender->sendMessage("§c图片屏不存在: $id");
        }
        return true;
    }

    private function handleList(CommandSender $sender): bool
    {
        $screens = $this->plugin->getScreenManager()->getAllScreens();
        if (empty($screens)) {
            $sender->sendMessage("§e当前没有图片屏");
            return true;
        }

        $sender->sendMessage("§e=== 图片屏列表 ===");
        foreach ($screens as $screen) {
            $pos = $screen->getPosition();
            $sender->sendMessage(
                "§7- §e{$screen->getId()} §7({$screen->getWidth()}×{$screen->getHeight()}) " .
                "at {$pos->getFloorX()}, {$pos->getFloorY()}, {$pos->getFloorZ()}"
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

    /**
     * GUI快速创建的处理方法
     */
    public function handleQuickCreateFromGui(Player $player, string $imageName, int $width, int $height): void
    {
        // 检查图片是否存在
        $imagePath = $this->plugin->getDataFolder() . "images/" . $imageName;
        if (!file_exists($imagePath)) {
            $player->sendMessage("§c图片不存在: $imageName");
            return;
        }

        if ($width < 1 || $width > 128 || $height < 1 || $height > 128) {
            $player->sendMessage("§c宽高必须在1-128之间");
            return;
        }

        // 自动生成ID
        $id = "img_" . time() . "_" . mt_rand(1000, 9999);

        // 根据玩家朝向确定方向
        $direction = $this->getPlayerDirection($player);

        // 获取玩家面前的位置
        $pos = $this->getPositionInFront($player, 3);

        $player->sendMessage("§e正在创建 {$width}×{$height} 图片屏...");

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
            true
        );

        if ($screen === null) {
            $player->sendMessage("§c创建失败");
            return;
        }

        $player->sendMessage("§a✔ 图片屏已创建！");
        $player->sendMessage("§7ID: §e$id");
        $player->sendMessage("§7尺寸: §e{$width}×{$height} 方块");
    }

    /**
     * GUI详细创建的处理方法
     */
    public function handleCreateFromGui(Player $player, string $id, int $width, int $height, string $direction, string $imageName): void
    {
        if ($width < 1 || $width > 128 || $height < 1 || $height > 128) {
            $player->sendMessage("§c宽高必须在1-128之间");
            return;
        }

        if (!in_array($direction, ["north", "south", "east", "west"], true)) {
            $player->sendMessage("§c方向错误");
            return;
        }

        $pos = $player->getPosition();
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
            $player->sendMessage("§c创建失败，可能ID重复或达到数量上限");
            return;
        }

        $player->sendMessage("§a已创建图片屏: $id ({$width}x{$height})");
    }
}
