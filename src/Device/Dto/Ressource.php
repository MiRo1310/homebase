<?php

namespace App\Device\Dto;

readonly class Ressource
{
    public function __construct(
        public MemoryUsage $memory,
        public DiskUsage   $diskUsage,
        public CpuUsage    $cpuUsage,
        public int         $uptimeSeconds,
        public OsRelease   $osRelease
    )
    {
    }


}
