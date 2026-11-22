<?php

namespace App\Ssh;

use phpseclib4\Crypt\PublicKeyLoader;
use phpseclib4\Net\SSH2;
use PHPUnit\Framework\MockObject\Stub\Exception;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SshConnection
{

    public function __construct(
        #[Autowire(env: "SSH_PATH")]
        private readonly string $path,
    )
    {
    }

    public function connection(string $host, string $user, ?int $port): Exception|SSH2
    {
        $key = PublicKeyLoader::load(file_get_contents($this->path));

        $ssh = new SSH2($host, $port);

        if (!$ssh->login($user, $key)) {
            throw new AuthenticationException("Error login, check user");
        }

        return $ssh;
    }
}
