<?php
namespace App\Controller;

use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home.index', methods: ['GET'])]
    public function index(CategoriesRepository $categoriesRepository ): Response
    {
        return $this->render('home.html.twig', [
            'categories' => $categoriesRepository->findBy([], ['categoryOrder' => 'asc'])
        ]);
    }
}
