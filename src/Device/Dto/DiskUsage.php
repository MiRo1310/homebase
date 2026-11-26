<?php

namespace App\Device\Dto;

readonly class DiskUsage
{
    public function __construct(
        public string $filesystem,
        public int    $oneBlocks,
        public int    $used,
        public int    $available,
        public string $capacity,
        public string $mountedOn
    )
    {
    }

}
