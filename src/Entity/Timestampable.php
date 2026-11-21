<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait Timestampable
{
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    public function onPrePersist() :void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate():void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt():?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt():?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
