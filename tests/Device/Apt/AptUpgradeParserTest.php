<?php

namespace App\Tests\Device\Apt;

use App\Device\Apt\AptUpgradeParser;
use PHPUnit\Framework\TestCase;

class AptUpgradeParserTest extends TestCase
{
    public function test_empty_output_returns_no_packages(): void
    {
        $parser = new AptUpgradeParser();
        static::assertSame(0, $parser->parseUpdateList("")->getCount());
    }

    public function test_only_conf_lines_return_no_packages(): void
    {
        $data = <<<EOT
      Conf paket-a (1.1 Ubuntu:24.04/noble-updates [amd64])
      Conf paket-b (2.1 Ubuntu:24.04/noble-security [amd64])
      EOT;

        $parser = new AptUpgradeParser();
        static::assertSame(0, $parser->parseUpdateList($data)->getCount());
    }

    public function test_non_inst_lines_are_ignored(): void
    {
        $data = <<<EOT
      Inst gcc-14-base [14.2.0-4ubuntu2~24.04] (14.2.0-4ubuntu2~24.04.1 Ubuntu:24.04/noble-updates, Ubuntu:24.04/noble-security [amd64]) [libstdc++6:amd64 libgcc-s1:amd64 ]
      Inst paket-a [1.0] (1.1 Ubuntu:24.04/noble-updates [amd64])
      Inst paket-Z (1.1 Ubuntu:24.04/noble-updates [amd64])
      Conf paket-a (1.1 Ubuntu:24.04/noble-updates [amd64])
      Inst paket-b [2.0] (2.1 Ubuntu:24.04/noble-security [amd64])
      Conf paket-b (2.1 Ubuntu:24.04/noble-security [amd64])
      EOT;

        $parser = new AptUpgradeParser();
        $packages = $parser->parseUpdateList($data);
        static::assertSame(4, $packages->getCount());
        static::assertSame("gcc-14-base", $packages->getPackages()[0]->name);
        static::assertSame("Ubuntu:24.04/noble-updates, Ubuntu:24.04/noble-security", $packages->getPackages()[0]->origin);
        static::assertSame("1.0", $packages->getPackages()[1]->oldVersion);
        static::assertSame("1.1", $packages->getPackages()[1]->newVersion);
        static::assertSame(null, $packages->getPackages()[2]->oldVersion);
        static::assertSame("1.1", $packages->getPackages()[2]->newVersion);
    }

    public function test_real_output_noise_is_ignored(): void
    {
        $data = <<<EOT
        Reading package lists...
        Building dependency tree...
        Reading state information...
        Calculating upgrade...
        The following packages will be upgraded:
          paket-a
        1 upgraded, 0 newly installed, 0 to remove and 0 not upgraded.
        Inst paket-a [1.0] (1.1 Ubuntu:24.04/noble-updates [amd64])
        Conf paket-a (1.1 Ubuntu:24.04/noble-updates [amd64])
        EOT;

        $packages = (new AptUpgradeParser())->parseUpdateList($data);

        static::assertSame(1, $packages->getCount());
        static::assertSame("paket-a", $packages->getPackages()[0]->name);
    }

    public function test_single_inst_line_is_parsed(): void
    {
        $data = "Inst paket-a [1.0] (1.1 Ubuntu:24.04/noble-updates [amd64])";

        $package = (new AptUpgradeParser())->parseUpdateList($data)->getPackages()[0];

        static::assertSame("paket-a", $package->name);
        static::assertSame("1.0", $package->oldVersion);
        static::assertSame("1.1", $package->newVersion);
        static::assertSame("Ubuntu:24.04/noble-updates", $package->origin);
    }

    public function test_mixed_inst_and_conf_lines_are_indexed_from_zero(): void
    {
        $data = <<<EOT
        Inst paket-a [1.0] (1.1 Ubuntu:24.04/noble-updates [amd64])
        Conf paket-a (1.1 Ubuntu:24.04/noble-updates [amd64])
        Inst paket-b [2.0] (2.1 Ubuntu:24.04/noble-security [amd64])
        Conf paket-b (2.1 Ubuntu:24.04/noble-security [amd64])
        EOT;

        $packages = (new AptUpgradeParser())->parseUpdateList($data);

        static::assertSame(2, $packages->getCount());
        static::assertSame("paket-a", $packages->getPackages()[0]->name);
        static::assertSame("paket-b", $packages->getPackages()[1]->name);
    }

    public function test_inst_line_without_old_version(): void
    {
        $data = "Inst paket-neu (1.1 Ubuntu:24.04/noble-updates [amd64])";

        $package = (new AptUpgradeParser())->parseUpdateList($data)->getPackages()[0];

        static::assertSame("paket-neu", $package->name);
        static::assertNull($package->oldVersion);
        static::assertSame("1.1", $package->newVersion);
        static::assertSame("Ubuntu:24.04/noble-updates", $package->origin);
    }

    public function test_trailing_package_list_is_not_part_of_origin(): void
    {
        $data = "Inst gcc-14-base [14.2.0-4ubuntu2~24.04] (14.2.0-4ubuntu2~24.04.1 Ubuntu:24.04/noble-updates [amd64]) [libstdc++6:amd64 libgcc-s1:amd64 ]";

        $package = (new AptUpgradeParser())->parseUpdateList($data)->getPackages()[0];

        static::assertSame("Ubuntu:24.04/noble-updates", $package->origin);
    }

    public function test_trailing_empty_brackets_are_not_part_of_origin(): void
    {
        $data = "Inst paket-a [1.0] (1.1 Ubuntu:24.04/noble-updates [amd64]) []";

        $package = (new AptUpgradeParser())->parseUpdateList($data)->getPackages()[0];

        static::assertSame("Ubuntu:24.04/noble-updates", $package->origin);
    }

    public function test_origin_with_multiple_sources(): void
    {
        $data = "Inst paket-a [1.0] (1.1 Ubuntu:24.04/noble-updates, Ubuntu:24.04/noble-security [amd64])";

        $package = (new AptUpgradeParser())->parseUpdateList($data)->getPackages()[0];

        static::assertSame("Ubuntu:24.04/noble-updates, Ubuntu:24.04/noble-security", $package->origin);
    }
}
