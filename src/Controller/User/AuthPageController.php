<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AuthPageController extends AbstractController
{
    #[Route('/login', name: 'app_login_page')]
    public function login(): Response
    {
        return $this->render('user/login.html.twig');
    }

    #[Route('/register', name: 'app_register_page')]
    public function register(): Response
    {
        return $this->render('user/register.html.twig');
    }
}