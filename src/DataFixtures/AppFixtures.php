<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setFullName('Admin Principal');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        $user = new User();
        $user->setEmail('user@example.com');
        $user->setFullName('Utilisateur Simple');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        $manager->persist($user);

        for ($i = 1; $i <= 5; $i++) {
            $event = new Event();
            $event->setTitle("Événement $i");
            $event->setDescription("Description de l’événement $i");
            $event->setLocation("Sousse");
            $event->setDate((new \DateTime())->modify("+$i days"));
            $event->setSeats(100);
            $event->setAvailableSeats(100);

            $manager->persist($event);
        }

        $manager->flush();
    }
}