<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\HabitatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HabitatRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['habitat:read']],
    denormalizationContext: ['groups' => ['habitat:write']],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_OWNER')",
            securityMessage: 'Seuls les administrateurs ou les propriétaires peuvent créer de nouveaux habitats.',
            denormalizationContext: ['groups' => ['habitat:write']]
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN') or object.getOwner() == user",
            securityMessage: 'Seuls les administrateurs ou le propriétaire peuvent modifier cet habitat.',
            denormalizationContext: ['groups' => ['habitat:write']]
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN') or object.getOwner() == user",
            securityMessage: 'Seuls les administrateurs ou le propriétaire peuvent supprimer cet habitat.'
        )
    ]
)]
#[ORM\HasLifecycleCallbacks]
class Habitat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['habitat:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Groups(['habitat:read', 'habitat:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    #[Groups(['habitat:read', 'habitat:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le slug ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[a-z0-9]+(?:-[a-z0-9]+)*$/",
        message: "Le slug doit contenir uniquement des lettres minuscules, des chiffres et des tirets."
    )]
    #[Groups(['habitat:read', 'habitat:write'])]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La localisation ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "La localisation ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Groups(['habitat:read', 'habitat:write'])]
    private ?string $location = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La ville ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "La ville ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Groups(['habitat:read', 'habitat:write'])]
    private ?string $city = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le pays ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le pays ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Groups(['habitat:read', 'habitat:write'])]
    private ?string $country = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le prix par nuit ne peut pas être nul.")]
    #[Assert\Positive(message: "Le prix par nuit doit être un nombre positif.")]
    #[Groups(['habitat:read', 'habitat:write'])]
    private ?float $pricePerNight = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le nombre maximum d'invités ne peut pas être nul.")]
    #[Assert\Positive(message: "Le nombre maximum d'invités doit être un nombre positif.")]
    #[Groups(['habitat:read', 'habitat:write'])]
    private ?int $maxGuests = null;

    #[ORM\Column(type: "json")]
    #[Assert\NotNull(message: "Les équipements ne peuvent pas être nuls.")]
    #[Groups(['habitat:read', 'habitat:write'])]
    private array $amenities = [];

    #[ORM\Column(type: "json")]
    #[Assert\NotNull(message: "La disponibilité ne peut pas être nulle.")]
    #[Groups(['habitat:read', 'habitat:write'])]
    private array $availability = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Url(message: "L'URL de l'image doit être valide.")]
    #[Groups(['habitat:read', 'habitat:write'])]
    private ?string $url = null;    

    #[ORM\Column]
    #[Assert\Type("\DateTimeImmutable")]
    #[Groups(['habitat:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\Type("\DateTimeImmutable")]
    #[Groups(['habitat:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'habitats')]
    #[Groups(['habitat:read', 'habitat:write'])]
    private ?User $owner = null;

    #[ORM\ManyToOne(inversedBy: 'habitats')]
    #[Assert\NotNull(message: "La catégorie ne peut pas être nulle.")]
    #[Groups(['habitat:read', 'habitat:write'])]
    private ?Category $category = null;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'habitat')]
    #[Groups(['habitat:read'])]
    private Collection $reservations;

    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'habitat')]
    #[Groups(['habitat:read'])]
    private Collection $avis;

    #[ORM\OneToMany(targetEntity: Promotion::class, mappedBy: 'habitat')]
    #[Groups(['habitat:read'])]
    private Collection $promotions;

    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'habitat')]
    #[Groups(['habitat:read'])]
    private Collection $images;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['habitat:read', 'habitat:write'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['habitat:read', 'habitat:write'])]
    private ?\DateTimeInterface $endDate = null;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->avis = new ArrayCollection();
        $this->promotions = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    // Getters and Setters for all fields

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;
        return $this;
    }

    public function getPricePerNight(): ?float
    {
        return $this->pricePerNight;
    }

    public function setPricePerNight(float $pricePerNight): self
    {
        $this->pricePerNight = $pricePerNight;
        return $this;
    }

    public function getMaxGuests(): ?int
    {
        return $this->maxGuests;
    }

    public function setMaxGuests(int $maxGuests): self
    {
        $this->maxGuests = $maxGuests;
        return $this;
    }

    public function getAmenities(): array
    {
        return $this->amenities;
    }

    public function setAmenities(array $amenities): self
    {
        $this->amenities = $amenities;
        return $this;
    }

    public function getAvailability(): array
    {
        return $this->availability;
    }

    public function setAvailability(array $availability): self
    {
        $this->availability = $availability;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setHabitat($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getHabitat() === $this) {
                $reservation->setHabitat(null);
            }
        }

        return $this;
    }

    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): self
    {
        if (!$this->avis->contains($avi)) {
            $this->avis->add($avi);
            $avi->setHabitat($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): self
    {
        if ($this->avis->removeElement($avi)) {
            if ($avi->getHabitat() === $this) {
                $avi->setHabitat(null);
            }
        }

        return $this;
    }

    public function getPromotions(): Collection
    {
        return $this->promotions;
    }

    public function addPromotion(Promotion $promotion): self
    {
        if (!$this->promotions->contains($promotion)) {
            $this->promotions->add($promotion);
            $promotion->setHabitat($this);
        }

        return $this;
    }

    public function removePromotion(Promotion $promotion): self
    {
        if ($this->promotions->removeElement($promotion)) {
            if ($promotion->getHabitat() === $this) {
                $promotion->setHabitat(null);
            }
        }

        return $this;
    }

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setHabitat($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getHabitat() === $this) {
                $image->setHabitat(null);
            }
        }

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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }
}