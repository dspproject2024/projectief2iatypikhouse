<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\AvisRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AvisRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['avis:read']],
    denormalizationContext: ['groups' => ['avis:write']],
    operations: [
        new Get(
            security: "is_granted('ROLE_USER') or object.getUser() == user",
            securityMessage: 'Seuls les utilisateurs authentifiés peuvent accéder à cette ressource.'
        ),
        new GetCollection(),
        new Post(
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Seuls les utilisateurs authentifiés peuvent créer des avis.'
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN') or object.getUser() == user",
            securityMessage: 'Vous ne pouvez modifier que vos propres avis, sauf si vous êtes administrateur.'
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN') or object.getUser() == user",
            securityMessage: 'Seuls les administrateurs ou l\'auteur peuvent supprimer leurs avis.'
        )
    ]
)]
#[ORM\HasLifecycleCallbacks]
class Avis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['avis:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La note ne peut pas être nulle.")]
    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: "La note doit être comprise entre {{ min }} et {{ max }}."
    )]
    #[Groups(['avis:read', 'avis:write'])]
    private ?int $rating = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 1000,
        maxMessage: "Le commentaire ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Groups(['avis:read', 'avis:write'])]
    private ?string $comment = null;

    #[ORM\Column]
    #[Assert\Type("\DateTimeImmutable")]
    #[Groups(['avis:read'])]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column]
    #[Assert\Type("\DateTimeImmutable")]
    #[Groups(['avis:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\Type("\DateTimeImmutable")]
    #[Groups(['avis:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'avis')]
    ##[Assert\NotNull(message: "L'utilisateur associé ne peut pas être nul.")]
    #[Groups(['avis:read', 'avis:write'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'avis')]
    #[Assert\NotNull(message: "L'habitat associé ne peut pas être nul.")]
    #[Groups(['avis:read', 'avis:write'])]
    private ?Habitat $habitat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
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

    public function getHabitat(): ?Habitat
    {
        return $this->habitat;
    }

    public function setHabitat(?Habitat $habitat): static
    {
        $this->habitat = $habitat;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function setPublishedAtValue(): void
    {
        if ($this->publishedAt === null) {
            $this->publishedAt = new \DateTimeImmutable();
        }
    }
}
