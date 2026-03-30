<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Reservation;
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
        // ── Users ──────────────────────────────────────────────────────────────
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setFullName('Admin Principal');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        $user1 = new User();
        $user1->setEmail('user@example.com');
        $user1->setFullName('Utilisateur Simple');
        $user1->setRoles(['ROLE_USER']);
        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'user123'));
        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail('alice@example.com');
        $user2->setFullName('Alice Dupont');
        $user2->setRoles(['ROLE_USER']);
        $user2->setPassword($this->passwordHasher->hashPassword($user2, 'alice123'));
        $manager->persist($user2);

        $user3 = new User();
        $user3->setEmail('bob@example.com');
        $user3->setFullName('Bob Martin');
        $user3->setRoles(['ROLE_USER']);
        $user3->setPassword($this->passwordHasher->hashPassword($user3, 'bob123'));
        $manager->persist($user3);

        // ── Events (from your SQL) ─────────────────────────────────────────────
        $eventsData = [
            [
                'title'       => 'Festival Jazz',
                'description' => 'Jazz en plein air',
                'date'        => new \DateTime('2026-06-15 20:00:00'),
                'location'    => 'Lyon',
                'seats'       => 50,
                'image'       => 'https://images.pexels.com/photos/1343331/pexels-photo-1343331.jpeg',
            ],
            [
                'title'       => 'Expo Art Moderne',
                'description' => "Exposition d'art moderne",
                'date'        => new \DateTime('2026-06-28 10:00:00'),
                'location'    => 'Marseille',
                'seats'       => 30,
                'image'       => 'https://images.pexels.com/photos/35241388/pexels-photo-35241388.jpeg',
            ],
            [
                'title'       => 'Spectacle Théâtre',
                'description' => 'Comédie drôle et captivante',
                'date'        => new \DateTime('2026-07-05 21:00:00'),
                'location'    => 'Toulouse',
                'seats'       => 75,
                'image'       => 'https://images.pexels.com/photos/7697343/pexels-photo-7697343.jpeg',
            ],
            [
                'title'       => 'Conférence Tech',
                'description' => 'Dernières innovations technologiques',
                'date'        => new \DateTime('2026-08-01 09:00:00'),
                'location'    => 'Nice',
                'seats'       => 200,
                'image'       => 'https://images.pexels.com/photos/34774354/pexels-photo-34774354.jpeg',
            ],
            [
                'title'       => 'Concert Jazz en Live',
                'description' => 'Soirée jazz avec des musiciens renommés.',
                'date'        => new \DateTime('2026-08-12 20:30:00'),
                'location'    => 'Lyon',
                'seats'       => 120,
                'image'       => 'https://images.pexels.com/photos/5657267/pexels-photo-5657267.jpeg',
            ],
        ];

        $events = [];
        foreach ($eventsData as $data) {
            $event = new Event();
            $event->setTitle($data['title']);
            $event->setDescription($data['description']);
            $event->setDate($data['date']);
            $event->setLocation($data['location']);
            $event->setSeats($data['seats']);
            $event->setAvailableSeats($data['seats']);
            $event->setImage($data['image']);
            $manager->persist($event);
            $events[] = $event;
        }

        // ── Reservations ───────────────────────────────────────────────────────
        $reservationsData = [
            // Festival Jazz
            ['name' => 'Alice Dupont',      'email' => 'alice@example.com', 'phone' => '99990500', 'event' => $events[0], 'user' => $user2],
            ['name' => 'Bob Martin',        'email' => 'bob@example.com',   'phone' => '99957373', 'event' => $events[0], 'user' => $user3],

            // Expo Art Moderne

            // Spectacle Théâtre
            ['name' => 'Alice Dupont',      'email' => 'alice@example.com', 'phone' => '26136666', 'event' => $events[2], 'user' => $user2],
            ['name' => 'Bob Martin',        'email' => 'bob@example.com',   'phone' => '56565854', 'event' => $events[2], 'user' => $user3],

            // Conférence Tech
            ['name' => 'Bob Martin',        'email' => 'bob@example.com',   'phone' => '95956231', 'event' => $events[3], 'user' => $user3],

            // Concert Jazz en Live
            ['name' => 'Alice Dupont',      'email' => 'alice@example.com', 'phone' => '21216633', 'event' => $events[4], 'user' => $user2],
        ];

        foreach ($reservationsData as $data) {
            $reservation = new Reservation();
            $reservation->setName($data['name']);
            $reservation->setEmail($data['email']);
            $reservation->setPhone($data['phone']);
            $reservation->setEvent($data['event']);
            $reservation->setUser($data['user']);
           

            // Decrement available seats
            $data['event']->setAvailableSeats(
                $data['event']->getAvailableSeats() - 1
            );

            $manager->persist($reservation);
        }

        $manager->flush();
    }
}