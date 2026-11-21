<?php

namespace App\Device\Apt;

use App\Device\Dto\Packages;
use App\Device\Dto\PackageUpdate;

class AptUpgradeParser
{
    private const string SEARCHED = "Inst ";

    /**
     * @param string $list
     * @return array
     */
    public function parseUpdateList(string $str): Packages
    {
        $upgradable = $this->getUpgradableRows($str);
        $array = array_map(fn($package) => $this->getPackageUpdate($package), $upgradable);
        return new Packages($array);
    }

    /**
     * @return array
     */
    private function getUpgradableRows(string $str): array
    {
        $splitted = explode("\n", $str);

        return array_values(array_filter($splitted, fn($row) => str_starts_with($row, self::SEARCHED)));
    }

    private function getPackageUpdate(string $package): PackageUpdate
    {
        $package = explode(" ", str_replace(self::SEARCHED, "", $package));

        $secondItem = $package[1];
        $oldVersion = null;
        $newVersion = $secondItem;
        $rest = array_slice($package, 2);

        if (str_starts_with($secondItem, "[")) {
            $oldVersion = $this->stripBrackets($secondItem);
            $newVersion = $package[2];
            $rest = array_slice($package, 3);
        }

        $origin = explode("[", implode(" ", $rest))[0];

        return new PackageUpdate($this->stripBrackets($package[0]), $oldVersion, $this->stripBrackets($newVersion), trim(str_replace(")", "", $origin)));
    }

    private function stripBrackets(string $str): string
    {
        return trim(str_replace(["[", "]", "(", ")"], "", $str));
    }
}
