<?php

namespace App\Interface;

use Doctrine\ORM\QueryBuilder;

interface Specification
{
    public function getAlias(): string;

    public function buildQueryBuilder(QueryBuilder $qb): QueryBuilder;

}
