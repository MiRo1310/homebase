<?php

namespace App\Device\Dto;

readonly class OsRelease
{
    public function __construct(
        public string $prettyName,
        public string $name,
        public string $version,
        public string $versionCodeName
    )
    {
    }

}
