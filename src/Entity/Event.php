<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'events')]
#[ORM\HasLifecycleCallbacks]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $location = null;

    #[ORM\Column]
    private ?int $seats = null;

    #[ORM\Column]
    private ?int $availableSeats = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Reservation::class, cascade: ['remove'])]
    private Collection $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }
    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $date): static { $this->date = $date; return $this; }
    public function getLocation(): ?string { return $this->location; }
    public function setLocation(string $location): static { $this->location = $location; return $this; }
    public function getSeats(): ?int { return $this->seats; }
    public function setSeats(int $seats): static { $this->seats = $seats; return $this; }
    public function getAvailableSeats(): ?int { return $this->availableSeats; }
    public function setAvailableSeats(int $availableSeats): static { $this->availableSeats = $availableSeats; return $this; }
    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): static { $this->image = $image; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
}