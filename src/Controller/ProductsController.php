<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\ProductsRepository;

#[Route('/produits', name: 'products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProductsRepository $productsRepository): Response
    {
        $products = $productsRepository->findAll(); // Récupérer tous les produits

        return $this->render('products/index.html.twig', [
            'products' => $products, // Transmettre la liste des produits au modèle Twig
        ]);
    }

    #[Route('/{slug}', name: 'details')]
    public function details(string $slug, ProductsRepository $productsRepository): Response
    {
        $product = $productsRepository->findOneBy(['slug' => $slug]);

        return $this->render('products/details.html.twig', [
            'product' => $product,
        ]);
    }
}