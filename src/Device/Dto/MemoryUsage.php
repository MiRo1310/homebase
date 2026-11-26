<?php

namespace App\Device\Dto;

readonly class MemoryUsage
{
    public function __construct(
        public int $memTotal,
        public int $memAvailable,
        public int $swapTotal,
        public int $swapFree
    )
    {
    }
}
