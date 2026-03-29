<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/events')]
#[IsGranted('ROLE_ADMIN')]
class EventManagementController extends AbstractController
{
    #[Route('', name: 'admin_events_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('admin/event_index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }

    #[Route('/{id}/reservations', name: 'admin_event_reservations', methods: ['GET'])]
    public function reservations(Event $event, ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findBy(
            ['event' => $event],
            ['id' => 'DESC']
        );

        return $this->render('admin/event_reservations.html.twig', [
            'event' => $event,
            'reservations' => $reservations,
        ]);
    }
}