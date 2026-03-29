<?php

namespace App\Controller\Api;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Service\PasskeyAuthService;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AuthApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
        private RefreshTokenManagerInterface $refreshTokenManager,
    ) {
    }

    #[Route('/api/auth/register', name: 'api_auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $fullName = trim($data['fullName'] ?? '');

        if (!$email || !$password || !$fullName) {
            return $this->json(['error' => 'Données manquantes.'], 400);
        }

        $existing = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($existing) {
            return $this->json(['error' => 'Email déjà utilisé.'], 409);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFullName($fullName);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Inscription réussie.',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'fullName' => $user->getFullName(),
                'roles' => $user->getRoles(),
            ],
        ], 201);
    }

    #[Route('/api/auth/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(): never
    {
        throw new \LogicException('Cette route est interceptée par le firewall json_login.');
    }

    #[Route('/api/auth/me', name: 'api_auth_me', methods: ['GET'])]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'fullName' => $user->getFullName(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/auth/passkey/register/options', name: 'app_passkey_register_options', methods: ['POST'])]
    public function passkeyRegisterOptions(
        #[CurrentUser] ?User $user,
        PasskeyAuthService $passkeyAuthService
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        return $this->json($passkeyAuthService->getRegistrationOptions($user));
    }

    #[Route('/auth/passkey/register/verify', name: 'app_passkey_register_verify', methods: ['POST'])]
    public function passkeyRegisterVerify(
        #[CurrentUser] ?User $user,
        Request $request,
        PasskeyAuthService $passkeyAuthService
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $payload = json_decode($request->getContent(), true);

        try {
            $passkeyAuthService->verifyRegistration($payload, $user);

            return $this->json([
                'message' => 'Passkey enregistrée avec succès.'
            ]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/auth/passkey/login/options', name: 'api_passkey_login_options', methods: ['POST'])]
    public function passkeyLoginOptions(PasskeyAuthService $passkeyAuthService): JsonResponse
    {
        return $this->json($passkeyAuthService->getLoginOptions());
    }

    #[Route('/api/auth/passkey/login/verify', name: 'api_passkey_login_verify', methods: ['POST'])]
    public function passkeyLoginVerify(
        Request $request,
        PasskeyAuthService $passkeyAuthService
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);

        try {
            $user = $passkeyAuthService->verifyLogin($payload);

            $jwt = $this->jwtManager->create($user);

            $refreshTokenValue = bin2hex(random_bytes(64));

            $refreshToken = new RefreshToken();
            $refreshToken->setRefreshToken($refreshTokenValue);
            $refreshToken->setUsername($user->getUserIdentifier());
            $refreshToken->setValid((new \DateTime())->modify('+30 days'));
            $this->refreshTokenManager->save($refreshToken);

            $response = $this->json([
                'message' => 'Connexion réussie avec passkey.',
                'token' => $jwt,
                'refresh_token' => $refreshToken->getRefreshToken(),
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'fullName' => $user->getFullName(),
                    'roles' => $user->getRoles(),
                ],
            ]);

            $secure = $request->isSecure();

           $response->headers->setCookie(
    Cookie::create(
        'BEARER',
        $jwt,
        new \DateTime('+1 hour'),
        '/',
        null,
        false, // localhost
        true,
        false,
        Cookie::SAMESITE_LAX
    )
);

$response->headers->setCookie(
    Cookie::create(
        'REFRESH_TOKEN',
        $refreshToken->getRefreshToken(),
        new \DateTime('+30 days'),
        '/',
        null,
        false, // localhost
        true,
        false,
        Cookie::SAMESITE_LAX
    )
);

            return $response;
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 401);
        }
    }
}