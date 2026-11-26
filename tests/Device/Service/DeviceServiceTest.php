<?php

namespace App\Tests\Device\Service;

use App\Device\Apt\AptUpgradeParser;
use App\Device\Service\DeviceService;
use App\Entity\Device;
use App\Repository\Device\DeviceRepository;
use App\Ssh\SshConnection;
use phpseclib4\Net\SSH2;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class DeviceServiceTest extends TestCase
{
    private SshConnection $sshConnection;
    private DeviceRepository $deviceRepository;
    private BufferedOutput $output;
    private DeviceService $service;

    protected function setUp(): void
    {
        $this->sshConnection = $this->createStub(SshConnection::class);
        $this->deviceRepository = $this->createStub(DeviceRepository::class);

        // BufferedOutput sammelt die Ausgabe -> mit $this->output->fetch() prüfen
        $this->output = new BufferedOutput();

        $this->service = new DeviceService(
            $this->sshConnection,
            new AptUpgradeParser(),
            $this->deviceRepository,
        );
    }

    public function test_unknown_device_returns_false(): void
    {
        $result = $this->service->syncDeviceUpdates($this->output, "unknown");

        static::assertFalse($result);
        static::assertStringContainsString("No devices found.", $this->output->fetch());
    }

    public function test_offline_device_is_reported_and_returns_false(): void
    {
        $device = new Device("Test", "192.168.2.1", "user");
        $this->deviceRepository->method('findAll')->willReturn([$device]);

        $result = $this->service->syncDeviceUpdates($this->output);

        $this->sshConnection->method('connection')->willThrowException(new \RuntimeException('Connection refused'));
        static::assertFalse($result);
    }

    public function test_wrong_user_is_reported_and_returns_false(): void
    {
        $device = new Device("Php Backend", "192.168.1.215", "test");

        $this->deviceRepository->method('findAll')->willReturn([$device]);

        $result = $this->service->syncDeviceUpdates($this->output);

        $this->sshConnection->method('connection')->willThrowException(new AuthenticationException());
        static::assertFalse($result);
    }

    public function test_one_failing_device_does_not_stop_the_others(): void
    {
        $ssh = $this->createStub(SSH2::class);
        $this->sshConnection->method('connection')->willReturnCallback(function (string $host) use ($ssh): SSH2 {
            if ($host === '192.168.1.216') {
                throw new \RuntimeException('Connection refused');
            }

            return $ssh;
        });
        $ssh->method('exec')->willReturn("Inst paket-a [1.0] (1.1 Ubuntu:24.04/noble-updates [amd64])");
        $device = new Device("Php Backend", "192.168.1.215", "michael", 22);
        $device2 = new Device("Php Backend2", "192.168.1.216", "michael", 22);

        $this->deviceRepository->method('findAll')->willReturn([$device, $device2]);

        $result = $this->service->syncDeviceUpdates($this->output);

        static::assertFalse($result);
        $output = $this->output->fetch();
        static::assertStringContainsString("Device 'Php Backend' has 1 Update(s)", $output);
        static::assertStringContainsString("Device 'Php Backend2' failed: Connection refused", $output);
        static::assertStringContainsString("For 1 devices failed to get update Infos.", $output);
    }

    public function test_reachable_device_returns_true(): void
    {
        $ssh = $this->createStub(SSH2::class);
        $this->sshConnection->method('connection')->willReturn($ssh);
        $ssh->method('exec')->willReturn("Inst paket-a [1.0] (1.1 Ubuntu:24.04/noble-updates [amd64])");
        $device = new Device("Php Backend", "192.168.1.215", "michael", 22);

        $this->deviceRepository->method('findAll')->willReturn([$device]);

        $result = $this->service->syncDeviceUpdates($this->output);

        static::assertTrue($result);
        static::assertStringContainsString("Device 'Php Backend' has 1 Update(s)", $this->output->fetch());
    }
}
