<?php

namespace App\Device\Dto;

class PackageUpdate
{
    public function __construct(
        public readonly string  $name,
        public readonly ?string $oldVersion,
        public readonly string  $newVersion,
        public readonly string  $origin
    )
    {
    }


}
