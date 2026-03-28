<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

#[AsEventListener(event: LogoutEvent::class)]
class LogoutListener
{
    public function __construct(private RouterInterface $router)
    {
    }

    public function __invoke(LogoutEvent $event): void
    {
        $response = new RedirectResponse($this->router->generate('app_home'));
        $event->setResponse($response);
    }
}