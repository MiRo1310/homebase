<?php

namespace App\Repository\Ressource;

use App\Entity\Device;
use App\Repository\SpecificationRepository;
use Doctrine\Persistence\ManagerRegistry;

class RessourceRepository extends SpecificationRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Device::class);
    }
}
