<?php

namespace App\Entity;

use App\Repository\Device\DeviceUpdateRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: DeviceUpdateRepository::class)]
class DeviceUpdate
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'updates')] // viele Updates -> ein Gerät; inversedBy = Name der Gegen-Property in Device ($updates)
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] // device_id ist Pflicht; Gerät gelöscht -> DB löscht seine Updates mit
    private Device $device;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $oldVersion = null;

    #[ORM\Column(length: 255)]
    private string $newVersion;

    #[ORM\Column(length: 255)]
    private string $origin;

    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param Device $device
     */
    public function setDevice(Device $device): void
    {
        $this->device = $device;
    }

    /**
     * @return Device
     */
    public function getDevice(): Device
    {
        return $this->device;
    }

    /**
     * @param string $newVersion
     */
    public function setNewVersion(string $newVersion): void
    {
        $this->newVersion = $newVersion;
    }

    /**
     * @return string
     */
    public function getNewVersion(): string
    {
        return $this->newVersion;
    }

    /**
     * @param string|null $oldVersion
     */
    public function setOldVersion(?string $oldVersion): void
    {
        $this->oldVersion = $oldVersion;
    }

    /**
     * @return string|null
     */
    public function getOldVersion(): ?string
    {
        return $this->oldVersion;
    }

    /**
     * @param string $origin
     */
    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }

    /**
     * @return string
     */
    public function getOrigin(): string
    {
        return $this->origin;
    }


}
