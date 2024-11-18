<?php

namespace App\Security\Voter;

use App\Entity\Products;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductsVoter extends Voter
{
    const EDIT = 'PRODUCT_EDIT';
    const DELETE = 'PRODUCT_DELETE';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    // Vérifie si l'attribut et l'objet sont supportés
    protected function supports(string $attribute, $product): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE]) && $product instanceof Products;
    }

    // Vérifie les permissions en fonction de l'attribut (edit, delete)
    protected function voteOnAttribute($attribute, $product, TokenInterface $token): bool
    {
        // Récupère l'utilisateur actuel
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            // Si l'utilisateur n'est pas connecté, il ne peut rien faire
            return false;
        }

        // Si l'utilisateur est admin, il peut tout faire
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Logique spécifique en fonction de l'attribut
        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($product, $user);
            case self::DELETE:
                return $this->canDelete($product, $user);
        }

        return false;
    }

    private function canEdit(Products $product, UserInterface $user): bool
    {
        // Par exemple : seulement les utilisateurs ayant ROLE_PRODUCT_ADMIN peuvent éditer
        return $this->security->isGranted('ROLE_PRODUCT_ADMIN');
    }

    private function canDelete(Products $product, UserInterface $user): bool
    {
        // Par exemple : seul un administrateur peut supprimer un produit
        return $this->security->isGranted('ROLE_ADMIN');
    }
}