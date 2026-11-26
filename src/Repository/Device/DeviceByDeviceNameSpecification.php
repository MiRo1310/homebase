<?php

namespace App\Repository\Device;

use App\Interface\Specification;
use Doctrine\ORM\QueryBuilder;

class DeviceByDeviceNameSpecification implements Specification
{
    public function __construct(private readonly string $name)
    {
    }

    public function getAlias(): string
    {
        return "d";
    }

    public function buildQueryBuilder(QueryBuilder $qb): QueryBuilder
    {
        return $qb
            ->andWhere("{$this->getAlias()}.name = :name")
            ->setParameter('name', $this->name);
    }
}
