<?php

namespace App\Controller;

use App\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\NewsRepository;

class NewsController extends AbstractController
{
    private $entityManager;
    
    private $newsRepository;

    public function __construct(
        EntityManagerInterface $entityManager, 
        NewsRepository $newsRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->newsRepository = $newsRepository;
    }

    /**
     * @Route("/api/add-news", name="api_add_news", methods={"POST"})
     */
    public function addNews(Request $request): JsonResponse
    {
        
        $name = $request->request->get('name');
        $link = $request->request->get('link');
        $img = $request->files->get('img');
        $tekst = $request->request->get('tekst');

        
        $news = new News();
        $news->setName($name);
        $news->setLink($link);
        
        if ($img) {
           
            $filename = md5(uniqid()) . '.' . $img->guessExtension();
            $img->move(
                $this->getParameter('news_directory'),
                $filename
            );
            $news->setImg($filename);
        }
        $news->setTekst($tekst);

        
        $this->entityManager->persist($news);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
        ]);
    }

    /**
     * @Route("/api/news", name="api_get_news", methods={"GET"})
     */
    public function getNews(): JsonResponse
    {
        $news = $this->newsRepository->findAll();

        $formattedNews = [];
        foreach ($news as $item) {
            $formattedNews[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'link' => $item->getLink(),
                'img' => $item->getImg(),
                'tekst' => $item->getTekst(),
            ];
        }

        return $this->json($formattedNews);
    }
}