<?php

namespace App\Repository\Device;

use App\Entity\DeviceUpdate;
use App\Repository\SpecificationRepository;
use Doctrine\Persistence\ManagerRegistry;

class DeviceUpdateRepository extends SpecificationRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeviceUpdate::class);
    }
}
