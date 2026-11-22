<?php

namespace App\Device\Service;

use App\Device\Apt\AptUpgradeParser;
use App\Device\Dto\Packages;
use App\Entity\Device;
use App\Repository\Device\DeviceByDeviceNameSpecification;
use App\Repository\Device\DeviceRepository;
use App\Ssh\SshConnection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeviceService
{
    public function __construct(
        private readonly SshConnection          $sshConnection,
        private readonly AptUpgradeParser       $aptUpgradeParser,
        private readonly DeviceRepository       $deviceRepository,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function syncDeviceUpdates(OutputInterface $output, ?string $deviceName = null): bool
    {
        $result = $this->getDevices($deviceName);

        $output->writeln("Updating devices...");
        $output->writeln("Devices count: " . count($result));

        if ($deviceName !== null && count($result) === 0) {
            $output->writeln("No devices found.");
            return false;
        }

        $failed = 0;

        foreach ($result as $device) {
            $package = $this->getDeviceUpdates($output, $device);
            if (!$package) {
                $failed++;
                continue;
            };

            $this->updateInDB($device, $package);
        }
        if ($failed === 0) {

            return true;
        }
        $output->writeln("For " . $failed . " devices failed to get update Infos.");
        return false;
    }

    private function getDeviceUpdates(OutputInterface $output, Device $device): Packages|false
    {
        $host = $device->getHost();
        $port = $device->getPort();
        $user = $device->getUsername();

        $output->writeln("Connecting to host: " . $host . " with User: " . $user);
        $ssh = null;
        $packages = null;
        try {
            $ssh = $this->sshConnection->connection($host, $user, $port);
            $packages = $this->aptUpgradeParser->parseUpdateList($ssh->exec('apt-get -s upgrade'));
            $output->writeln("Device '" . ($device->getName() ?? $host) . "' has " . $packages->getCount() . " Update(s)");
        } catch (\Throwable $exception) {
            $output->writeln("<error>Device '" . $device->getName() . "' failed: " . $exception->getMessage() . "</error>");
            return false;
        } finally {
            $ssh?->disconnect();
            $output->writeln("Device updates are synced.");
            $output->writeln("Connection closed for host: " . $host);

            return $packages ?? false;
        }
    }

    /**
     * @param string|null $deviceName
     * @return array<Device>
     */
    public function getDevices(?string $deviceName): array
    {
        if ($deviceName) {
            $device = $this->deviceRepository->findOneBySpecification(new DeviceByDeviceNameSpecification($deviceName));
            return $device ? [$device] : [];
        }

        return $this->deviceRepository->findAll();
    }

    private function updateInDB(Device $device, Packages $packages): void
    {
        $device->getUpdates()->clear();

        foreach ($packages->mapToDeviceUpdate($device) as $deviceUpdate) {
            $device->getUpdates()->add($deviceUpdate);
        }

        $this->entityManager->flush();
    }
}
