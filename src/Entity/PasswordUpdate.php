<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordUpdate
{

    // Cette Entity n'en est pas une.
    // On l'a créé pour pouvoir utiliser les validations de formulaire de Symfony (Assert)
    // On créé ensuite un Form qui n'a pas de lien avec une Entity, on doit ensuite modifier correctement le formulaire


    private $oldPassword;


    /**
     * @Assert\Length(min="8", minMessage="Votre mot de passe doit faire au moins 8 caractères")
     */
    private $newPassword;


    /**
     * @Assert\EqualTo(propertyPath="newPassword", message="Les deux mots de passe ne correspondent pas")
     */
    private $confirmPassword;



    public function getOldpassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldpassword(string $oldPassword): self
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): self
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(string $confirmPassword): self
    {
        $this->confirmPassword = $confirmPassword;

        return $this;
    }
}
