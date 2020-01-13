<?php

namespace App\Controller;

use App\Entity\PasswordUpdate;
use App\Entity\User;
use App\Form\AccountType;
use App\Form\PasswordUpdateType;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AccountController extends AbstractController
{
    /**
     * Permet d'afficher et de gérer le formulaire de connexion
     *
     * @Route("/login", name="account_login")
     * @param AuthenticationUtils $utils
     * @return Response
     */
    public function login(AuthenticationUtils $utils)
        // AUTHENTICATIONUTILS => Pour les erreurs, ce qu'il s'est passé etc
    {
        // Renvoie un Objet (message, type d'erreur etc)
        $error = $utils->getLastAuthenticationError();
        // Le dernier utilisateur connu
        $username = $utils->getLastUsername();

        // Security.yaml form_login
        return $this->render('account/login.html.twig', [
            //Si l'erreur est différent de null
            'has_error' => $error !== null,
            'username' => $username
        ]);
    }


    /**
     * Permet de se déconnecter
     *
     * @Route("/logout", name="account_logout")
     * @return void
     */
    public function logout()
    {
        // security.yaml logout
    }


    /**
     * Permet d'affiche le formulaire d'inscription
     *
     * @Route("/register", name="account_register")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function register(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
        //Pour la requête d'enregistrement + La gestion de l'enregistrement avec le manager + L'encodage du mot de passe
    {
        // On créé un nouvel utilisateur
        $user = new User();

        // On créé un nouveau formulaire de type Registration
        $form = $this->createForm(RegistrationType::class, $user);

        // Pour gérer la requête du formulaire "submit"
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // security.yaml => encoders
            // Encodeur prend deux params : l'entity et le mot de passe de l'entity
            $hash = $encoder->encodePassword($user, $user->getHash());

            // On enregistre le mot de passe crypté
            $user->setHash($hash);

            // Faire persister le nouvel utilisateur créé
            $manager->persist($user);
            // Enregistrement
            $manager->flush();

            // On créé un message flash
            $this->addFlash(
            // Le type
                'success',
                // Le message
                'Votre compte à bien été créé vous pouvez vous connecter !'
            );

            return $this->redirectToRoute('account_login');
        }

        return $this->render("account/registration.html.twig", [
            //On créé le formulaire
            'form' => $form->createView()
        ]);

    }


    /**
     * Affiche et traite le formulaire de modif de profil
     * @Route("/account/profil/", name="account_profil")
     *
     * Permet de dire qu'un utilisateur non connecté ne peut pas accéder au profil
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function profil(Request $request, EntityManagerInterface $manager)
        // Request pour gérer la soumission du formulaire, Manager pour la BDD
    {
        $user = $this->getUser();

        $form = $this->createForm(AccountType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Pas nécéssaire car l'entity existe déjà mais par sécurité on persist
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'Les données du profil on été enregistrées avec succès !'
            );
        }


        return $this->render('account/profil.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * Permet de modifier le mdp
     *
     * @Route("/account/password-update", name="account_password")
     *
     * Empêche quelqu'un de non connecté d'accéder à la page de modification du mot de passe
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function updatePassword(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager)
    {
        // On déclare notre "Fausse" Entity
        $passwordUpdate = new PasswordUpdate();

        // On regarde l'utilisateur actuel
        $user = $this->getUser();

        // On créé la forme de Type PasswordUpdateType, et on lui passe notre Entity PasswordUpdate
        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);

        // Gérer la requête
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            // Ici on verifie que le mot de passe de $user (BDD) est bien le même que celui rentré dans $oldPassword
            // $encoder->isPasswordValid(1er parem : Entity (donc notre user actuel), 2eme param : Password (le password (champs) à vérifier
            if (!$encoder->isPasswordValid($user, $passwordUpdate->getOldpassword())) {

                // Ici on accède au champs oldPassword, on lui rajoute une erreur
                // Pour afficher que les mots de passe ne correspondent pas
                $form->get('oldPassword')->addError(new FormError("Votre mot de passe n'est pas le bon"));

            } else {

                // On enregistre le nouveau mdp dans une variable
                $newPassword = $passwordUpdate->getNewPassword();

                // On le hash avec l'encoder (1er param : l'entity, 2eme param : le mdp à hasher)
                $hash = $encoder->encodePassword($user, $newPassword);

                // On set ensuite le nouveau hash de l'utilisateur
                $user->setHash($hash);


                $manager->persist($user);
                $manager->flush();


                $this->addFlash(
                    'success',
                    'Le mot de passe à été modifié'
                );

                return $this->redirectToRoute('home');

            }
        }


        return $this->render('account/password.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * Permet d'afficher le profil utilisateur
     *
     * @Route("/account", name="account_index")
     *
     * Si non connecté on ne peut pas accéder à notre compte
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function myAccount()
    {
        return $this->render("user/index.html.twig", [
            'user' => $this->getUser()
        ]);
    }


    /**
     * Permet d'afficher les réservations
     *
     * @Route("account/bookings", name="account_bookings")
     *
     * @return Response
     */
    public function bookings()
    {
        return $this->render("/account/booking.html.twig");
    }

}
