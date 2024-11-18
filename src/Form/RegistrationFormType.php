<?php
namespace App\Form;

use App\Entity\Users; 
use Symfony\Component\Form\AbstractType; // Importation de la classe de base pour créer un formulaire Symfony.
use Symfony\Component\Form\Extension\Core\Type\CheckboxType; // Importation de CheckboxType pour un champ de type case à cocher.
use Symfony\Component\Form\Extension\Core\Type\EmailType; // Importation de EmailType pour un champ de type e-mail.
use Symfony\Component\Form\Extension\Core\Type\PasswordType; // Importation de PasswordType pour un champ de type mot de passe.
use Symfony\Component\Form\Extension\Core\Type\TextType; // Importation de TextType pour un champ de type texte (utilisé pour le nom, prénom, etc.).
use Symfony\Component\Form\FormBuilderInterface; // Interface pour construire le formulaire.
use Symfony\Component\OptionsResolver\OptionsResolver; // Classe permettant de configurer les options du formulaire.
use Symfony\Component\Validator\Constraints\IsTrue; // Validation qui vérifie qu'une case à cocher est cochée.
use Symfony\Component\Validator\Constraints\Length; // Validation pour vérifier la longueur d'un texte (ex. mot de passe).
use Symfony\Component\Validator\Constraints\NotBlank; // Validation qui s'assure qu'un champ n'est pas vide.

class RegistrationFormType extends AbstractType
{
    // La méthode buildForm sert à définir les champs du formulaire.
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Ajout d'un champ 'email' de type EmailType
        $builder
            ->add('email', EmailType::class, [
                'attr' => [ // Définition des attributs HTML pour le champ (classe CSS ici).
                    'class' => 'form-control' // Applique la classe CSS 'form-control' à ce champ pour une mise en forme.
                ],
                'label' => 'E-mail' // Texte de l'étiquette associée au champ.
            ])
            // Ajout d'un champ 'lastname' de type TextType pour le nom de famille.
            ->add('lastname', TextType::class, [
                'attr' => [ // Définition des attributs HTML pour le champ.
                    'class' => 'form-control' // Applique la classe CSS 'form-control' à ce champ.
                ],
                'label' => 'Nom' // Texte de l'étiquette associée au champ.
            ])
            // Ajout d'un champ 'firstname' de type TextType pour le prénom.
            ->add('firstname', TextType::class, [
                'attr' => [ // Définition des attributs HTML pour le champ.
                    'class' => 'form-control' // Applique la classe CSS 'form-control' à ce champ.
                ],
                'label' => 'Prénom' // Texte de l'étiquette associée au champ.
            ])
            // Ajout d'un champ 'address' de type TextType pour l'adresse.
            ->add('address', TextType::class, [
                'attr' => [ // Définition des attributs HTML pour le champ.
                    'class' => 'form-control' // Applique la classe CSS 'form-control' à ce champ.
                ],
                'label' => 'Adresse' // Texte de l'étiquette associée au champ.
            ])
            // Ajout d'un champ 'zipcode' de type TextType pour le code postal.
            ->add('zipcode', TextType::class, [
                'attr' => [ // Définition des attributs HTML pour le champ.
                    'class' => 'form-control' // Applique la classe CSS 'form-control' à ce champ.
                ],
                'label' => 'Code postal' // Texte de l'étiquette associée au champ.
            ])
            // Ajout d'un champ 'city' de type TextType pour la ville.
            ->add('city', TextType::class, [
                'attr' => [ // Définition des attributs HTML pour le champ.
                    'class' => 'form-control' // Applique la classe CSS 'form-control' à ce champ.
                ],
                'label' => 'Ville' // Texte de l'étiquette associée au champ.
            ])
            // Ajout d'un champ 'RGPDConsent' de type CheckboxType (case à cocher pour le consentement RGPD).
            ->add('RGPDConsent', CheckboxType::class, [
                'mapped' => false, // Ce champ n'est pas lié à une propriété de l'entité Users.
                'constraints' => [ // Validation : l'utilisateur doit accepter les termes.
                    new IsTrue([
                        'message' => 'You should agree to our terms.', // Message d'erreur si l'utilisateur ne coche pas la case.
                    ]), 
                ],
                'label' => 'En m\'inscrivant à ce site j\'accepte...' // Texte de l'étiquette associée au champ.
            ])
            // Ajout d'un champ 'plainPassword' de type PasswordType pour le mot de passe.
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false, // Ce champ n'est pas lié directement à une propriété de l'entité Users.
                'attr' => [
                    'autocomplete' => 'new-password', // Désactive la suggestion de mot de passe par le navigateur.
                    'class' => 'form-control' // Applique la classe CSS 'form-control' à ce champ.
                ],
                'constraints' => [
                    new NotBlank([ // Validation pour s'assurer que le champ n'est pas vide.
                        'message' => 'Please enter a password', // Message d'erreur si le champ est vide.
                    ]),
                    new Length([ // Validation pour vérifier la longueur du mot de passe.
                        'min' => 6, // Longueur minimale du mot de passe.
                        'minMessage' => 'Your password should be at least {{ limit }} characters', // Message d'erreur si le mot de passe est trop court.
                        'max' => 4096, // Longueur maximale du mot de passe.
                    ]),
                ],
                'label' => 'Mot de passe' // Texte de l'étiquette associée au champ.
            ])
        ;
    }

    // La méthode configureOptions permet de définir des options par défaut pour le formulaire.
    public function configureOptions(OptionsResolver $resolver): void
    {
        // Définition de l'entité associée au formulaire (ici, l'entité Users).
        $resolver->setDefaults([
            'data_class' => Users::class, // Le formulaire est lié à l'entité Users.
        ]);
    }
}
