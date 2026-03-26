<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\WebauthnCredential;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WebauthnCredentialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebauthnCredential::class);
    }

    public function saveCredential(User $user, string $credentialId, array $credentialData, string $name = 'Default passkey'): WebauthnCredential
    {
        $entity = new WebauthnCredential();
        $entity->setUser($user);
        $entity->setCredentialId($credentialId);
        $entity->setCredentialData($credentialData);
        $entity->setName($name);

        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();

        return $entity;
    }

    public function findByCredentialId(string $credentialId): ?WebauthnCredential
    {
        return $this->findOneBy(['credentialId' => $credentialId]);
    }

    public function findAllByUser(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }
}