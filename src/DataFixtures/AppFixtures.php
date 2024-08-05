<?php

namespace App\DataFixtures;

use App\Entity\Avis;
use App\Entity\Category;
use App\Entity\Habitat;
use App\Entity\Image;
use App\Entity\Message;
use App\Entity\Notification;
use App\Entity\Payment;
use App\Entity\Promotion;
use App\Entity\Reservation;
use App\Entity\Role;
use App\Entity\Status;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use function Webmozart\Assert\Tests\StaticAnalysis\length;

class AppFixtures extends Fixture
{
    private Generator $faker;
    private UserPasswordHasherInterface $passwordHasher;
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->faker =Factory::create('fr_FR');
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadRoles($manager);
      $this->loadUsers($manager);
      $this->loadCategories($manager);
      $this->loadHabitats($manager);
           $this->loadStatuses($manager);
            $this->loadReservations($manager);
            $this->loadAvis($manager);
            $this->loadPromotions($manager);
           $this->loadMessages($manager);
         $this->loadImages($manager);
           $this->loadNotifications($manager);
         $this->loadPayment($manager);

    }
    public function loadAvis(ObjectManager $manager): void
    {
        // Récupération des utilisateurs et habitats déjà existants
        $users = $manager->getRepository(User::class)->findAll();
        $habitats = $manager->getRepository(Habitat::class)->findAll();

        for ($i = 0; $i < 100; $i++) {
            $avis = new Avis();
            $avis->setRating($this->faker->numberBetween(1, 5))
                ->setComment($this->faker->paragraph())
                // Convert DateTime to DateTimeImmutable
                ->setPublishedAt(\DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-1 years', 'now')))
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
                ->setUser($this->faker->randomElement($users))
                ->setHabitat($this->faker->randomElement($habitats));

            $manager->persist($avis);
        }

        $manager->flush();
    }
    public function loadStatuses(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Définissez quelques statuts pré-définis que votre application pourrait utiliser
        $statusTitles = ['Pending', 'Confirmed', 'Cancelled', 'Completed'];

        foreach ($statusTitles as $title) {
            $status = new Status();
            $status->setTitle($title)
                ->setDescription($faker->sentence())
                ->setSlug($faker->slug())
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($status);
        }

        // Ajoutez quelques statuts supplémentaires générés par Faker
        for ($i = 0; $i < 5; $i++) {
            $status = new Status();
            $status->setTitle($faker->word())
                ->setDescription($faker->paragraph())
                ->setSlug($faker->slug())
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($status);
        }

        $manager->flush();
    }
    public function loadRoles(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $roleNames = ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGER'];

        // Génération de rôles pré-définis et factices
        foreach ($roleNames as $roleName) {
            $role = new Role();
            $role->setRoleName($roleName)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($role);
        }

        $manager->flush();
    }
    public function loadReservations(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Supposons que vous avez déjà des entités User, Habitat, et Status en base de données
        $users = $manager->getRepository(User::class)->findAll();
        $habitats = $manager->getRepository(Habitat::class)->findAll();
        $statuses = $manager->getRepository(Status::class)->findAll();

        // Génération de 30 réservations factices
        for ($i = 0; $i < 30; $i++) {
            $reservation = new Reservation();

            // Génération de dates de début et de fin
            $startDate = $faker->dateTimeBetween('-1 months', '+1 months');
            $endDate = (clone $startDate)->modify('+' . mt_rand(1, 14) . ' days');

            $reservation->setStartDate($startDate)
                ->setEndDate($endDate)
                ->setTotalPrice($faker->randomFloat(2, 100, 2000))
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
                ->setUser($faker->randomElement($users))
                ->setHabitat($faker->randomElement($habitats))
                ->setStatus($faker->randomElement($statuses));

            $manager->persist($reservation);
        }

        $manager->flush();
    }
    public function loadPromotions(ObjectManager $manager): void
    {
        // Retrieve existing habitats from the database
        $habitats = $manager->getRepository(Habitat::class)->findAll();

        // Ensure there are habitats to associate with promotions
        if (count($habitats) === 0) {
            throw new \Exception("No habitats found. Please load Habitat fixtures first.");
        }

        // Generate 20 fake promotions
        for ($i = 0; $i < 20; $i++) {
            $promotion = new Promotion();
            $promotion->setTitle($this->faker->catchPhrase())
                ->setDescription($this->faker->paragraph())
                ->setSlug($this->faker->slug())
                ->setDiscountPercentage($this->faker->randomFloat(2, 5, 50))
                ->setStartDate($this->faker->dateTimeBetween('-1 months', '+1 months'))
                ->setEndDate($this->faker->dateTimeBetween('+1 months', '+6 months'))
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
                ->setHabitat($this->faker->randomElement($habitats));

            $manager->persist($promotion);
        }

        $manager->flush();
    }

    public function loadPayment(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Supposons que vous avez déjà des entités Reservation en base de données
        $reservations = $manager->getRepository(Reservation::class)->findAll();

        // Génération de 30 paiements factices
        for ($i = 0; $i < 30; $i++) {
            $payment = new Payment();
            $payment->setAmount($faker->randomFloat(2, 50, 1000))
                ->setStatus($faker->randomElement(['pending', 'completed', 'failed']))
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
                ->setReservation($faker->randomElement($reservations));

            $manager->persist($payment);
        }

        $manager->flush();
    }


public function loadNotifications(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Assuming you have some User entities already loaded
        // Retrieve these users for associating with notifications
        $users = $manager->getRepository(User::class)->findAll();

        // Génération de 20 notifications factices
        for ($i = 0; $i < 20; $i++) {
            $notification = new Notification();
            $notification->setMessage($faker->sentence())
                ->setRead($faker->boolean())
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUser($faker->randomElement($users));

            $manager->persist($notification);
        }

        $manager->flush();
    }
    public function loadMessages(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Supposons que vous ayez déjà quelques utilisateurs pour assigner les messages
        $users = $manager->getRepository(User::class)->findAll();

        if (count($users) < 2) {
            throw new \Exception("Please ensure there are at least two users in the database.");
        }

        // Génération de 20 messages factices
        for ($i = 0; $i < 20; $i++) {
            $message = new Message();
            $sender = $faker->randomElement($users);
            $receiver = $faker->randomElement(array_filter($users, fn($user) => $user !== $sender));

            $message->setContent($faker->text(200))
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
                ->setSender($sender)
                ->setReceiver($receiver);

            $manager->persist($message);
        }

        $manager->flush();
    }
    public function loadImages(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Génération de 10 images factices
        for ($i = 0; $i < 10; $i++) {
            $image = new Image();
            $image->setUrl($faker->imageUrl(640, 480, 'business', true, 'Faker'))
                ->setDescription($faker->sentence())
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($image);
        }

        $manager->flush();
    }
    public function loadUsers (ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Génération de 10 utilisateurs factices
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->safeEmail())
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setPhoneNumber($faker->phoneNumber())
                ->setActive($faker->boolean())
                ->setRoles(['ROLE_USER'])
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable());

            // Hashing the password
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                'password' // Example password
            );
            $user->setPassword($hashedPassword);

            $manager->persist($user);
        }

        $manager->flush();
    }
    public function loadCategories(ObjectManager $manager): void
    {
        for ($i = 0; $i < 50; $i++) {
            $category = new Category();
            $category->setTitle($this->faker->realText(30));
            $category->setDescription($this->faker->realText(100));
            $category->setSlug($this->faker->realText());
            $category->setCreatedAt(new \DateTimeImmutable());
            $category->setUpdatedAt(new \DateTimeImmutable());
            $this->setReference("category_$i",$category);
            $manager->persist($category);
        }
        $manager->flush();
    }

    public function loadHabitats(ObjectManager $manager): void
    {
        // Suppose that users and categories are already loaded
        $users = $manager->getRepository(User::class)->findAll();
        $categories = $manager->getRepository(Category::class)->findAll();

        // Suppose this is your part of the fixture
        $amenities = [$this->faker->word(), $this->faker->word(), $this->faker->word()];
        // Ensure $amenities is always an array before using join/implode

        $availability = [$this->faker->randomElement([
            '1 day', '2 days', '3 days', '4 days'
        ])];

        for ($i = 0; $i < 10; $i++) {
            $habitat = new Habitat();
            $habitat->setTitle($this->faker->realText(50))
                ->setDescription($this->faker->realText(100))
                ->setSlug($this->faker->slug())
                ->setLocation($this->faker->address)
                ->setPricePerNight($this->faker->randomFloat(2, 50, 300))
                ->setAmenities($amenities)
                ->setAvailability($availability)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
                ->setOwner($this->faker->randomElement($users))
                ->setCategory($this->faker->randomElement($categories))
                ->setMaxGuests($this->faker->numberBetween(1, 10));

            $manager->persist($habitat);
        }

        $manager->flush();
    }



}
