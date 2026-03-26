<?php

namespace App\Entity;

use App\Repository\WebauthnCredentialRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WebauthnCredentialRepository::class)]
#[ORM\Table(name: 'webauthn_credentials')]
#[ORM\HasLifecycleCallbacks]
class WebauthnCredential
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'webauthnCredentials')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: 'text')]
    private ?string $credentialId = null;

    #[ORM\Column(type: 'json')]
    private array $credentialData = [];

    #[ORM\Column(length: 120)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastUsedAt = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function touch(): void
    {
        $this->lastUsedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getCredentialId(): ?string
    {
        return $this->credentialId;
    }

    public function setCredentialId(string $credentialId): static
    {
        $this->credentialId = $credentialId;
        return $this;
    }

    public function getCredentialData(): array
    {
        return $this->credentialData;
    }

    public function setCredentialData(array $credentialData): static
    {
        $this->credentialData = $credentialData;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLastUsedAt(): ?\DateTimeImmutable
    {
        return $this->lastUsedAt;
    }
}