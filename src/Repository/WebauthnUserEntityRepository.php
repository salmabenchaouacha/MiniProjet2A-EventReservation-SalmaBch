<?php

namespace App\Repository;

use App\Entity\User;
use Webauthn\Bundle\Repository\PublicKeyCredentialUserEntityRepositoryInterface;
use Webauthn\PublicKeyCredentialUserEntity;

class WebauthnUserEntityRepository implements PublicKeyCredentialUserEntityRepositoryInterface
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function findOneByUsername(string $username): ?PublicKeyCredentialUserEntity
    {
        $user = $this->userRepository->findOneBy([
            'email' => $username,
        ]);

        if (!$user instanceof User) {
            return null;
        }

        return $this->createUserEntity($user);
    }

    public function findOneByUserHandle(string $userHandle): ?PublicKeyCredentialUserEntity
    {
        $user = $this->userRepository->find($userHandle);

        if (!$user instanceof User) {
            return null;
        }

        return $this->createUserEntity($user);
    }

    private function createUserEntity(User $user): PublicKeyCredentialUserEntity
    {
        return new PublicKeyCredentialUserEntity(
            $user->getEmail(),
            (string) $user->getId(),
            $user->getFullName()
        );
    }
}