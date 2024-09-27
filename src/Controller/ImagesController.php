<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Habitat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ImagesController extends AbstractController
{
    #[Route('/api/upload-images', name: 'upload_images', methods: ['POST'])]
    public function uploadImages(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $files = $request->files->get('images');
        $habitatId = $request->request->get('habitat_id');

        if (!$files || !$habitatId) {
            return new JsonResponse(['error' => 'Images or habitat_id missing'], Response::HTTP_BAD_REQUEST);
        }

        // Trouver l'habitat associé
        $habitat = $entityManager->getRepository(Habitat::class)->find($habitatId);
        if (!$habitat) {
            return new JsonResponse(['error' => 'Habitat non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $urls = [];
        foreach ($files as $file) {
            $fileName = uniqid() . '.' . $file->guessExtension();
            $file->move($this->getParameter('uploads_directory'), $fileName);
            $url = $request->getSchemeAndHttpHost() . '/uploads/images/' . $fileName;


            // Créer une nouvelle entité Image
            $image = new Image();
            $image->setUrl($url);
            $image->setHabitat($habitat);

            // Sauvegarder l'image dans la base de données
            $entityManager->persist($image);
            $urls[] = $url;
        }

        $entityManager->flush();

        return new JsonResponse(['urls' => $urls], Response::HTTP_OK);
    }

    #[Route('/uploads/images/{filename}', name: 'uploads_image', methods: ['GET'])]
    public function serveImage(Request $request, string $filename): Response
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/images/' . $filename;

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Image not found');
        }

        return new Response(file_get_contents($filePath), 200, [
            'Content-Type' => mime_content_type($filePath)
        ]);
    }
}
