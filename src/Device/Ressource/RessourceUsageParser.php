<?php

namespace App\Device\Ressource;

use App\Device\Dto\CpuUsage;
use App\Device\Dto\DiskUsage;
use App\Device\Dto\MemoryUsage;
use App\Device\Dto\OsRelease;
use App\Device\Dto\Ressource;

class RessourceUsageParser
{
    private const array MEMORY = ["MemTotal", "MemAvailable", "SwapTotal", "SwapFree"];

    public function parseRessourceList(string $ressourceList): Ressource
    {
        $sections = explode("=====\n", $ressourceList);

        return new Ressource(
            memory: $this->parseMemUsage(explode("\n", $sections[0])),
            diskUsage: $this->parseDiskUsage(explode("\n", $sections[1])),
            cpuUsage: $this->parseCpuUsage(explode("\n", $sections[2])),
            uptimeSeconds: (int)explode(" ", $sections[3])[0],
            osRelease: $this->parseOsRelease(explode("\n", $sections[4]))
        );
    }

    private function parseOsRelease(array $list): OsRelease
    {
        $values = [];

        foreach (["PRETTY_NAME", "NAME", "VERSION", "VERSION_CODENAME"] as $name) {
            $filteredRows = array_values(array_filter($list, fn(string $row) => str_starts_with($row, $name . "=")));
            $values[$name] = trim(str_replace([$name . "=", '"'], "", $filteredRows[0]));
        }

        return new OsRelease(
            prettyName: $values["PRETTY_NAME"],
            name: $values["NAME"],
            version: $values["VERSION"],
            versionCodeName: $values["VERSION_CODENAME"]);
    }

    private function parseCpuUsage(array $list): CpuUsage
    {
        return new CpuUsage(
            timeStartNs: (int)$list[0],
            timeEndNs: (int)$list[2],
            usageStartUsec: $this->valueAfterKey($list[1]),
            usageEndUsec: $this->valueAfterKey($list[3]),
            cores: $this->effectiveCores((int)$list[4], $list[5]),
        );
    }

    /**
     * "usage_usec 1892784842" → 1892784842
     */
    private function valueAfterKey(string $row): int
    {
        return (int)explode(" ", trim($row))[1];
    }

    /**
     * cpu.max hat das Format "<Kontingent> <Periode>", z. B. "150000 100000" → 1,5 Kerne.
     * Steht dort "max ...", gibt es kein Limit und es gilt nproc.
     */
    private function effectiveCores(int $nproc, string $cpuMax): float
    {
        [$quota, $period] = explode(" ", trim($cpuMax));

        if ($quota === "max") {
            return $nproc;
        }

        return (int)$quota / (int)$period;
    }

    private function parseDiskUsage(array $list): DiskUsage
    {
        $diskValues = array_values(array_filter(
            explode(" ", $list[1]),
            fn(string $val) => $val !== ""
        ));

        return new DiskUsage(filesystem: $diskValues[0], oneBlocks: (int)$diskValues[1], used: (int)$diskValues[2], available: (int)$diskValues[3], capacity: $diskValues[4], mountedOn: $diskValues[5]);
    }

    private function parseMemUsage(array $list): MemoryUsage
    {
        $memory = [];
        foreach (self::MEMORY as $resource) {
            $memory[$resource] = $this->filterIntFromRow($list, $resource);
        }
        return new MemoryUsage(
            memTotal: $memory["MemTotal"],
            memAvailable: $memory["MemAvailable"],
            swapTotal: $memory["SwapTotal"],
            swapFree: $memory["SwapFree"]
        );
    }

    /**
     * @param string[] $row
     * @param string $ressource
     * @return int
     */
    private function filterIntFromRow(array $list, string $ressource): int
    {
        $filteredRow = array_find($list, function (string $row) use ($ressource) {
            return str_starts_with($row, $ressource);
        });

        return trim(str_replace("kB", "", explode(":", $filteredRow)[1]));
    }
}
