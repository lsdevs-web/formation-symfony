<?php


namespace App\Form;


use Symfony\Component\Form\AbstractType;

class ApplicationType extends AbstractType
{

    // Classe crée pour gérer la fonction getConfig pour ne pas se répeter.
    // On créé cette classe qui étendt de AbstractType pour ensuite passer notre ApplicationType à nos formulaires
    // Comme ça on évite de se répeter si chaque formulaire à besoin de cette fonction
    // Et comme notre ApplicationType étend de AbstractType on à toutes les fonctions nécéssaires

    /**
     * POUR EVITER DE SE REPETER DANS LA MODIFICATION DES ATTRIBUTS ON CREER UNE FONCTION
     * @param $label
     * @param $placeholder
     * @param array $options
     * @return array
     */
    protected function getConfig($label, $placeholder, $options = [])
    {
        return array_merge_recursive([
            'label' => $label,
            'attr' => [
                'placeholder' => $placeholder
            ]
        ], $options);
    }

}
