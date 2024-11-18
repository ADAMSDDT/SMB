<?php

namespace App\Controller;
use App\Repository\ProductsRepository;

use App\Entity\Categories;

use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/categories', name: 'categories_')]
class CategoriesController extends AbstractController
{
    #[Route('/{slug}', name: 'list')]
    public function list(Categories $category, ProductsRepository $productsRepository, Request $request): Response
    {
        // On va chercher le numéro de page dans l'url
        $page = $request->query->getInt('page', 1);

        // On va chercher la liste des produits de la catégorie
        $products = $productsRepository->findProductsPaginated($page, $category->getSlug(), 4);

        return $this->render('categories/list.html.twig', compact('category', 'products'));
    }

    #[Route('/search', name: 'search')]
    public function search(Request $request, CategoriesRepository $categoriesRepository): Response
    {
        // Récupérer la chaîne de recherche depuis le formulaire
        $query = $request->query->get('q', '');

        // Rechercher les sous-catégories dont le nom correspond à la chaîne
        $categories = $categoriesRepository->findByNameLike($query);

        // Afficher les résultats dans une vue Twig (ou la même vue de la liste des catégories si tu veux réutiliser)
        return $this->render('categories/search_results.html.twig', [
            'categories' => $categories,
            'query' => $query,
        ]);
    }
}
