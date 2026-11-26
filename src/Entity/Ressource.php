<?php

namespace App\Entity;

use App\Repository\Ressource\RessourceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: RessourceRepository::class)]
class Ressource
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'ressource')]                 // inversedBy nur, wenn Device eine Gegen-Property bekommt
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]  // Pflicht; Gerät gelöscht -> Ressource wird mitgelöscht
    private Device $device;

    #[ORM\Column(length: 100)]
    private string $prettyName;

    #[ORM\Column(length: 50)]
    private string $name;

    #[ORM\Column(length: 50)]
    private string $version;

    #[ORM\Column(length: 50)]
    private string $versionCodenName;

    #[ORM\Column]
    private int $cpuUsagePercent;

    #[ORM\Column]
    private int $cores;

    #[ORM\Column]
    private int $memTotal;

    #[ORM\Column]
    private int $memAvailable;

    #[ORM\Column]
    private int $swapTotal;

    #[ORM\Column]
    private int $swapFree;

    #[ORM\Column]
    private int $oneBlocks;

    #[ORM\Column]
    private int $diskUsed;

    #[ORM\Column]
    private int $diskAvailable;

    #[ORM\Column(length: 10)]
    private string $diskCapacity;

    #[ORM\Column(length: 100)]
    private string $mountedOn;

    #[ORM\Column(length: 255)]
    private string $filesystem;

    #[ORM\Column]
    private int $uptimeSeconds;

    public function setMemTotal(int $memTotal): void
    {
        $this->memTotal = $memTotal;
    }

    public function getMemTotal(): int
    {
        return $this->memTotal;
    }

    public function setDevice(Device $device): void
    {
        $this->device = $device;
    }

    public function getDevice(): Device
    {
        return $this->device;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setCores(int $cores): void
    {
        $this->cores = $cores;
    }

    public function getCores(): int
    {
        return $this->cores;
    }

    public function setCpuUsagePercent(int $cpuUsagePercent): void
    {
        $this->cpuUsagePercent = $cpuUsagePercent;
    }

    public function getCpuUsagePercent(): int
    {
        return $this->cpuUsagePercent;
    }

    public function setDiskAvailable(int $diskAvailable): void
    {
        $this->diskAvailable = $diskAvailable;
    }

    public function getDiskAvailable(): int
    {
        return $this->diskAvailable;
    }

    public function setDiskCapacity(string $diskCapacity): void
    {
        $this->diskCapacity = $diskCapacity;
    }

    public function getDiskCapacity(): string
    {
        return $this->diskCapacity;
    }

    public function setDiskUsed(int $diskUsed): void
    {
        $this->diskUsed = $diskUsed;
    }

    public function getDiskUsed(): int
    {
        return $this->diskUsed;
    }

    public function setFilesystem(string $filesystem): void
    {
        $this->filesystem = $filesystem;
    }

    public function getFilesystem(): string
    {
        return $this->filesystem;
    }

    public function setMemAvailable(int $memAvailable): void
    {
        $this->memAvailable = $memAvailable;
    }

    public function getMemAvailable(): int
    {
        return $this->memAvailable;
    }

    public function setMountedOn(string $mountedOn): void
    {
        $this->mountedOn = $mountedOn;
    }

    public function getMountedOn(): string
    {
        return $this->mountedOn;
    }

    public function setOneBlocks(int $oneBlocks): void
    {
        $this->oneBlocks = $oneBlocks;
    }

    public function getOneBlocks(): int
    {
        return $this->oneBlocks;
    }

    public function setPrettyName(string $prettyName): void
    {
        $this->prettyName = $prettyName;
    }

    public function getPrettyName(): string
    {
        return $this->prettyName;
    }

    public function setSwapFree(int $swapFree): void
    {
        $this->swapFree = $swapFree;
    }

    public function getSwapFree(): int
    {
        return $this->swapFree;
    }

    public function setSwapTotal(int $swapTotal): void
    {
        $this->swapTotal = $swapTotal;
    }

    public function getSwapTotal(): int
    {
        return $this->swapTotal;
    }

    public function setUptimeSeconds(int $uptimeSeconds): void
    {
        $this->uptimeSeconds = $uptimeSeconds;
    }

    public function getUptimeSeconds(): int
    {
        return $this->uptimeSeconds;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersionCodenName(string $versionCodenName): void
    {
        $this->versionCodenName = $versionCodenName;
    }

    public function getVersionCodenName(): string
    {
        return $this->versionCodenName;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSwapUsed(): int
    {
        return $this->swapTotal - $this->swapFree;
    }

    public function getMemUsed(): int
    {
        return $this->memTotal - $this->memAvailable;
    }

}
