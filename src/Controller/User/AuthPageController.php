<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
class AuthPageController extends AbstractController
{
    #[Route('/register', name: 'app_register_page')]
    public function register(): Response
    {
        return $this->render('user/register.html.twig');
    }
}