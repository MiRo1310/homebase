<?php

namespace App\Repository;


use App\Interface\Specification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class SpecificationRepository extends ServiceEntityRepository
{
    public function findBySpecification(Specification $specification): array
    {
        $queryBuilder = $this->createQueryBuilder($specification->getAlias());
        $queryBuilder = $specification->buildQueryBuilder($queryBuilder);
        return $queryBuilder->getQuery()->getResult();
    }

    public function findOneBySpecification(Specification $specification): ?object
    {
        $queryBuilder = $this->createQueryBuilder($specification->getAlias());
        $queryBuilder = $specification->buildQueryBuilder($queryBuilder);
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
