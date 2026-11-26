<?php

declare(strict_types=1);

namespace App\Repository\Device;

use App\Entity\Device;
use App\Repository\SpecificationRepository;
use Doctrine\Persistence\ManagerRegistry;

class DeviceRepository extends SpecificationRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Device::class);
    }
}
