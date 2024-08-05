<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    operations: [
        new Get(
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Seuls les utilisateurs authentifiés peuvent accéder à cette ressource.'
        ),
        new GetCollection(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seuls les administrateurs peuvent voir la liste des utilisateurs.'
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seuls les administrateurs peuvent créer de nouveaux utilisateurs.'
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN') or object == user",
            securityMessage: 'Vous ne pouvez modifier que votre propre compte, sauf si vous êtes administrateur.'
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seuls les administrateurs peuvent supprimer des utilisateurs.'
        )
    ]
)]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['read', 'write'])]
    #[Assert\NotBlank(message: "L'email ne peut pas être vide.")]
    #[Assert\Email(message: "Veuillez entrer un email valide.")]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    #[Groups(['read', 'write'])]
    private array $roles = [];

    #[ORM\Column]
    #[Groups(['write'])]
    #[Assert\NotBlank(message: "Le mot de passe ne peut pas être vide.")]
    #[Assert\Length(
        min: 6,
        max: 4096,
        minMessage: "Le mot de passe doit comporter au moins {{ limit }} caractères.",
        maxMessage: "Le mot de passe ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $password = null;

    #[Groups(['write'])]
    #[Assert\NotBlank(message: "Le mot de passe de confirmation ne peut pas être vide.")]
    #[Assert\Expression(
        "this.getPassword() === this.getReTypePassword()",
        message: "Les mots de passe ne correspondent pas."
    )]
    private ?string $reTypePassword = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['read', 'write'])]
    private ?bool $isActive = true;

    #[ORM\Column(length: 255)]
    #[Groups(['read', 'write'])]
    #[Assert\NotBlank(message: "Le prénom ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le prénom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read', 'write'])]
    #[Assert\NotBlank(message: "Le nom de famille ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le nom de famille ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read', 'write'])]
    #[Assert\NotBlank(message: "Le numéro de téléphone ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le numéro de téléphone ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^\+?[0-9]*$/",
        message: "Le numéro de téléphone ne doit contenir que des chiffres et éventuellement un signe plus au début."
    )]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: Habitat::class, mappedBy: 'owner', cascade: ['persist', 'remove'])]
    #[Groups(['read'])]
    private Collection $habitats;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[Groups(['read'])]
    private Collection $reservations;

    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[Groups(['read'])]
    private Collection $avis;

    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'sender', cascade: ['persist', 'remove'])]
    #[Groups(['read'])]
    private Collection $messages;

    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[Groups(['read'])]
    private Collection $notifications;

    public function __construct()
    {
        $this->habitats = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->avis = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getReTypePassword(): ?string
    {
        return $this->reTypePassword;
    }

    public function setReTypePassword(?string $reTypePassword): void
    {
        $this->reTypePassword = $reTypePassword;
    }

    public function eraseCredentials(): void
    {
        // Clear temporary, sensitive data
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

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

    public function getHabitats(): Collection
    {
        return $this->habitats;
    }

    public function addHabitat(Habitat $habitat): self
    {
        if (!$this->habitats->contains($habitat)) {
            $this->habitats->add($habitat);
            $habitat->setOwner($this);
        }

        return $this;
    }

    public function removeHabitat(Habitat $habitat): self
    {
        if ($this->habitats->removeElement($habitat)) {
            if ($habitat->getOwner() === $this) {
                $habitat->setOwner(null);
            }
        }

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
            $reservation->setUser($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getUser() === $this) {
                $reservation->setUser(null);
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
            $avi->setUser($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): self
    {
        if ($this->avis->removeElement($avi)) {
            if ($avi->getUser() === $this) {
                $avi->setUser(null);
            }
        }

        return $this;
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setSender($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getSender() === $this) {
                $message->setSender(null);
            }
        }

        return $this;
    }

    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
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
}
