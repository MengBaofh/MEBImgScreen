<?php

declare(strict_types=1);

namespace MengBao\MEBAdScreen\Struct;

use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\world\World;

class ImgScreen
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

    public function __construct(
        string $id,
        World $world,
        int $x,
        int $y,
        int $z,
        string $direction,
        int $width,
        int $height,
        string $imagePath
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
            "imagePath" => $this->imagePath
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
            $data["imagePath"]
        );
    }
}
