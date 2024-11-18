<?php

namespace App\Controller\Admin;

use App\Entity\Images;
use App\Entity\Products;
use App\Form\ProductsFormType;
use App\Repository\ProductsRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/produits', name: 'admin_products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProductsRepository $productsRepository): Response
    {
        $produits = $productsRepository->findAll();
        return $this->render('admin/products/index.html.twig', [
            'produits' => $produits
        ]);;
    }

    #[Route('/ajout', name: 'add')]
public function add(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, PictureService $pictureService): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    $product = new Products();
    $productForm = $this->createForm(ProductsFormType::class, $product);
    $productForm->handleRequest($request);

    if ($productForm->isSubmitted() && $productForm->isValid()) {
        // Ajout des images
        $images = $productForm->get('images')->getData();
        foreach ($images as $image) {
            $folder = 'products';
            $fichier = $pictureService->add($image, $folder, 300, 300);
            $img = new Images();
            $img->setName($fichier);
            $product->addImage($img);
        }

        // Gérer le slug
        $slug = $slugger->slug($product->getName());
        $product->setSlug($slug);

        // Pas besoin de faire quoi que ce soit d'autre pour les tailles ici
        $em->persist($product);
        $em->flush();

        $this->addFlash('success', 'Produit ajouté avec succès');
        return $this->redirectToRoute('admin_products_index');
    }

    return $this->renderForm('admin/products/add.html.twig', compact('productForm'));
}

#[Route('/edition/{id}', name: 'edit')]
public function edit(Products $product, Request $request, EntityManagerInterface $em, SluggerInterface $slugger, PictureService $pictureService): Response
{
    $this->denyAccessUnlessGranted('PRODUCT_EDIT', $product);
    $productForm = $this->createForm(ProductsFormType::class, $product);
    $productForm->handleRequest($request);

    if ($productForm->isSubmitted() && $productForm->isValid()) {
        // Ajout des images
        $images = $productForm->get('images')->getData();
        foreach ($images as $image) {
            $folder = 'products';
            $fichier = $pictureService->add($image, $folder, 300, 300);
            $img = new Images();
            $img->setName($fichier);
            $product->addImage($img);
        }

        // Gérer le slug
        $slug = $slugger->slug($product->getName());
        $product->setSlug($slug);

        // Pas besoin de faire quoi que ce soit d'autre pour les tailles ici
        $em->persist($product);
        $em->flush();

        $this->addFlash('success', 'Produit modifié avec succès');
        return $this->redirectToRoute('admin_products_index');
    }

    return $this->render('admin/products/edit.html.twig', [
        'productForm' => $productForm->createView(),
        'product' => $product,
    ]);
}

    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(Products $product): Response
    {
        $this->denyAccessUnlessGranted('PRODUCT_DELETE', $product);
        return $this->render('admin/products/index.html.twig');
    }

    #[Route('/suppression/image/{id}', name: 'delete_image', methods: ['DELETE'])]
    public function deleteImage(Images $image, Request $request, EntityManagerInterface $em, PictureService $pictureService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])) {
            $nom = $image->getName();

            if ($pictureService->delete($nom, 'products', 300, 300)) {
                $em->remove($image);
                $em->flush();
                return new JsonResponse(['success' => true], 200);
            }
            return new JsonResponse(['error' => 'Erreur de suppression'], 400);
        }

        return new JsonResponse(['error' => 'Token invalide'], 400);
    }
}
