<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoriesFixtures extends Fixture
{
    private $counter = 1;

    public function __construct(private SluggerInterface $slugger){}

    public function load(ObjectManager $manager): void
    {
        // Création des catégories principales avec un ordre spécifié
        $male = $this->createCategory('Homme', null, $manager, 1);
        $this->createCategory('Costumes', $male, $manager, 2);
        $this->createCategory('Chemises', $male, $manager, 3);
        $this->createCategory('Jeans', $male, $manager, 4);

        $female = $this->createCategory('Femme', null, $manager, 5);
        $this->createCategory('Robes', $female, $manager, 6);
        $this->createCategory('Jupes', $female, $manager, 7);
        $this->createCategory('Blouses', $female, $manager, 8);

        $manager->flush();
    }

    public function createCategory(string $name, Categories $parent = null, ObjectManager $manager, int $order)
    {
        $category = new Categories();
        $category->setName($name);
        $category->setSlug($this->slugger->slug($category->getName())->lower());
        $category->setParent($parent);
        $category->setCategoryOrder($order); // Assignation de l'ordre de la catégorie
        $manager->persist($category);

        $this->addReference('cat-'.$this->counter, $category);
        $this->counter++;

        return $category;
    }
}
