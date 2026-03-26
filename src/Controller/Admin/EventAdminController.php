<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/events')]
#[IsGranted('ROLE_ADMIN')]
class EventAdminController extends AbstractController
{
    #[Route('', name: 'api_admin_events_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader
    ): JsonResponse {
        $event = new Event();

        $event->setTitle($request->request->get('title'));
        $event->setDescription($request->request->get('description'));
        $event->setLocation($request->request->get('location'));
        $event->setSeats((int) $request->request->get('seats'));
        $event->setAvailableSeats((int) $request->request->get('seats'));
        $event->setDate(new \DateTime($request->request->get('date')));

        $image = $request->files->get('image');
        if ($image) {
            $filename = $fileUploader->upload($image);
            $event->setImage($filename);
        }

        $entityManager->persist($event);
        $entityManager->flush();

        return $this->json(['message' => 'Événement créé.', 'id' => $event->getId()], 201);
    }

    #[Route('/{id}', name: 'api_admin_events_update', methods: ['POST', 'PUT'])]
    public function update(
        Event $event,
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader
    ): JsonResponse {
        $event->setTitle($request->request->get('title', $event->getTitle()));
        $event->setDescription($request->request->get('description', $event->getDescription()));
        $event->setLocation($request->request->get('location', $event->getLocation()));

        if ($request->request->get('seats')) {
            $newSeats = (int) $request->request->get('seats');
            $diff = $newSeats - $event->getSeats();
            $event->setSeats($newSeats);
            $event->setAvailableSeats(max(0, $event->getAvailableSeats() + $diff));
        }

        if ($request->request->get('date')) {
            $event->setDate(new \DateTime($request->request->get('date')));
        }

        $image = $request->files->get('image');
        if ($image) {
            $filename = $fileUploader->upload($image);
            $event->setImage($filename);
        }

        $entityManager->flush();

        return $this->json(['message' => 'Événement mis à jour.']);
    }

    #[Route('/{id}', name: 'api_admin_events_delete', methods: ['DELETE'])]
    public function delete(Event $event, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($event);
        $entityManager->flush();

        return $this->json(['message' => 'Événement supprimé.']);
    }
}