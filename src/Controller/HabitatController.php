<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Habitat;

class HabitatController
{
    #[Route('/api/habitats', name: 'create_habitat', methods: ['POST'])]
    public function createHabitat(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);  // Récupérer les données JSON envoyées
        if (!$data) {
            return new JsonResponse(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }

        $habitat = new Habitat();
        $habitat->setTitle($data['title']);
        $habitat->setDescription($data['description']);
        $habitat->setSlug($data['slug']);
        $habitat->setLocation($data['location']);
        $habitat->setCity($data['city']);
        $habitat->setCountry($data['country']);
        $habitat->setPricePerNight($data['pricePerNight']);
        $habitat->setMaxGuests($data['maxGuests']);
        $habitat->setAmenities($data['amenities']);
        $habitat->setAvailability($data['availability']);

        // Stocker les URLs des images envoyées via le formulaire JSON
        $habitat->setUrl($data['urls']);  // URLs des images doivent être envoyées ici

        // Sauvegarder l'entité Habitat en base de données
        $entityManager->persist($habitat);
        $entityManager->flush();

        return new JsonResponse(['success' => 'Habitat créé avec succès'], Response::HTTP_CREATED);
    }
}