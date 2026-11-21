<?php

namespace App\Device\Dto;

class Packages
{
    public function __construct(
        private array $packages
    )
    {
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return count($this->packages);
    }

    /**
     * @return array<PackageUpdate>
     */
    public function getPackages(): array
    {
        return $this->packages;
    }
}
