<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Account\Enum\UserRole;
use App\Account\Service\UserService;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $usersData = [
            ['email' => 'user1@example.com', 'password' => 'password1', 'roles' => [UserRole::User->value]],
            ['email' => 'admin@example.com', 'password' => 'password1', 'roles' => [UserRole::User->value, UserRole::Admin->value]],
        ];

        foreach ($usersData as $data) {
            $user = new User($data['email']);
            $this->userService->setPassword($user, $data['password'], false);
            $user->setRoles($data['roles']);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
