<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['status:read']],
    denormalizationContext: ['groups' => ['status:write']],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Seuls les administrateurs peuvent créer de nouveaux statuts."
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Seuls les administrateurs peuvent modifier les statuts."
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Seuls les administrateurs peuvent supprimer des statuts."
        )
    ]
)]
#[ORM\HasLifecycleCallbacks]
class Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['status:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Groups(['status:read', 'status:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 1000,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Groups(['status:read', 'status:write'])]
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
    #[Groups(['status:read', 'status:write'])]
    private ?string $slug = null;

    #[ORM\Column]
    #[Assert\Type("\DateTimeImmutable")]
    #[Groups(['status:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\Type("\DateTimeImmutable")]
    #[Groups(['status:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'status')]
    #[Groups(['status:read'])]
    private Collection $reservations;

    /**
     * @var Collection<int, Payment>
     */
    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'valide')]
    private Collection $payments;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setStatus($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getStatus() === $this) {
                $reservation->setStatus(null);
            }
        }

        return $this;
  }

//
//    #[ORM\PrePersist]
//    public function setCreatedAtValue(): void
//    {
//        if ($this->createdAt === null) {
//            $this->createdAt = new \DateTimeImmutable();
//        }
//    }
//
//    #[ORM\PrePersist]
//    #[ORM\PreUpdate]
//    public function setUpdatedAtValue(): void
//    {
//        $this->updatedAt = new \DateTimeImmutable();
//    }

/**
 * @return Collection<int, Payment>
 */
public function getPayments(): Collection
{
    return $this->payments;
}

public function addPayment(Payment $payment): static
{
    if (!$this->payments->contains($payment)) {
        $this->payments->add($payment);
        $payment->setValide($this);
    }

    return $this;
}

public function removePayment(Payment $payment): static
{
    if ($this->payments->removeElement($payment)) {
        // set the owning side to null (unless already changed)
        if ($payment->getValide() === $this) {
            $payment->setValide(null);
        }
    }

    return $this;
}
}
