<?php

namespace App\Controller;

use App\Entity\Gallery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\GalleryRepository;

class GalleryController extends AbstractController
{
     /**
     * @var EntityManager
     */
    private $entityManager;

   /**
     * @var UserRepository
     */
    private $galleryRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        GalleryRepository $galleryRepository,
    )
    {
        $this->entityManager = $entityManager;
        $this->galleryRepository = $galleryRepository;
    }

    /**
     * @Route("/api/gallery/add", name="add_gallery", methods={"POST"})
     */
    public function addGallery(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $gallery = $this->galleryRepository->findOneBy(['name' => $data['name']]);

        if (!$gallery) {
            $gallery = new Gallery();
            $gallery->setName($data['name']);
        } else {
            return $this->json(['message' => 'Gallery is already in database']);
        }

        // Perform the necessary actions to add the gallery to the database or any other storage
        $this->entityManager->persist($gallery);
        $this->entityManager->flush();
        return $this->json(['message' => 'Gallery added successfully']);

        return new JsonResponse(['success' => true]);
    }

     /**
     * @Route("/api/gallery", name="api_gallery_list", methods={"GET"})
     */
    public function getGalleryList(): JsonResponse
    {
        $galleries = $this->galleryRepository->findAll();
        $galleryList = [];

        foreach ($galleries as $gallery) {
            $galleryList[] = [
                'id' => $gallery->getId(),
                'name' => $gallery->getName(),
            ];
        }

        return new JsonResponse($galleryList);
    }
}