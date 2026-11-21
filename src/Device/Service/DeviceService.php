<?php

namespace App\Device\Service;

use App\Device\Apt\AptUpgradeParser;
use App\Device\Dto\DeviceInformation;
use App\Ssh\SshConnection;

class DeviceService
{
    public function __construct(
        private readonly SshConnection    $sshConnection,
        private readonly AptUpgradeParser $aptUpgradeParser
    )
    {
    }

    public function getDeviceInformation(string $host, string $user): DeviceInformation
    {
        $ssh = $this->sshConnection->connection($host, $user);
        $packages = $this->aptUpgradeParser->parseUpdateList($ssh->exec('apt-get -s upgrade'));
        return new DeviceInformation($ssh->exec('uname -s'));
    }
}
