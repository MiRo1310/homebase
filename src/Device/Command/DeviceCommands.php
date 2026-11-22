<?php

namespace App\Device\Command;

use App\Device\Service\DeviceService;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:device')]
class DeviceCommands
{
    public function __construct(
        private DeviceService $deviceService,
    )
    {
    }

    #[AsCommand('sync-updates', description: 'Sync update info for devices')]
    public function syncUpdateInfoForOneDevice(
        #[Argument('The user to connect with')] ?string $name = null,
        OutputInterface                                 $output): int
    {
        $success = $this->deviceService->syncDeviceUpdates($output, $name);

        return $success ? Command::SUCCESS : Command::FAILURE;
    }
}
