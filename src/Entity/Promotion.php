<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\PromotionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PromotionRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['promotion:read']],
    denormalizationContext: ['groups' => ['promotion:write']],
    operations: [
        new Get(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seuls les administrateurs peuvent créer des promotions.'
        ),
        new GetCollection(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seuls les administrateurs peuvent créer des promotions.'
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seuls les administrateurs peuvent créer des promotions.'
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seuls les administrateurs peuvent modifier les promotions.'
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seuls les administrateurs peuvent supprimer les promotions.'
        )
    ]
)]
#[ORM\HasLifecycleCallbacks]
class Promotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['promotion:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Groups(['promotion:read', 'promotion:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 1000,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Groups(['promotion:read', 'promotion:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    ##[Assert\NotBlank(message: "Le slug ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le slug ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[a-z0-9]+(?:-[a-z0-9]+)*$/",
        message: "Le slug doit contenir uniquement des lettres minuscules, des chiffres et des tirets."
    )]
    #[Groups(['promotion:read', 'promotion:write'])]
    private ?string $slug = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le pourcentage de réduction ne peut pas être nul.")]
    #[Assert\PositiveOrZero(message: "Le pourcentage de réduction doit être positif ou zéro.")]
    #[Assert\LessThanOrEqual(
        value: 100,
        message: "Le pourcentage de réduction ne peut pas dépasser 100%."
    )]
    #[Groups(['promotion:read', 'promotion:write'])]
    private ?float $discountPercentage = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "La date de début ne peut pas être nulle.")]
    #[Assert\Type("\DateTimeInterface")]
    #[Groups(['promotion:read', 'promotion:write'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "La date de fin ne peut pas être nulle.")]
    #[Assert\Type("\DateTimeInterface")]
    #[Assert\Expression(
        "this.getEndDate() > this.getStartDate()",
        message: "La date de fin doit être postérieure à la date de début."
    )]
    #[Groups(['promotion:read', 'promotion:write'])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column]
    #[Assert\Type("\DateTimeImmutable")]
    #[Groups(['promotion:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\Type("\DateTimeImmutable")]
    #[Groups(['promotion:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'promotions')]
    #[Assert\NotNull(message: "L'habitat associé ne peut pas être nul.")]
    #[Groups(['promotion:read', 'promotion:write'])]
    private ?Habitat $habitat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDiscountPercentage(): ?float
    {
        return $this->discountPercentage;
    }

    public function setDiscountPercentage(float $discountPercentage): static
    {
        $this->discountPercentage = $discountPercentage;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

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
