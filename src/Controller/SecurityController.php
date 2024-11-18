<?php

namespace App\Controller;

// Importation des classes nécessaires pour les formulaires, la gestion de la base de données, et la sécurité
use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UsersRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    // Route pour la page de connexion
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté, on pourrait le rediriger vers une autre page
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // Récupère l'erreur d'authentification s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        // Récupère le dernier nom d'utilisateur entré par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        // Affiche la page de connexion avec le dernier nom d'utilisateur et l'erreur s'il y en a une
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    // Route pour la déconnexion
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Ce code ne sera jamais exécuté, car Symfony gère la déconnexion automatiquement via le firewall
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    // Route pour demander la réinitialisation du mot de passe
    #[Route('/oubli-pass', name:'forgotten_password')]
    public function forgottenPassword(
        Request $request,
        UsersRepository $usersRepository,
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface $entityManager,
        SendMailService $mail
    ): Response
    {
        // Création du formulaire de demande de réinitialisation de mot de passe
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère l'utilisateur en fonction de l'email entré dans le formulaire
            $user = $usersRepository->findOneByEmail($form->get('email')->getData());

            // Vérifie si l'utilisateur existe
            if ($user) {
                // Génère un token de réinitialisation de mot de passe
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $entityManager->persist($user);
                $entityManager->flush();

                // Génère un lien de réinitialisation de mot de passe
                $url = $this->generateUrl('reset_pass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                
                // Crée les données pour le mail de réinitialisation de mot de passe
                $context = compact('url', 'user');

                // Envoie du mail avec le lien de réinitialisation
                $mail->send(
                    'no-reply@e-commerce.fr',     // Adresse de l'expéditeur
                    $user->getEmail(),             // Adresse de l'utilisateur
                    'Réinitialisation de mot de passe',  // Sujet du mail
                    'password_reset',              // Template du mail
                    $context                       // Données du mail (contexte)
                );

                // Message de succès pour l'utilisateur
                $this->addFlash('success', 'Email envoyé avec succès');
                return $this->redirectToRoute('app_login');
            }

            // Si aucun utilisateur n'est trouvé
            $this->addFlash('danger', 'Un problème est survenu');
            return $this->redirectToRoute('app_login');
        }

        // Affiche le formulaire de demande de réinitialisation de mot de passe
        return $this->render('security/reset_password_request.html.twig', [
            'requestPassForm' => $form->createView()
        ]);
    }

    // Route pour réinitialiser le mot de passe à partir du lien envoyé par email
    #[Route('/oubli-pass/{token}', name:'reset_pass')]
    public function resetPass(
        string $token,
        Request $request,
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        // Vérifie si le token existe dans la base de données
        $user = $usersRepository->findOneByResetToken($token);
        
        if ($user) {
            // Création du formulaire de réinitialisation de mot de passe
            $form = $this->createForm(ResetPasswordFormType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Efface le token de réinitialisation une fois le mot de passe changé
                $user->setResetToken('');
                // Hash et enregistre le nouveau mot de passe
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $entityManager->persist($user);
                $entityManager->flush();

                // Message de succès pour l'utilisateur
                $this->addFlash('success', 'Mot de passe changé avec succès');
                return $this->redirectToRoute('app_login');
            }

            // Affiche le formulaire pour entrer le nouveau mot de passe
            return $this->render('security/reset_password.html.twig', [
                'passForm' => $form->createView()
            ]);
        }

        // Si le token est invalide, affiche un message d'erreur
        $this->addFlash('danger', 'Jeton invalide');
        return $this->redirectToRoute('app_login');
    }
}
