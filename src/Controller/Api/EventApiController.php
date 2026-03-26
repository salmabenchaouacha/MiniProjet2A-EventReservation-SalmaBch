<?php

namespace App\Controller\Api;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/events')]
class EventApiController extends AbstractController
{
    #[Route('', name: 'api_events_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): JsonResponse
    {
        $events = $eventRepository->findUpcomingEvents();

        $data = array_map(function (Event $event) {
            return [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'date' => $event->getDate()?->format('Y-m-d H:i:s'),
                'location' => $event->getLocation(),
                'seats' => $event->getSeats(),
                'availableSeats' => $event->getAvailableSeats(),
                'image' => $event->getImage(),
            ];
        }, $events);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'api_events_show', methods: ['GET'])]
    public function show(Event $event): JsonResponse
    {
        return $this->json([
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'date' => $event->getDate()?->format('Y-m-d H:i:s'),
            'location' => $event->getLocation(),
            'seats' => $event->getSeats(),
            'availableSeats' => $event->getAvailableSeats(),
            'image' => $event->getImage(),
        ]);
    }
}