<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\WebauthnCredentialRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class PasskeyAuthService
{
    public function __construct(
        private RequestStack $requestStack,
        private WebauthnCredentialRepository $credentialRepository,
    ) {
    }

    public function getRegistrationOptions(User $user): array
{
    $challenge = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

    $options = [
        'challenge' => $challenge,
        'rp' => [
    'name' => $_ENV['WEBAUTHN_RP_NAME'] ?? 'Event Reservation App',
],
        'user' => [
            'id' => base64_encode((string) $user->getId()),
            'name' => $user->getEmail(),
            'displayName' => $user->getFullName(),
        ],
        'pubKeyCredParams' => [
            ['alg' => -7, 'type' => 'public-key'],
            ['alg' => -257, 'type' => 'public-key'],
        ],
        'timeout' => 60000,
        'attestation' => 'none',
        'authenticatorSelection' => [
            'residentKey' => 'preferred',
            'userVerification' => 'preferred',
        ],
    ];

    $session = $this->requestStack->getSession();
    $session->set('webauthn_registration', $options);

    return $options;
}

    public function verifyRegistration(array $payload, User $user): void
    {
        $session = $this->requestStack->getSession();
        $options = $session->get('webauthn_registration');

        if (!$options) {
            throw new \RuntimeException('Challenge d’enregistrement introuvable.');
        }

        $credentialId = $payload['id'] ?? null;

        if (!$credentialId) {
            throw new \RuntimeException('Credential ID manquant.');
        }

        $this->credentialRepository->saveCredential(
            $user,
            $credentialId,
            $payload,
            'Passkey '.date('Y-m-d H:i')
        );

        $session->remove('webauthn_registration');
    }

    public function getLoginOptions(): array
{
    $challenge = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

    $options = [
        'challenge' => $challenge,
        'rpId' => 'localhost',
        'timeout' => 60000,
        'userVerification' => 'preferred',
    ];

    $session = $this->requestStack->getSession();
    $session->set('webauthn_login', $options);

    return $options;
}
    public function verifyLogin(array $payload): User
    {
        $session = $this->requestStack->getSession();
        $options = $session->get('webauthn_login');

        if (!$options) {
            throw new \RuntimeException('Challenge de connexion introuvable.');
        }

        $credentialId = $payload['id'] ?? null;

        if (!$credentialId) {
            throw new \RuntimeException('Credential ID manquant.');
        }

        $credential = $this->credentialRepository->findByCredentialId($credentialId);

        if (!$credential) {
            throw new \RuntimeException('Passkey inconnue.');
        }

        $credential->touch();
        $this->credentialRepository->getEntityManager()->flush();

        $session->remove('webauthn_login');

        return $credential->getUser();
    }
}