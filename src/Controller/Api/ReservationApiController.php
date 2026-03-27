<?php

namespace App\Controller\Api;

use App\Entity\Event;
use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

#[Route('/api')]
class ReservationApiController extends AbstractController
{
    #[Route('/reservations', name: 'api_reservations_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $eventId = $data['event_id'] ?? null;
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if (!$eventId || !$name || !$email || !$phone) {
            return $this->json(['error' => 'Données manquantes.'], 400);
        }

        $event = $entityManager->getRepository(Event::class)->find($eventId);

        if (!$event) {
            return $this->json(['error' => 'Événement introuvable.'], 404);
        }

        if ($event->getAvailableSeats() <= 0) {
            return $this->json(['error' => 'Plus de places disponibles.'], 400);
        }

        $reservation = new Reservation();
        $reservation->setEvent($event);
        $reservation->setUser($user);
        $reservation->setName($name);
        $reservation->setEmail($email);
        $reservation->setPhone($phone);

        $event->setAvailableSeats($event->getAvailableSeats() - 1);

        $entityManager->persist($reservation);
        $entityManager->flush();

        try {
            $confirmationEmail = (new TemplatedEmail())
                ->from(new Address('benchaouachasalma8@gmail.com', 'Event Reservation App'))
                ->to($reservation->getEmail())
                ->subject('Confirmation de votre réservation')
                ->htmlTemplate('emails/reservation_confirmation.html.twig')
                ->context([
                    'reservation' => $reservation,
                    'event' => $event,
                ]);

            $mailer->send($confirmationEmail);

            $message = 'Réservation confirmée. Un email de confirmation a été envoyé.';
        } catch (\Exception $e) {
    return $this->json([
        'message' => 'Réservation confirmée, mais l’email de confirmation n’a pas pu être envoyé.',
        'error_detail' => $e->getMessage(),
    ], 500);
}

        return $this->json([
            'message' => $message,
            'reservation' => [
                'id' => $reservation->getId(),
                'name' => $reservation->getName(),
                'email' => $reservation->getEmail(),
                'phone' => $reservation->getPhone(),
                'event' => $event->getTitle(),
            ],
        ], 201);
    }

    #[Route('/admin/events/{id}/reservations', name: 'api_admin_event_reservations', methods: ['GET'])]
    public function reservationsByEvent(Event $event): JsonResponse
    {
        $rows = [];

        foreach ($event->getReservations() as $reservation) {
            $rows[] = [
                'id' => $reservation->getId(),
                'name' => $reservation->getName(),
                'email' => $reservation->getEmail(),
                'phone' => $reservation->getPhone(),
                'createdAt' => $reservation->getCreatedAt()?->format('Y-m-d H:i:s'),
            ];
        }

        return $this->json($rows);
    }
}