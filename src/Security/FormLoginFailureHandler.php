<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class FormLoginFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function __construct(private RouterInterface $router)
    {
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        if ($request->hasSession()) {
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
            $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, (string) $request->request->get('email', ''));
        }

        $loginSource = $request->request->get('login_source');

        if ($loginSource === 'user') {
            return new RedirectResponse($this->router->generate('app_user_login'));
        }

        return new RedirectResponse($this->router->generate('app_login'));
    }
}