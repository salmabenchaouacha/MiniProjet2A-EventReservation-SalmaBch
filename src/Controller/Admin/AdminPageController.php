<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminPageController extends AbstractController
{
    #[Route('', name: 'admin_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/events', name: 'admin_events_index', methods: ['GET'])]
    public function eventsIndex(): Response
    {
        return $this->render('admin/event_index.html.twig');
    }

    #[Route('/events/new', name: 'admin_events_new', methods: ['GET'])]
    public function newEvent(): Response
    {
        return $this->render('admin/event_form.html.twig');
    }
}