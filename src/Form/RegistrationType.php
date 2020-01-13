<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends ApplicationType // ApplicationType => Classe crée nous-même pour gérer la fonction getConfig. Elle étend de AbstractType.
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, $this->getConfig('Prénom', 'Votre prénom'))
            ->add('lastName', TextType::class, $this->getConfig('Nom', 'Votre nom'))
            ->add('email', EmailType::class, $this->getConfig('Email', 'Votre adresse email'))
            ->add('avatar', UrlType::class, $this->getConfig('Photo de profil', 'Url de votre avatar'))
            ->add('hash', PasswordType::class, $this->getConfig('Mot de passe', 'Choisir un bon mot de passe'))
            // Champ de vérification du mot de passe
            // Comme l'Entity User n'a pas d'attribut passwordConfirm, il faut le créer !
            ->add('passwordConfirm', PasswordType::class, $this->getConfig('Confirmation de mot de passe', "Veuillez confirmer votre mot de passe"))
            ->add('introduction', TextType::class, $this->getConfig('Introduction', 'Présentez-vous'))
            ->add('description', TextareaType::class, $this->getConfig('Déscription', "C'est le moment de vous présenter !"));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
