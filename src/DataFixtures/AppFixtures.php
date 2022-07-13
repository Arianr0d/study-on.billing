<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHash;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHash = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Супер-пользователь
        $userSuperAdmin = new User();
        $userSuperAdmin->setEmail('admin@study-on-billing.ru');
        $userPasswordHash = $this->passwordHash->hashPassword(
            $userSuperAdmin,
            'passwordUserAdmin'
        );
        $userSuperAdmin->setPassword($userPasswordHash);
        $userSuperAdmin->setRoles(['ROLE_SUPER_ADMIN']);
        $manager->persist($userSuperAdmin);

        // Обычный пользователь
        $userUsual = new User();
        $userUsual->setEmail('user@study-on-billing.ru');
        $userPasswordHash = $this->passwordHash->hashPassword(
            $userUsual,
            'passwordUser'
        );
        $userUsual->setPassword($userPasswordHash);
        $userUsual->setRoles(['ROLE_USER']);
        $manager->persist($userUsual);

        $manager->flush();
    }
}
