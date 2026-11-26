<?php

namespace App\Device\Service;

use App\Device\Apt\AptUpgradeParser;
use App\Device\Dto\Packages;
use App\Device\Dto\Ressource;
use App\Device\Ressource\RessourceUsageParser;
use App\Entity\Device;
use App\Repository\Device\DeviceByDeviceNameSpecification;
use App\Repository\Device\DeviceRepository;
use App\Ssh\SshConnection;
use Doctrine\ORM\EntityManagerInterface;
use phpseclib4\Net\SSH2;
use Symfony\Component\Console\Output\OutputInterface;

class DeviceService
{
    public function __construct(
        private readonly SshConnection          $sshConnection,
        private readonly AptUpgradeParser       $aptUpgradeParser,
        private readonly RessourceUsageParser   $ressourceUsageParser,
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

    public function syncDeviceRessourceUsage(OutputInterface $output, ?string $deviceName = null): bool
    {
        $result = $this->getDevices($deviceName);

        $output->writeln("Getting device status...");
        $output->writeln("Devices count: " . count($result));

        if ($deviceName !== null && count($result) === 0) {
            $output->writeln("No devices found.");
            return false;
        }

        $failed = 0;

        foreach ($result as $device) {
            $ressource = $this->getDeviceRessourceUsage($output, $device);
            if (!$ressource) {
                $failed++;
                continue;
            };

//            $this->updateInDB($device, $package);
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
            $output->writeln("Device updates are synced.");
            $this->closeConnection($ssh, $output, $host);

            return $packages ?? false;
        }
    }

    private function getDeviceRessourceUsage(OutputInterface $output, Device $device): Ressource|false
    {
        $host = $device->getHost();
        $port = $device->getPort();
        $user = $device->getUsername();

        $output->writeln("Connecting to host: " . $host . " with User: " . $user);
        $ssh = null;
        $ressource = null;
        try {
            $ssh = $this->sshConnection->connection($host, $user, $port);
            $ressource = $this->ressourceUsageParser->parseRessourceList($ssh->exec(
                "cat /proc/meminfo; echo '====='; "                               // RAM + Swap in kB, danach Trenner
                . "df -P -B1 /; echo '====='; "                                             // Root-Dateisystem in Byte (-B1), POSIX-Format (-P), danach Trenner
                . "date +%s%N; grep usage_usec /sys/fs/cgroup/cpu.stat; sleep 1; "          // Zeit 1 (ns) + CPU-Zähler 1 (µs seit Start), 1 s warten
                . "date +%s%N; grep usage_usec /sys/fs/cgroup/cpu.stat; "                   // Zeit 2 (ns) + CPU-Zähler 2 → Differenz ergibt Auslastung
                . "nproc; cat /sys/fs/cgroup/cpu.max; echo '====='; "                       // Kernzahl + CPU-Kontingent ("max ..." = kein Limit → nproc gilt)
                . "cat /proc/uptime; echo '====='; "                                        // Uptime
                . "cat /etc/os-release"                                                     // OS-Release
            ));
            dd($ressource);
        } catch (\Throwable $exception) {
            $output->writeln("<error>Device '" . $device->getName() . "' failed: " . $exception->getMessage() . "</error>");
            return false;
        } finally {
            $this->closeConnection($ssh, $output, $host);
            $output->writeln("Device resources for '" . ($device->getName() ?? $host) . "' received successfully.");

            return $ressource ?? false;
        }
    }

    private function closeConnection(?SSH2 $ssh, OutputInterface $output, string $host): void
    {
        $ssh?->disconnect();
        $output->writeln("Connection closed for host: " . $host);
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
