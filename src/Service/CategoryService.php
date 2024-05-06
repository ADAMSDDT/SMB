<?php
// src/Service/CategoryService.php

namespace App\Service;

use App\Entity\Categories;
use Doctrine\ORM\EntityManagerInterface;

class CategoryService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getMainCategoriesWithSubcategories()
    {
        $categoriesRepository = $this->entityManager->getRepository(Categories::class);
        $mainCategories = $categoriesRepository->findBy(['parent' => null]);  // Hypothétique: Assurez-vous que votre entité et repository supportent cela.
        $categoriesWithSubs = [];

        foreach ($mainCategories as $category) {
            $subCategories = $categoriesRepository->findBy(['parent' => $category]);
            $categoriesWithSubs[$category->getName()] = $subCategories;
        }

        return $categoriesWithSubs;
    }
}
