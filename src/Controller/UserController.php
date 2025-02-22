<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/mon-compte', name: 'user_account')]
    public function account(): Response
    {
        return $this->render('user/account.html.twig');
    }
}
