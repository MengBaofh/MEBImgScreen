<?php

declare(strict_types=1);

namespace MengBao\MEBAdScreen\Struct;

use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;

class AdScreen
{
    private string $id;
    private World $world;
    private int $x;
    private int $y;
    private int $z;
    private string $direction;
    private int $width;
    private int $height;
    private string $imagePath;

    /** @var int[] */
    private array $mapIds;

    /** @var Player[] */
    private array $viewers = [];

    private int $currentFrame = 0;
    private int $frameCount = 1;

    public function __construct(
        string $id,
        World $world,
        int $x,
        int $y,
        int $z,
        string $direction,
        int $width,
        int $height,
        string $imagePath,
        array $mapIds
    ) {
        $this->id = $id;
        $this->world = $world;
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->direction = $direction;
        $this->width = $width;
        $this->height = $height;
        $this->imagePath = $imagePath;
        $this->mapIds = $mapIds;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getWorld(): World
    {
        return $this->world;
    }

    public function getPosition(): Vector3
    {
        return new Vector3($this->x, $this->y, $this->z);
    }

    public function getCenterPosition(): Vector3
    {
        return new Vector3(
            $this->x + ($this->width * 0.5),
            $this->y + ($this->height * 0.5),
            $this->z
        );
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getImagePath(): string
    {
        return $this->imagePath;
    }

    public function getMapIds(): array
    {
        return $this->mapIds;
    }

    public function addViewer(Player $player): void
    {
        $name = $player->getName();
        if (!isset($this->viewers[$name])) {
            $this->viewers[$name] = $player;
        }
    }

    public function removeViewer(Player $player): void
    {
        unset($this->viewers[$player->getName()]);
    }

    /**
     * @return Player[]
     */
    public function getViewers(): array
    {
        return array_filter($this->viewers, fn($p) => $p->isOnline());
    }

    public function getCurrentFrame(): int
    {
        return $this->currentFrame;
    }

    public function nextFrame(): void
    {
        $this->currentFrame = ($this->currentFrame + 1) % $this->frameCount;
    }

    public function setFrameCount(int $count): void
    {
        $this->frameCount = max(1, $count);
    }

    public function getFrameCount(): int
    {
        return $this->frameCount;
    }

    public function isAnimated(): bool
    {
        return $this->frameCount > 1;
    }

    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "world" => $this->world->getFolderName(),
            "x" => $this->x,
            "y" => $this->y,
            "z" => $this->z,
            "direction" => $this->direction,
            "width" => $this->width,
            "height" => $this->height,
            "imagePath" => $this->imagePath,
            "mapIds" => $this->mapIds
        ];
    }

    public static function fromArray(array $data, Server $server): self
    {
        $worldManager = $server->getWorldManager();
        $world = $worldManager->getWorldByName($data["world"]);
        if ($world === null) {
            throw new \RuntimeException("世界不存在: " . $data["world"]);
        }

        return new self(
            $data["id"],
            $world,
            $data["x"],
            $data["y"],
            $data["z"],
            $data["direction"],
            $data["width"],
            $data["height"],
            $data["imagePath"],
            $data["mapIds"]
        );
    }
}
