<?php
namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use App\Entity\Size;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/cart', name: 'cart_')] // Déclare la route de base pour ce contrôleur, tout ce qui suit sera préfixé par '/cart'
class CartController extends AbstractController
{
    private $entityManager; // Déclare une propriété pour l'EntityManager

    // Injection de l'EntityManager dans le constructeur
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager; // L'EntityManager est injecté et stocké pour être utilisé plus tard
    }

    // Route pour afficher le panier (par défaut, la route '/cart')
    #[Route('/', name: 'index')] 
    public function index(SessionInterface $session, ProductsRepository $productsRepository)
    {
        $panier = $session->get('panier', []); // Récupère le panier de la session, ou un tableau vide s'il n'existe pas
        $data = []; // Tableau pour stocker les informations sur les produits du panier
        $total = 0; // Initialisation du total à 0

        // Parcourt chaque élément du panier
        foreach ($panier as $key => $quantity) {
            // Décompose la clé qui est sous la forme 'idProduit-idTaille'
            if (strpos($key, '-') !== false) {
                [$id, $sizeId] = explode('-', $key); // Sépare l'ID du produit et de la taille
            } else {
                continue; // Si la clé n'est pas dans le bon format, passe à l'élément suivant
            }

            // Récupère le produit et la taille depuis la base de données
            $product = $productsRepository->find($id);
            $size = $this->entityManager->getRepository(Size::class)->find($sizeId);

            // Si le produit et la taille existent
            if ($product && $size) {
                // Ajoute les informations du produit au tableau 'data' avec sa quantité
                $data[] = [
                    'product' => $product,
                    'size' => $size,
                    'quantity' => $quantity,
                ];
                // Calcule le total en multipliant le prix du produit par sa quantité
                $total += $product->getPrice() * $quantity;
            } else {
                // Si le produit ou la taille n'existe pas, ignore cet élément
                continue;
            }
        }

        // Rend la vue 'cart/index.html.twig' avec les données du panier et le total
        return $this->render('cart/index.html.twig', compact('data', 'total'));
    }

    // Route pour ajouter un produit au panier
    #[Route('/add/{id}', name: 'add')] 
    public function add(Products $product, SessionInterface $session, Request $request)
    {
        // Récupère l'ID de la taille sélectionnée dans les paramètres de la requête
        $sizeId = $request->query->get('size');
        $id = $product->getId();

        // Récupère le panier existant dans la session
        $panier = $session->get('panier', []);

        // Crée une clé unique pour le produit et sa taille (par exemple '123-2' pour produit 123, taille 2)
        $productKey = $id . '-' . $sizeId;

        // Si le produit n'est pas encore dans le panier, l'ajoute avec une quantité de 1
        if (empty($panier[$productKey])) {
            $panier[$productKey] = 1;
        } else {
            // Si le produit est déjà dans le panier, on augmente sa quantité de 1
            $panier[$productKey]++;
        }

        // Met à jour le panier dans la session
        $session->set('panier', $panier);
        // Redirige l'utilisateur vers la page du panier
        return $this->redirectToRoute('cart_index');
    }

    // Route pour supprimer un produit du panier
    #[Route('/remove/{id}', name: 'remove')] 
    public function remove(Products $product, SessionInterface $session, Request $request)
    {
        // Récupère l'ID de la taille sélectionnée dans les paramètres de la requête
        $sizeId = $request->query->get('size');
        $id = $product->getId();
        // Récupère le panier actuel dans la session
        $panier = $session->get('panier', []);
        $productKey = $id . '-' . $sizeId;

        // Si le produit est dans le panier et que la quantité est supérieure à 1
        if (!empty($panier[$productKey])) {
            if ($panier[$productKey] > 1) {
                // Diminue la quantité de 1
                $panier[$productKey]--;
            } else {
                // Si la quantité est 1, on supprime le produit du panier
                unset($panier[$productKey]);
            }
        }

        // Met à jour le panier dans la session
        $session->set('panier', $panier);
        // Redirige l'utilisateur vers la page du panier
        return $this->redirectToRoute('cart_index');
    }

    // Route pour supprimer un produit du panier complètement
    #[Route('/delete/{id}', name: 'delete')] 
    public function delete(Products $product, SessionInterface $session, Request $request)
    {
        // Récupère l'ID de la taille et le produit
        $sizeId = $request->query->get('size');
        $id = $product->getId();
        // Récupère le panier actuel de la session
        $panier = $session->get('panier', []);
        $productKey = $id . '-' . $sizeId;

        // Si le produit est dans le panier
        if (!empty($panier[$productKey])) {
            // Supprime le produit du panier
            unset($panier[$productKey]);
        }

        // Met à jour le panier dans la session
        $session->set('panier', $panier);
        // Redirige l'utilisateur vers la page du panier
        return $this->redirectToRoute('cart_index');
    }

    // Route pour vider le panier complètement
    #[Route('/empty', name: 'empty')] 
    public function empty(SessionInterface $session)
    {
        // Supprime le panier de la session
        $session->remove('panier');
        // Redirige l'utilisateur vers la page du panier
        return $this->redirectToRoute('cart_index');
    }
}
