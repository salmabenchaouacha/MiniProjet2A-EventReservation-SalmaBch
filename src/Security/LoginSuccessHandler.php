<?php

namespace App\Security;

use App\Entity\RefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private RefreshTokenManagerInterface $refreshTokenManager,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        $user = $token->getUser();

        $jwt = $this->jwtManager->create($user);

        $refreshTokenValue = bin2hex(random_bytes(64));

        $refreshToken = new RefreshToken();
        $refreshToken->setRefreshToken($refreshTokenValue);
        $refreshToken->setUsername($user->getUserIdentifier());
        $refreshToken->setValid((new \DateTime())->modify('+30 days'));

        $this->refreshTokenManager->save($refreshToken);

        return new JsonResponse([
            'token' => $jwt,
            'refresh_token' => $refreshToken->getRefreshToken(),
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'fullName' => $user->getFullName(),
            ],
        ]);
    }
}