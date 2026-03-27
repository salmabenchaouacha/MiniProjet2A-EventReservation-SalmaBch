<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/events')]
#[IsGranted('ROLE_ADMIN')]
class EventPageController extends AbstractController
{
    #[Route('/create', name: 'admin_events_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader
    ): Response {
        $title = trim($request->request->get('title', ''));
        $description = trim($request->request->get('description', ''));
        $location = trim($request->request->get('location', ''));
        $seats = (int) $request->request->get('seats', 0);
        $date = $request->request->get('date');

        if (!$title || !$description || !$location || !$date || $seats <= 0) {
            $this->addFlash('error', 'Veuillez remplir tous les champs correctement.');
            return $this->redirectToRoute('admin_events_new');
        }

        $event = new Event();
        $event->setTitle($title);
        $event->setDescription($description);
        $event->setLocation($location);
        $event->setSeats($seats);
        $event->setAvailableSeats($seats);
        $event->setDate(new \DateTime($date));

        $image = $request->files->get('image');
        if ($image) {
            $filename = $fileUploader->upload($image);
            $event->setImage($filename);
        }

        $entityManager->persist($event);
        $entityManager->flush();

        $this->addFlash('success', 'Événement créé avec succès.');

        return $this->redirectToRoute('admin_events_index');
    }

     #[Route('/{id}/edit', name: 'admin_events_edit', methods: ['GET'])]
    public function editForm(Event $event): Response
    {
        return $this->render('admin/event_form.html.twig', [
            'event' => $event,
            'is_edit' => true,
        ]);
    }

    #[Route('/{id}/update', name: 'admin_events_update', methods: ['POST'])]
    public function update(
        Event $event,
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader
    ): Response {
        $title = trim($request->request->get('title', ''));
        $description = trim($request->request->get('description', ''));
        $location = trim($request->request->get('location', ''));
        $seats = (int) $request->request->get('seats', 0);
        $date = $request->request->get('date');

        if (!$title || !$description || !$location || !$date || $seats <= 0) {
            $this->addFlash('error', 'Veuillez remplir tous les champs correctement.');
            return $this->redirectToRoute('admin_events_edit', ['id' => $event->getId()]);
        }

        $oldSeats = $event->getSeats();
        $diff = $seats - $oldSeats;

        $event->setTitle($title);
        $event->setDescription($description);
        $event->setLocation($location);
        $event->setSeats($seats);
        $event->setAvailableSeats(max(0, $event->getAvailableSeats() + $diff));
        $event->setDate(new \DateTime($date));

        $image = $request->files->get('image');
        if ($image) {
            $filename = $fileUploader->upload($image);
            $event->setImage($filename);
        }

        $entityManager->flush();

        $this->addFlash('success', 'Événement mis à jour avec succès.');

        return $this->redirectToRoute('admin_events_index');
    }

    #[Route('/{id}/delete', name: 'admin_events_delete', methods: ['POST'])]
    public function delete(
        Event $event,
        EntityManagerInterface $entityManager
    ): Response {
        $entityManager->remove($event);
        $entityManager->flush();

        $this->addFlash('success', 'Événement supprimé avec succès.');

        return $this->redirectToRoute('admin_events_index');
    }
}