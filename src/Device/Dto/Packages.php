<?php

namespace App\Device\Dto;

use App\Entity\Device;
use App\Entity\DeviceUpdate;

class Packages
{
    /**
     * @param array<PackageUpdate> $packages
     */
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

    /**
     * @return array<DeviceUpdate>
     */
    public function mapToDeviceUpdate(Device $device): array
    {
        return array_map(function (PackageUpdate $package) use ($device) {
            $deviceUpdate = new DeviceUpdate($device);
            $deviceUpdate->setName($package->name);
            $deviceUpdate->setNewVersion($package->newVersion);
            $deviceUpdate->setOldVersion($package->oldVersion);
            $deviceUpdate->setOrigin($package->origin);
            return $deviceUpdate;
        },
            $this->packages
        );
    }
}
