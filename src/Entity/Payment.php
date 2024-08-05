<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['payment:read']],
    denormalizationContext: ['groups' => ['payment:write']],
    operations: [
        new Get(
            security: "is_granted('ROLE_USER') and object.getReservation().getUser() == user",
            securityMessage: 'Vous ne pouvez accéder qu\'à vos propres paiements.',
            normalizationContext: ['groups' => ['payment:read']]
        ),
        new GetCollection(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seuls les administrateurs peuvent voir la liste des paiements.',
            normalizationContext: ['groups' => ['payment:read']]
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seuls les administrateurs peuvent créer des paiements.',
            denormalizationContext: ['groups' => ['payment:write']]
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seuls les administrateurs peuvent modifier les paiements.',
            denormalizationContext: ['groups' => ['payment:write']]
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seuls les administrateurs peuvent supprimer les paiements.'
        )
    ]
)]
#[ORM\HasLifecycleCallbacks]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['payment:read'])]
    private ?int $id = null;

    #[ORM\Column]
    ##[Assert\NotNull(message: "Le montant ne peut pas être nul.")]
    #[Assert\Positive(message: "Le montant doit être un nombre positif.")]
    #[Groups(['payment:read', 'payment:write'])]
    private ?float $amount = null;

    #[ORM\Column(length: 255)]
    ##[Assert\NotBlank(message: "Le statut ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le statut ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Groups(['payment:read', 'payment:write'])]
    private ?string $status = null;

    #[ORM\Column]
    #[Assert\Type("\DateTimeImmutable")]
    #[Groups(['payment:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\Type("\DateTimeImmutable")]
    #[Groups(['payment:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[Assert\NotNull(message: "La réservation associée ne peut pas être nulle.")]
    #[Groups(['payment:read', 'payment:write'])]
    private ?Reservation $reservation = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[Groups(['payment:read', 'payment:write'])]
    private ?Status $valide = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): static
    {
        $this->reservation = $reservation;

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

    public function getValide(): ?Status
    {
        return $this->valide;
    }

    public function setValide(?Status $valide): static
    {
        $this->valide = $valide;

        return $this;
    }
}
