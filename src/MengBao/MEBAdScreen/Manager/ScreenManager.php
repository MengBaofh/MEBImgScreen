<?php

declare(strict_types=1);

namespace MengBao\MEBAdScreen\Manager;

use MengBao\MEBAdScreen\Main;
use MengBao\MEBAdScreen\Struct\AdScreen;
use MengBao\MEBAdScreen\Task\AnimationTask;
use MengBao\MEBAdScreen\Task\PlayerDistanceCheckTask;
use MengBao\MEBAdScreen\Util\ItemFrameBuilder;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\World;

class ScreenManager
{
    private Main $plugin;

    /** @var AdScreen[] */
    private array $screens = [];

    private MapRenderer $mapRenderer;

    private ItemFrameBuilder $frameBuilder;

    private int $nextMapId;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $this->nextMapId = (int) $plugin->getPluginConfig()->get("地图起始ID", 1000);
        $this->mapRenderer = new MapRenderer($plugin);
        $this->frameBuilder = new ItemFrameBuilder();

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
                $screen = AdScreen::fromArray($screenData, $this->plugin->getServer());
                $this->screens[$screen->getId()] = $screen;
            } catch (\Exception $e) {
                $this->plugin->getLogger()->error("加载屏幕失败: " . $e->getMessage());
            }
        }

        $this->plugin->getLogger()->info("§a已加载 " . count($this->screens) . " 个广告屏");
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
        $interval = max(1, (int) round(20 * (float) $this->plugin->getPluginConfig()->get("距离检测间隔(s)", 1.0)));
        $this->plugin->getScheduler()->scheduleRepeatingTask(
            new PlayerDistanceCheckTask($this->plugin, $this),
            $interval
        );

        $this->plugin->getScheduler()->scheduleRepeatingTask(
            new AnimationTask($this->plugin, $this),
            (int) $this->plugin->getPluginConfig()->get("GIF帧间隔(tick)", 4)
        );
    }

    public function createScreen(string $id, World $world, int $x, int $y, int $z, string $direction, int $width, int $height, string $imagePath, bool $autoBuild = true): ?AdScreen
    {
        if (isset($this->screens[$id])) {
            return null;
        }

        if (count($this->screens) >= (int) $this->plugin->getPluginConfig()->get("最大屏幕数", 50)) {
            return null;
        }

        $mapIds = [];
        for ($i = 0; $i < $width * $height; $i++) {
            $mapIds[] = $this->nextMapId++;
        }

        $screen = new AdScreen($id, $world, $x, $y, $z, $direction, $width, $height, $imagePath, $mapIds);
        $this->screens[$id] = $screen;

        // 渲染图像到地图
        if (!$this->mapRenderer->renderScreen($screen)) {
            unset($this->screens[$id]);
            return null;
        }

        // 自动构建物品展示框
        if ($autoBuild) {
            $this->frameBuilder->buildFrameGrid($screen);
        }

        $this->saveScreens();

        return $screen;
    }

    public function removeScreen(string $id): bool
    {
        if (!isset($this->screens[$id])) {
            return false;
        }

        $screen = $this->screens[$id];

        // 移除物品展示框
        $this->frameBuilder->removeFrameGrid($screen);

        unset($this->screens[$id]);
        $this->saveScreens();
        return true;
    }

    public function getScreen(string $id): ?AdScreen
    {
        return $this->screens[$id] ?? null;
    }

    /**
     * @return AdScreen[]
     */
    public function getAllScreens(): array
    {
        return $this->screens;
    }

    public function getMapRenderer(): MapRenderer
    {
        return $this->mapRenderer;
    }

    public function updatePlayerVisibility(Player $player): void
    {
        $maxDistance = (float) $this->plugin->getPluginConfig()->get("最大可视距离", 64);
        $playerPos = $player->getPosition();

        foreach ($this->screens as $screen) {
            if ($screen->getWorld()->getFolderName() !== $playerPos->getWorld()->getFolderName()) {
                continue;
            }

            $distance = $playerPos->distance($screen->getCenterPosition());
            $wasViewing = in_array($player, $screen->getViewers(), true);

            if ($distance <= $maxDistance) {
                if (!$wasViewing) {
                    $screen->addViewer($player);
                    // 新观看者，发送初始地图数据
                    $this->sendInitialMaps($player, $screen);
                }
            } else {
                if ($wasViewing) {
                    $screen->removeViewer($player);
                }
            }
        }
    }

    /**
     * 向玩家发送屏幕的初始地图数据
     */
    private function sendInitialMaps(Player $player, AdScreen $screen): void
    {
        $currentFrame = $screen->getCurrentFrame();

        foreach ($screen->getMapIds() as $mapId) {
            $pixels = $this->mapRenderer->getMapData($mapId, $currentFrame);
            if ($pixels === null) {
                continue;
            }

            $pk = new \pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket();
            $pk->mapId = $mapId;
            $pk->scale = 0;
            $pk->width = 128;
            $pk->height = 128;
            $pk->dimensionId = 0;
            $pk->colors = $pixels;

            $player->getNetworkSession()->sendDataPacket($pk);
        }
    }

    public function shutdown(): void
    {
        $this->saveScreens();
    }
}
