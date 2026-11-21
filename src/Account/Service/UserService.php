<?php

declare(strict_types=1);

namespace App\Account\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface      $entityManager,
    )
    {
    }

    public function setPassword(User $user, string $password, bool $flush = true): void
    {
        $user->setHashedPassword($this->passwordHasher->hashPassword($user, $password));

        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
