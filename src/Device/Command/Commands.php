<?php

namespace App\Device\Command;

use App\Device\Service\DeviceService;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: "app:connection")]
class Commands
{
    public function __construct(
        private DeviceService $deviceService,
    )
    {
    }

    public function __invoke(
        #[Argument('The host to connect')] string      $host,
        #[Argument('The user to connect with')] string $user,
        OutputInterface                                $output): int
    {
        $output->writeln("Connecting to host: " . $host . " with User: " . $user);

        $deviceInformation = $this->deviceService->getDeviceInformation($host, $user);


        $output->writeln("Command");
//        $output->writeln($ssh->exec('uname -r')); // Version Proxmox
        $output->writeln("Kernelname: " . $deviceInformation->kernalName);
//        $output->writeln($ssh->exec('apt list --upgradable')); // Liste der Updates
//        $output->writeln($ssh->exec('apt-get -s upgrade')); // Liste der Updates


        return Command::SUCCESS;
    }
}
