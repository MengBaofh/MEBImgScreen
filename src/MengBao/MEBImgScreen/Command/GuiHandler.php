<?php

declare(strict_types=1);

namespace MengBao\MEBImgScreen\Command;

use MengBao\MEBImgScreen\Main;
use pocketmine\player\Player;

/**
 * GUI操作（需要MEBForms插件）
 */
class GuiHandler
{
    private Main $plugin;
    private ImgScreenCommand $commandHandler;

    public function __construct(Main $plugin, ImgScreenCommand $commandHandler)
    {
        $this->plugin = $plugin;
        $this->commandHandler = $commandHandler;
    }

    /**
     * 检查MEBForms是否可用
     */
    public static function isAvailable(): bool
    {
        return class_exists('MengBao\MEBForms\SimpleForm') && class_exists('MengBao\MEBForms\CustomForm');
    }

    /**
     * 显示主菜单
     */
    public function showMainMenu(Player $player): void
    {
        $form = new \MengBao\MEBForms\SimpleForm(function (Player $player, $data) {
            if ($data === null) {
                return;
            }

            switch ($data) {
                case "make":
                    $this->showQuickCreateForm($player);
                    break;
                case "create":
                    $this->showCreateForm($player);
                    break;
                case "remove":
                    $this->showRemoveForm($player);
                    break;
                case "list":
                    $this->showListForm($player);
                    break;
                case "info":
                    $this->showInfoSelectForm($player);
                    break;
            }
        });

        $form->setTitle("§l§6MEBImgScreen");
        $form->setContent("§7选择操作\n§8将图片放入 plugins/MEBImgScreen/images/");
        $form->addButton("§a快速创建\n§8默认16x16", 0, "textures/ui/color_plus", "make");
        $form->addButton("§e详细创建\n§8自定义参数", 0, "textures/ui/newOffersIcon", "create");
        $form->addButton("§c删除图片屏\n§8选择ID删除", 0, "textures/ui/trash_default", "remove");
        $form->addButton("§b图片屏列表\n§8查看所有", 0, "textures/ui/icon_list", "list");
        $form->addButton("§d查看详情\n§8显示信息", 0, "textures/ui/magnifyingGlass", "info");

        $player->sendForm($form);
    }

    /**
     * 快速创建表单
     */
    public function showQuickCreateForm(Player $player): void
    {
        $images = $this->getAvailableImages();

        if (empty($images)) {
            $player->sendMessage("§c没有可用的图片");
            $player->sendMessage("§7请将图片放入: plugins/MEBImgScreen/images/");
            return;
        }

        $form = new \MengBao\MEBForms\CustomForm(function (Player $player, $data) use ($images) {
            if ($data === null) {
                return;
            }

            $imageName = $images[$data[0]];
            $width = (int) $data[1];
            $height = (int) $data[2];

            // 调用命令处理器的快速创建逻辑
            $this->commandHandler->handleQuickCreateFromGui($player, $imageName, $width, $height);
        });

        $form->setTitle("§l§a快速创建");
        $form->addDropdown("§e选择图片", $images, 0);
        $form->addSlider("§e宽度 §7(方块)", 1, 128, 1, 16);
        $form->addSlider("§e高度 §7(方块)", 1, 128, 1, 16);
        $form->addLabel("§7提示: 将在你面前3格处创建\n§7每个方块=1像素，推荐尺寸16-64");

        $player->sendForm($form);
    }

    /**
     * 详细创建表单
     */
    public function showCreateForm(Player $player): void
    {
        $images = $this->getAvailableImages();

        if (empty($images)) {
            $player->sendMessage("§c没有可用的图片");
            $player->sendMessage("§7请将图片放入: plugins/MEBImgScreen/images/");
            return;
        }

        $form = new \MengBao\MEBForms\CustomForm(function (Player $player, $data) use ($images) {
            if ($data === null) {
                return;
            }

            $id = trim($data[0]);
            $width = (int) $data[1];
            $height = (int) $data[2];
            $directions = ["north", "south", "east", "west"];
            $direction = $directions[$data[3]];
            $imageName = $images[$data[4]];

            // 调用命令处理器的创建逻辑
            $this->commandHandler->handleCreateFromGui($player, $id, $width, $height, $direction, $imageName);
        });

        $form->setTitle("§l§e详细创建");
        $form->addInput("§eID", "img_screen_1");
        $form->addSlider("§e宽度 §7(方块)", 1, 128, 1, 16);
        $form->addSlider("§e高度 §7(方块)", 1, 128, 1, 16);
        $form->addDropdown("§e方向", ["north", "south", "east", "west"], 0);
        $form->addDropdown("§e选择图片", $images, 0);
        $form->addLabel("§7提示: 将在你当前位置创建\n§7每个方块=1像素");

        $player->sendForm($form);
    }

    /**
     * 删除表单
     */
    public function showRemoveForm(Player $player): void
    {
        $screens = $this->plugin->getScreenManager()->getAllScreens();

        if (empty($screens)) {
            $player->sendMessage("§e当前没有图片屏");
            return;
        }

        $screenIds = [];
        foreach ($screens as $screen) {
            $screenIds[] = $screen->getId();
        }

        $form = new \MengBao\MEBForms\CustomForm(function (Player $player, $data) use ($screenIds) {
            if ($data === null) {
                return;
            }

            $id = $screenIds[$data[0]];

            if ($this->plugin->getScreenManager()->removeScreen($id)) {
                $player->sendMessage("§a已删除图片屏: $id");
            } else {
                $player->sendMessage("§c图片屏不存在: $id");
            }
        });

        $form->setTitle("§l§c删除图片屏");
        $form->addDropdown("§e选择要删除的图片屏", $screenIds, 0);
        $form->addLabel("§c注意: 删除操作不可恢复");

        $player->sendForm($form);
    }

    /**
     * 列表表单
     */
    public function showListForm(Player $player): void
    {
        $screens = $this->plugin->getScreenManager()->getAllScreens();

        if (empty($screens)) {
            $player->sendMessage("§e当前没有图片屏");
            return;
        }

        $form = new \MengBao\MEBForms\SimpleForm(function (Player $player, $data) {
            if ($data === null) {
                return;
            }
            $this->showMainMenu($player);
        });

        $form->setTitle("§l§b图片屏列表");

        $content = "§7共 §e" . count($screens) . " §7个图片屏\n\n";
        foreach ($screens as $screen) {
            $pos = $screen->getPosition();
            $content .= "§e{$screen->getId()}\n";
            $content .= "§7尺寸: {$screen->getWidth()}×{$screen->getHeight()} 方块\n";
            $content .= "§7位置: {$pos->getFloorX()}, {$pos->getFloorY()}, {$pos->getFloorZ()}\n\n";
        }

        $form->setContent($content);
        $form->addButton("§a返回", 0, "textures/ui/back_button_default_light");

        $player->sendForm($form);
    }

    /**
     * 选择要查看详情的图片屏
     */
    public function showInfoSelectForm(Player $player): void
    {
        $screens = $this->plugin->getScreenManager()->getAllScreens();

        if (empty($screens)) {
            $player->sendMessage("§e当前没有图片屏");
            return;
        }

        $screenIds = [];
        foreach ($screens as $screen) {
            $screenIds[] = $screen->getId();
        }

        $form = new \MengBao\MEBForms\CustomForm(function (Player $player, $data) use ($screenIds) {
            if ($data === null) {
                return;
            }

            $id = $screenIds[$data[0]];
            $this->showInfoDetails($player, $id);
        });

        $form->setTitle("§l§d查看详情");
        $form->addDropdown("§e选择图片屏", $screenIds, 0);

        $player->sendForm($form);
    }

    /**
     * 显示详细信息
     */
    public function showInfoDetails(Player $player, string $id): void
    {
        $screen = $this->plugin->getScreenManager()->getScreen($id);

        if ($screen === null) {
            $player->sendMessage("§c图片屏不存在: $id");
            return;
        }

        $pos = $screen->getPosition();

        $form = new \MengBao\MEBForms\SimpleForm(function (Player $player, $data) {
            if ($data === null) {
                return;
            }
            $this->showMainMenu($player);
        });

        $form->setTitle("§l§d图片屏详情");
        $content = "§eID: §f$id\n\n";
        $content .= "§e尺寸: §f{$screen->getWidth()}×{$screen->getHeight()} 方块\n";
        $content .= "§e方向: §f{$screen->getDirection()}\n";
        $content .= "§e图片: §f{$screen->getImagePath()}\n";
        $content .= "§e位置: §f{$pos->getFloorX()}, {$pos->getFloorY()}, {$pos->getFloorZ()}\n";
        $content .= "§e世界: §f{$screen->getWorld()->getFolderName()}\n";
        $content .= "§e类型: §f方块像素画";

        $form->setContent($content);
        $form->addButton("§a返回", 0, "textures/ui/back_button_default_light");

        $player->sendForm($form);
    }

    /**
     * 获取可用图片列表
     */
    private function getAvailableImages(): array
    {
        $imagesDir = $this->plugin->getDataFolder() . "images/";
        if (!is_dir($imagesDir)) {
            return [];
        }

        $images = [];
        $files = scandir($imagesDir);
        foreach ($files as $file) {
            if ($file === "." || $file === "..") {
                continue;
            }
            $path = $imagesDir . $file;
            if (is_file($path)) {
                $images[] = $file;
            }
        }

        return $images;
    }
}

