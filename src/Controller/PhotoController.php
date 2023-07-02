<?php

namespace App\Controller;

use App\Entity\Photo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PhotoRepository;
use App\Repository\GalleryRepository;

class PhotoController extends AbstractController
{
    private $entityManager;
    private $photoRepository;
    private $galleryRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        PhotoRepository $photoRepository,
        GalleryRepository $galleryRepository
    ) {
        $this->entityManager = $entityManager;
        $this->photoRepository = $photoRepository;
        $this->galleryRepository = $galleryRepository;
    }

    /**
     * @Route("/api/photos", methods={"POST"})
     */
    public function addPhoto(Request $request, int $galleryId): JsonResponse
    {
        $name = $request->request->get('name');
        $imgFile = $request->files->get('img');
    
        if ($imgFile instanceof UploadedFile) {
            $imgFileName = uniqid().'.'.$imgFile->getClientOriginalExtension();
            $imgFile->move($this->getParameter('photos_directory'), $imgFileName);
    
            $gallery = $this->galleryRepository->find($galleryId);
    
            if (!$gallery) {
                return $this->json(['message' => 'Gallery not found'], 404);
            }
    
            $photo = new Photo();
            $photo->setName($name);
            $photo->setImg($imgFileName);
            $photo->setGallery($gallery);
    
            $this->entityManager->persist($photo);
            $this->entityManager->flush();
    
            return $this->json(['message' => 'Photo added successfully']);
        }
    
        return $this->json(['message' => 'Invalid file'], 400);
    }

    /**
     * @Route("/api/get_photos", methods={"GET"})
     */
    public function getPhotos(): JsonResponse
    {
        $photos = $this->photoRepository->findAll();
    
        $formattedPhotos = [];
        foreach ($photos as $photo) {
            $formattedPhotos[] = [
                'id' => $photo->getId(),
                'name' => $photo->getName(),
                'img' => $photo->getImg(),
            ];
        }
    
        return $this->json(['photos' => $formattedPhotos]);
    }
}
