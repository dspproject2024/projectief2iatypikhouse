<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['notification:read']],
    denormalizationContext: ['groups' => ['notification:write']],
    operations: [
        new Get(
            security: "is_granted('ROLE_USER') and object.getUser() == user",
            securityMessage: 'Vous ne pouvez accéder qu\'à vos propres notifications.'
        ),
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Seuls les utilisateurs authentifiés peuvent voir la liste des notifications.'
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seuls les administrateurs peuvent créer des notifications.'
        ),
        new Put(
            security: "is_granted('ROLE_USER') and object.getUser() == user",
            securityMessage: 'Vous ne pouvez modifier que vos propres notifications.'
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seuls les administrateurs peuvent supprimer des notifications.'
        )
    ]
)]
#[ORM\HasLifecycleCallbacks]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['notification:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le message de la notification ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le message de la notification ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Groups(['notification:read', 'notification:write'])]
    private ?string $message = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "L'état de lecture ne peut pas être nul.")]
    #[Groups(['notification:read', 'notification:write'])]
    private ?bool $isRead = false;

    #[ORM\Column]
    #[Assert\Type("\DateTimeImmutable")]
    #[Groups(['notification:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[Assert\NotNull(message: "L'utilisateur associé ne peut pas être nul.")]
    #[Groups(['notification:read', 'notification:write'])]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function isRead(): ?bool
    {
        return $this->isRead;
    }

    public function setRead(bool $isRead): static
    {
        $this->isRead = $isRead;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }
}
