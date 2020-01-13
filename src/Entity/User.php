<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert; // Pour utiliser les validations de formulaire
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity; // Pour vérifier que l'entity est unique

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 * Unique Entity prend deux paramètres : les champs et le message
 * Ici l'email doit être unique
 * @UniqueEntity(
 *     fields={"email"},
 *     message="Cet utilisateur existe déjà"
 * )
 */
// Comme un User doit pouvoir se connecter => On l'ajoute dans le security.yaml
class User implements UserInterface // UserInterface => Nécéssaire à TOUS les utilisateurs
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Vous devez renseigner votre prénom")
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Vous devez renseigner votre nom de famille")
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(message="Veuillez renseignez un email valide")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Url(message="URL d'avatar non valide")
     */
    private $avatar;

    /**
     * @ORM\Column(type="string", length=255)
     *
     */
    private $hash;


    /**
     * @Assert\EqualTo(propertyPath="hash", message="Les deux mots de passes ne correspondent pas")
     */
    public $passwordConfirm;
    // On met en public pour ne pas à avoir à créer let getters et setters
    // Pas d'annotations non plus car ce n'est pas qqch qui existe dans la BDD
    // Sauf Assert pour vérifier EqualTo


    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="10",minMessage="Votre introduction doit faire au moins 10 caractères")
     */
    private $introduction;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(min="100",minMessage="Votre description doit faire au moins 100 caractères")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Annonce", mappedBy="author")
     */
    private $annonces;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Role", mappedBy="users")
     */
    private $userRoles;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Booking", mappedBy="booker")
     */
    private $bookings;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="author", orphanRemoval=true)
     */
    private $comments;


    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function initSlug()
    {
        // Permet de générer un slug automatiquement
        if (empty($this->slug)) {
            // nouvelle classe slugify
            $slugify = new Slugify();
            // On dit que le slug = slugify(ce que je veux)
            $this->slug = $slugify->slugify($this->firstName . '-' . $this->lastName);
        }

    }

    // Pour que dans Twig nous n'avons pas à faire {{ annonce.author.firstname }} {{ annone.author.lastname }}
    public function getFullName()
    {
        return "{$this->firstName} {$this->lastName}";
    }

    public function __construct()
    {
        $this->annonces = new ArrayCollection();
        $this->userRoles = new ArrayCollection();
        $this->bookings = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(string $introduction): self
    {
        $this->introduction = $introduction;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection|Annonce[]
     */
    public function getAnnonces(): Collection
    {
        return $this->annonces;
    }

    public function addAnnonce(Annonce $annonce): self
    {
        if (!$this->annonces->contains($annonce)) {
            $this->annonces[] = $annonce;
            $annonce->setAuthor($this);
        }

        return $this;
    }

    public function removeAnnonce(Annonce $annonce): self
    {
        if ($this->annonces->contains($annonce)) {
            $this->annonces->removeElement($annonce);
            // set the owning side to null (unless already changed)
            if ($annonce->getAuthor() === $this) {
                $annonce->setAuthor(null);
            }
        }

        return $this;
    }


    // Methodes necessaires à l'implémentation de UserInterface

    public function getRoles()
    {
        // Pour pouvoir get un role, comme c'est une entity il faut faire un fonction qui la retournera en string

        //On stock le role dans une variable, la fonction map() permet de créer un tableau avec le résultat de la fonction
        // On demande à la fonction ce qu'on veut qu'elle nous retourne (le titre de du role)
        $role = $this->userRoles->map(function ($role) {
                return $role->getTitle();
        })->toArray();

        // Tous les utilisateurs doivent forcément avoir le ROLE_USER, mais tous n'ont pas forcément un autre role =! Un utilisateur n'est pas un Anonyme
        // Alors à notre tableau qui à parcouru tous les rôles de l'utilisateur, on lui rajoute aussi le ROLE_USER
        // Comme ça si l'utilisateur n'a pas de role, il aura forcément le ROLE_USER
        $role[] = 'ROLE_USER';

        return $role;

    }

    public function getPassword()
    {
        return $this->hash;
    }


    public function getSalt()
    {
    } // Pas besoin le salt est déjà dedans!


    public function getUsername()
    {
        return $this->email; // Renvoie l'email car on veut que le User se connecte avec son email
    }

    public function eraseCredentials()
    {
        // Supprime des données sensibles de l'utilisateurs dans le code (Nous passons par la BDD et encodés)
    }

    /**
     * @return Collection|Role[]
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    public function addUserRole(Role $userRole): self
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles[] = $userRole;
            $userRole->addUser($this);
        }

        return $this;
    }

    public function removeUserRole(Role $userRole): self
    {
        if ($this->userRoles->contains($userRole)) {
            $this->userRoles->removeElement($userRole);
            $userRole->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|Booking[]
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings[] = $booking;
            $booking->setBooker($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->contains($booking)) {
            $this->bookings->removeElement($booking);
            // set the owning side to null (unless already changed)
            if ($booking->getBooker() === $this) {
                $booking->setBooker(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

}
