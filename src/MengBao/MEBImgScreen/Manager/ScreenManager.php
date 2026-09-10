<?php

declare(strict_types=1);

namespace MengBao\MEBImgScreen\Manager;

use MengBao\MEBImgScreen\Main;
use MengBao\MEBImgScreen\Struct\ImgScreen;
use MengBao\MEBImgScreen\Util\BlockScreenBuilder;
use MengBao\MEBImgScreen\Util\ImageProcessor;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\World;

class ScreenManager
{
    private Main $plugin;

    /** @var ImgScreen[] */
    private array $screens = [];

    private BlockScreenBuilder $screenBuilder;
    private ImageProcessor $imageProcessor;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $this->screenBuilder = new BlockScreenBuilder($plugin);
        $this->imageProcessor = new ImageProcessor();

        $this->loadScreens();
        $this->startTasks();
    }

    private function loadScreens(): void
    {
        $file = $this->plugin->getDataFolder() . "screens/screens.json";
        if (!file_exists($file)) {
            file_put_contents($file, "[]");
            return;
        }

        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data)) {
            return;
        }

        foreach ($data as $screenData) {
            try {
                $screen = ImgScreen::fromArray($screenData, $this->plugin->getServer());
                $this->screens[$screen->getId()] = $screen;

                // 重新构建方块屏幕
                $this->rebuildScreen($screen);
            } catch (\Exception $e) {
                $this->plugin->getLogger()->error("Load screen failed: " . $e->getMessage());
            }
        }

        $this->plugin->getLogger()->info("§aLoaded " . count($this->screens) . " image screens");
    }

    public function saveScreens(): void
    {
        $data = [];
        foreach ($this->screens as $screen) {
            $data[] = $screen->toArray();
        }

        $file = $this->plugin->getDataFolder() . "screens/screens.json";
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function startTasks(): void
    {
        // 方块屏幕是静态的，不需要定时任务
    }

    public function createScreen(string $id, World $world, int $x, int $y, int $z, string $direction, int $width, int $height, string $imagePath, bool $autoBuild = true): ?ImgScreen
    {
        if (isset($this->screens[$id])) {
            return null;
        }

        if (count($this->screens) >= (int) $this->plugin->getPluginConfig()->get("最大屏幕数", 50)) {
            return null;
        }

        $screen = new ImgScreen($id, $world, $x, $y, $z, $direction, $width, $height, $imagePath);
        $this->screens[$id] = $screen;

        // 构建方块屏幕
        if ($autoBuild) {
            if (!$this->rebuildScreen($screen)) {
                unset($this->screens[$id]);
                return null;
            }
        }

        $this->saveScreens();

        return $screen;
    }

    /**
     * 重新构建方块屏幕
     */
    private function rebuildScreen(ImgScreen $screen): bool
    {
        $imagePath = $this->plugin->getDataFolder() . "images/" . $screen->getImagePath();
        if (!file_exists($imagePath)) {
            $this->plugin->getLogger()->error("Image not found: {$imagePath}");
            return false;
        }

        // 加载并调整图片大小到屏幕尺寸（每个方块 = 1 像素）
        $pixels = $this->imageProcessor->loadAndResizeForBlocks(
            $imagePath,
            $screen->getWidth(),
            $screen->getHeight()
        );

        if ($pixels === null) {
            $this->plugin->getLogger()->error("Failed to load image: {$imagePath}");
            return false;
        }

        // 构建方块屏幕
        return $this->screenBuilder->buildScreen($screen, $pixels);
    }

    public function removeScreen(string $id): bool
    {
        if (!isset($this->screens[$id])) {
            return false;
        }

        $screen = $this->screens[$id];

        // 移除方块
        $this->screenBuilder->removeScreen($screen);

        unset($this->screens[$id]);
        $this->saveScreens();
        return true;
    }

    public function getScreen(string $id): ?ImgScreen
    {
        return $this->screens[$id] ?? null;
    }

    /**
     * @return ImgScreen[]
     */
    public function getAllScreens(): array
    {
        return $this->screens;
    }

    public function shutdown(): void
    {
        $this->saveScreens();
    }
}
