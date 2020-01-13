<?php

namespace App\Form;

use App\Entity\Annonce;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnonceType extends ApplicationType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // On ajoute chaque champs à notre builder (formulaire créé avec make:form)
            // Deuxième param : Type d'input, Troisième param: ['label' => $monlabel, 'attr' => ['placeholder' => $monPlaceholder, 'class' => 'active ]

            ->add('title', TextType::class, $this->getConfig("Titre", "Tapez un super titre pour votre annonce"))
            ->add('coverImage', UrlType::class, $this->getConfig("Url de l'image principale", "Donnez l'addresse d'une image qui donne envie"))
            ->add('intro', TextType::class, $this->getConfig("Introduction", "Donnez une description globale"))
            ->add('content', TextareaType::class, $this->getConfig("Description détaillée", "Une description qui donne envie de venir chez vous"))
            ->add('rooms', IntegerType::class, $this->getConfig("Chambres", "Nombre de chambres disponibles"))
            ->add('price', MoneyType::class, $this->getConfig("Prix par nuit", "Indiquez le prix pour une nuit"))


            // Pour les images on créé un sous formulaire (un autre Type) On lui passe ensuite son type qui est une Collection
            ->add(
            // Nouveau champs images
                'images',
                //Type collection d'image
                CollectionType::class,
                [
                    'entry_type' => ImageType::class, // Formulaire de type ImageType
                    'allow_add' => true, //Permet de rajouter de nouveaux éléments
                    'allow_delete' => true //Permet de supprimer les éléments
                ]
            );

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Annonce::class,
        ]);
    }
}
