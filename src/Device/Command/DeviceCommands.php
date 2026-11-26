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
    public function syncUpdateInfos(
        OutputInterface                                 $output,
        #[Argument('The user to connect with')] ?string $name = null,
    ): int
    {
        $success = $this->deviceService->syncDeviceUpdates($output, $name);

        return $success ? Command::SUCCESS : Command::FAILURE;
    }

    #[AsCommand('sync-ressource', description: 'Sync device status values')]
    public function syncRessourceUsage(
        OutputInterface                                 $output,
        #[Argument('The user to connect with')] ?string $name = null,
    ): int
    {
        $success = $this->deviceService->syncDeviceRessourceUsage($output, $name);

        return $success ? Command::SUCCESS : Command::FAILURE;
    }
}
