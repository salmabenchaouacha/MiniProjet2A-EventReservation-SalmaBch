<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class JwtCookieAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private UserProviderInterface $userProvider
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->cookies->has('BEARER');
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $jwt = $request->cookies->get('BEARER');

        if (!$jwt) {
            throw new AuthenticationException('Cookie BEARER manquant.');
        }

        $payload = $this->jwtManager->parse($jwt);

        if (!$payload) {
            throw new AuthenticationException('Impossible de parser le JWT.');
        }

        $identifier = $payload['username'] ?? $payload['email'] ?? null;

        if (!$identifier) {
            throw new AuthenticationException('Aucun identifiant trouvé dans le JWT.');
        }

        return new SelfValidatingPassport(
            new UserBadge(
                $identifier,
                fn (string $userIdentifier) => $this->userProvider->loadUserByIdentifier($userIdentifier)
            )
        );
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}