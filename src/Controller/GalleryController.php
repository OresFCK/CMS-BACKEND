<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Entity\Photo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\GalleryRepository;
use App\Repository\PhotoRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;

class GalleryController extends AbstractController
{
    private $entityManager;
    private $photoRepository;
    private $galleryRepository;
    private $parameterBag;


    public function __construct(
        EntityManagerInterface $entityManager,
        GalleryRepository $galleryRepository,
        PhotoRepository $photoRepository,
        ParameterBagInterface $parameterBag
    ) {
        $this->entityManager = $entityManager;
        $this->galleryRepository = $galleryRepository;
        $this->photoRepository = $photoRepository;
        $this->parameterBag = $parameterBag;
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
            return $this->json(['message' => 'Gallery is already in the database']);
        }

        $this->entityManager->persist($gallery);
        $this->entityManager->flush();

        return $this->json(['message' => 'Gallery added successfully']);
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

    /**
     * @Route("/api/gallery/{id}/photos", name="api_gallery_photos", methods={"GET"})
     */
    public function getGalleryPhotos(int $id): JsonResponse
    {
        $gallery = $this->galleryRepository->find($id);

        if (!$gallery) {
            return $this->json(['message' => 'Gallery not found'], 404);
        }

        $photos = $gallery->getPhotos();

        $formattedPhotos = [];
        foreach ($photos as $photo) {
            $formattedPhotos[] = [
                'id' => $photo->getId(),
                'name' => $photo->getName(),
                'img' => $photo->getImg(),
            ];
        }

        return $this->json($formattedPhotos);
    }

    /**
     * @Route("/api/photos/{galleryId}", name="add_photo", methods={"POST"})
     */
    public function addPhoto(Request $request, int $galleryId): JsonResponse
    {
        $gallery = $this->galleryRepository->find($galleryId);

        if (!$gallery) {
            return $this->json(['message' => 'Gallery not found'], Response::HTTP_NOT_FOUND);
        }

        $name = $request->request->get('name');
        $file = $request->files->get('img');

        if (!$name || !$file) {
            return $this->json(['message' => 'Missing name or img field'], Response::HTTP_BAD_REQUEST);
        }

        $uploadsDirectory = $this->parameterBag->get('photos_directory');
        $fileName = md5(uniqid()) . '.' . $file->guessExtension();

        try {
            $file->move($uploadsDirectory, $fileName);

            $photo = new Photo();
            $photo->setName($name);
            $photo->setImg($fileName);
            $photo->setGallery($gallery);

            $this->entityManager->persist($photo);
            $this->entityManager->flush();

            return $this->json(['message' => 'Photo added successfully']);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Error adding photo: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}