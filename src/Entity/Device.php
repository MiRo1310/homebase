<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\Device\DeviceRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: DeviceRepository::class)]
class Device
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $host;

    #[ORM\Column]
    private int $port = 22;

    #[ORM\Column(length: 20)]
    private string $username;

    public function __construct(string $name, string $host, string $username, int $port = 22)
    {
        $this->name = $name;
        $this->host = $host;
        $this->username = $username;
        $this->port = $port;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param string $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }
}
