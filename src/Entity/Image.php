<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\ImageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['image:read']],
    denormalizationContext: ['groups' => ['image:write']],
    operations: [
        new Get(
            normalizationContext: ['groups' => ['image:read']]
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['image:read']]
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_OWNER') or is_granted('ROLE_MANAGER')",
            securityMessage: 'Seuls les administrateurs, les propriétaires ou les managers peuvent ajouter une image.',
            denormalizationContext: ['groups' => ['image:write']]
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN') or (is_granted('ROLE_OWNER') and object.getHabitat().getOwner() == user) or is_granted('ROLE_MANAGER')",
            securityMessage: 'Seuls les administrateurs ou le propriétaire de l\'habitat peuvent modifier cette image.',
            denormalizationContext: ['groups' => ['image:write']]
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN') or (is_granted('ROLE_OWNER') and object.getHabitat().getOwner() == user) or is_granted('ROLE_MANAGER')",
            securityMessage: 'Seuls les administrateurs ou le propriétaire de l\'habitat peuvent supprimer cette image.'
        )
    ]
)]
#[ORM\HasLifecycleCallbacks]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['image:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'URL de l'image ne peut pas être vide.")]
    #[Assert\Url(message: "L'URL doit être valide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "L'URL ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Groups(['image:read', 'image:write'])]
    private ?string $url = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 1000,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Groups(['image:read', 'image:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\Type("\DateTimeImmutable")]
    #[Groups(['image:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\Type("\DateTimeImmutable")]
    #[Groups(['image:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    #[Assert\NotNull(message: "L'habitat associé ne peut pas être nul.")]
    #[Groups(['image:read', 'image:write'])]
    private ?Habitat $habitat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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
