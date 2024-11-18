<?php
namespace App\Controller\Admin;

use App\Entity\Size;
use App\Form\SizeFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/taille', name: 'admin_size_')]
class SizeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(EntityManagerInterface $em): Response
    {
        $sizes = $em->getRepository(Size::class)->findAll();

        return $this->render('admin/size/index.html.twig', [
            'sizes' => $sizes,
        ]);
    }

    #[Route('/ajout', name: 'add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $size = new Size();
        $form = $this->createForm(SizeFormType::class, $size);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($size);
            $em->flush();

            $this->addFlash('success', 'Taille ajoutée avec succès.');
            return $this->redirectToRoute('admin_size_index');
        }

        return $this->render('admin/size/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edition/{id}', name: 'edit')]
    public function edit(Size $size, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SizeFormType::class, $size);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Taille modifiée avec succès.');
            return $this->redirectToRoute('admin_size_index');
        }

        return $this->render('admin/size/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(Size $size, EntityManagerInterface $em): Response
    {
        $em->remove($size);
        $em->flush();

        $this->addFlash('success', 'Taille supprimée avec succès.');
        return $this->redirectToRoute('admin_size_index');
    }
}