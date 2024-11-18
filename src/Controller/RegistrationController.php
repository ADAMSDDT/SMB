<?php

namespace App\Controller;

use App\Entity\Users; // Import de l'entité utilisateur
use App\Form\RegistrationFormType; // Import du formulaire d'inscription
use App\Repository\UsersRepository; // Import du repository pour les utilisateurs
use App\Security\UsersAuthenticator; // Import du système d'authentification personnalisé
use App\Service\JWTService; // Import du service pour manipuler les JWT
use App\Service\SendMailService; // Import du service pour envoyer des emails
use Doctrine\ORM\EntityManagerInterface; // Import pour interagir avec la base de données
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Contrôleur de base dans Symfony
use Symfony\Component\HttpFoundation\Request; // Gère les données de la requête HTTP
use Symfony\Component\HttpFoundation\Response; // Gère les réponses HTTP
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Service pour hacher les mots de passe
use Symfony\Component\Routing\Annotation\Route; // Annotation pour définir les routes
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface; // Interface pour gérer l'authentification

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        UserAuthenticatorInterface $userAuthenticator, 
        UsersAuthenticator $authenticator, 
        EntityManagerInterface $entityManager, 
        SendMailService $mail, 
        JWTService $jwt
    ): Response {
        // Création d'un nouvel utilisateur
        $user = new Users();

        // Création du formulaire d'inscription basé sur l'entité utilisateur
        $form = $this->createForm(RegistrationFormType::class, $user);

        // Traitement de la requête (remplissage des données utilisateur à partir du formulaire soumis)
        $form->handleRequest($request);

        // Vérification que le formulaire a été soumis et que les données sont valides
        if ($form->isSubmitted() && $form->isValid()) {
            // Hachage du mot de passe de l'utilisateur avant de l'enregistrer
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user, // Objet utilisateur
                    $form->get('plainPassword')->getData() // Récupération du mot de passe brut
                )
            );

            // Sauvegarde de l'utilisateur dans la base de données
            $entityManager->persist($user); // Préparation de l'enregistrement
            $entityManager->flush(); // Exécution de l'enregistrement

            // Génération du JWT pour l'utilisateur
            $header = [
                'typ' => 'JWT', // Type du token
                'alg' => 'HS256' // Algorithme utilisé
            ];

            $payload = [
                'user_id' => $user->getId() // Ajout de l'ID utilisateur dans le payload
            ];

            // Génération du token avec le header, le payload et la clé secrète
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            // Envoi d'un email d'activation à l'utilisateur
            $mail->send(
                'no-reply@monsite.net', // Adresse email de l'expéditeur
                $user->getEmail(), // Adresse email du destinataire
                'Activation de votre compte sur le site e-commerce', // Sujet du mail
                'register', // Template du mail
                compact('user', 'token') // Variables envoyées au template (user et token)
            );

            // Authentification automatique de l'utilisateur après l'inscription
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        // Affichage du formulaire d'inscription s'il n'est pas soumis ou est invalide
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    

    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifyUser(
        $token, 
        JWTService $jwt, 
        UsersRepository $usersRepository, 
        EntityManagerInterface $em
    ): Response {
        // Vérification de la validité, de l'expiration et de la signature du token
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {
            // Récupération des données contenues dans le payload du token
            $payload = $jwt->getPayload($token);

            // Recherche de l'utilisateur correspondant à l'ID présent dans le token
            $user = $usersRepository->find($payload['user_id']);

            // Activation du compte si l'utilisateur existe et qu'il n'est pas déjà vérifié
            if ($user && !$user->getIsVerified()) {
                $user->setIsVerified(true); // Activation du compte
                $em->flush(); // Mise à jour en base de données

                // Message de confirmation pour l'utilisateur
                $this->addFlash('success', 'Utilisateur activé');
                return $this->redirectToRoute('profile_index'); // Redirection vers la page de profil
            }
        }

        // Message d'erreur si le token est invalide ou expiré
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login'); // Redirection vers la page de connexion
    }

    #[Route('/renvoiverif', name: 'resend_verif')]
    public function resendVerif(
        JWTService $jwt, 
        SendMailService $mail
    ): Response {
        // Vérification que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login'); // Redirection vers la page de connexion
        }

        // Vérification que l'utilisateur n'est pas déjà activé
        if ($user->getIsVerified()) {
            $this->addFlash('warning', 'Cet utilisateur est déjà activé');
            return $this->redirectToRoute('profile_index'); // Redirection vers la page de profil
        }

        // Génération d'un nouveau token JWT
        $header = [
            'typ' => 'JWT', // Type du token
            'alg' => 'HS256' // Algorithme utilisé
        ];

        $payload = [
            'user_id' => $user->getId() // Ajout de l'ID utilisateur dans le payload
        ];

        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

        // Envoi d'un email de vérification avec le nouveau token
        $mail->send(
            'no-reply@monsite.net', // Adresse email de l'expéditeur
            $user->getEmail(), // Adresse email du destinataire
            'Activation de votre compte sur le site e-commerce', // Sujet du mail
            'register', // Template du mail
            compact('user', 'token') // Variables envoyées au template (user et token)
        );

        // Message de confirmation pour l'utilisateur
        $this->addFlash('success', 'Email de vérification envoyé');
        return $this->redirectToRoute('profile_index'); // Redirection vers la page de profil
    }
}
