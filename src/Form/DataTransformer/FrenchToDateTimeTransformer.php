<?php

// On créé une classe pour transformer nos dates FR en DateTime
// Notre classe a besoin d'implémenter DataTransformerInterface

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;


class FrenchToDateTimeTransformer implements DataTransformerInterface
{


    /**
     * Transforme la date au format Français
     *
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws TransformationFailedException
     */
    public function transform($value)
    {
        if ($value === null) {
            return '';
        }

        return $value->format('d/m/Y');
    }


    /**
     * Renvoie un vrai DateTime
     *
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws TransformationFailedException
     */
    public function reverseTransform($value)
    {
        if ($value === null) {
            // Exception
            throw new TransformationFailedException("Vous devez fournir une date");
        }

        $date = \DateTime::createFromFormat('d/m/Y', $value);

        if ($date == false) {
            // Exception
            throw new TransformationFailedException("Le format de la date n'est pas le bon");

        }

        return $date;
    }
}
