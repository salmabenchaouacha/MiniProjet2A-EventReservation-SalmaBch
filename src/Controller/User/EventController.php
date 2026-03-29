<?php

namespace App\Controller\User;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EventController extends AbstractController
{
    #[Route('/events', name: 'app_events')]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('user/events.html.twig', [
            'events' => $eventRepository->findUpcomingEvents(),
        ]);
    }

       #[Route('/events/{id}', name: 'app_event_show')]
    public function show(int $id, EventRepository $eventRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_user_login');
        }

        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException();
        }

        return $this->render('user/event_show.html.twig', [
            'event' => $event,
        ]);
    }
}