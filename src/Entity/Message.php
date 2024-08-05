<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['message:read']],
    denormalizationContext: ['groups' => ['message:write']],
    operations: [
        new Get(
            security: "is_granted('ROLE_USER') and (object.getSender() == user or object.getReceiver() == user)",
            securityMessage: 'Vous ne pouvez accéder qu\'à vos propres messages.'
        ),
        new GetCollection(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seuls les utilisateurs authentifiés peuvent voir la liste des messages.'
        ),
        new Post(
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Seuls les utilisateurs authentifiés peuvent envoyer des messages.'
        ),
        new Put(
            security: "is_granted('ROLE_USER') and object.getSender() == user",
            securityMessage: 'Vous ne pouvez modifier que les messages que vous avez envoyés.'
        ),
        new Delete(
            security: "is_granted('ROLE_USER') and object.getSender() == user",
            securityMessage: 'Vous ne pouvez supprimer que les messages que vous avez envoyés.'
        )
    ]
)]
#[ORM\HasLifecycleCallbacks]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['message:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le contenu du message ne peut pas être vide.")]
    #[Groups(['message:read', 'message:write'])]
    private ?string $content = null;

    #[ORM\Column]
    ##[Assert\NotNull(message: "La date de création ne peut pas être nulle.")]
    #[Assert\Type("\DateTimeImmutable")]
    #[Groups(['message:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    ##[Assert\NotNull(message: "La date de mise à jour ne peut pas être nulle.")]
    #[Assert\Type("\DateTimeImmutable")]
    #[Groups(['message:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    ##[Assert\NotNull(message: "L'expéditeur ne peut pas être nul.")]
    #[Groups(['message:read', 'message:write'])]
    private ?User $sender = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    ##[Assert\NotNull(message: "Le destinataire ne peut pas être nul.")]
    #[Groups(['message:read', 'message:write'])]
    private ?User $receiver = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

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

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    public function setReceiver(?User $receiver): static
    {
        $this->receiver = $receiver;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
