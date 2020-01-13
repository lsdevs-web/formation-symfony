<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity; // Permet de dire que l'entity est unique
use Symfony\Component\Validator\Constraints as Assert; // Permet de valider les types de données (protection formulaire back-end) Constraint(s)

/**
 * @ORM\Entity(repositoryClass="App\Repository\AnnonceRepository")
 * //Permet d'utiliser PreUpdate, PrePersist etc
 * @ORM\HasLifecycleCallbacks()
 *  Permet de vérifier que notre Entity est unique dans la BDD !
 *  Unique Entity prend deux paramètres : les champs et le message
 *  Ici le titre doit être unique
 * @UniqueEntity(
 *     fields={"title"},
 *     message="Une autre annonce possède déjà ce titre !"
 * )
 */
class Annonce
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *     min="10", max="255",
     *     minMessage="Le titre doit faire plus de 10 charactères !",
     *     maxMessage="Le titre ne peut pas faire plus de 255 charactères"
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     *
     */
    private $slug;

    /**
     * @ORM\Column(type="float")
     * @Assert\Positive(message="Vous devez rentrez un prix correct")
     */
    private $price;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(
     *     min="20", max="255",
     *     minMessage="Votre introduction doit faire plus de 20 charactères !",
     *     maxMessage="Votre introduction ne peut pas faire plus de 255 charactères"
     * )
     */
    private $intro;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(
     *     min="100", max="255",
     *     minMessage="La description doit faire plus de 100 charactères !",
     *     maxMessage="La description ne peut pas faire plus de 255 charactères"
     * )
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Url
     */
    private $coverImage;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Positive(message="Veuillez renseigner au moins une chambre")
     */
    private $rooms;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Image", mappedBy="annonce", orphanRemoval=true)
     * Permet de valider aussi les sous-formulaires
     * @Assert\Valid()
     */
    private $images;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="annonces")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Booking", mappedBy="annonce")
     */
    private $bookings;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="annonce", orphanRemoval=true)
     */
    private $comments;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->bookings = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function initSlug()
    {

        if (empty($this->slug)) {
            $slugify = new Slugify();
            $this->slug = $slugify->slugify($this->title);
        }

    }

    /**
     * Permet d'obtenir un tableau des jours non disponibles pour cette annonce
     * Retoune un tableau d'objet DateTime représentant les jours
     * @return array
     */
    public function getNotAvailablesDays()
    {
        // Un tableau contenant les jours qui ne sont pas disponibles
        $notAvailablesDays = [];

        // On parcours tous les bookings
        foreach ($this->bookings as $booking) {
            // Calculer les jours qui se trouvent entre la date d'arrivée et de départ
            $resultat = range($booking->getStartDate()->getTimestamp(),
                $booking->getEndDate()->getTimestamp(),
                24 * 60 * 60
            );

            $days = array_map(function ($dayTimestamp) {
                return new \DateTime(date('Y-m-d', $dayTimestamp));

            }, $resultat);

            $notAvailablesDays = array_merge($notAvailablesDays, $days);
        }

        return $notAvailablesDays;

    }


    /**
     * Permet de calculer la note moyenne (TWIG => show annonce)
     *
     * @return float
     */
    public function getAverageRatings()
    {
        // Calculer la somme des notations
        $somme = array_reduce($this->comments->toArray(), function ($total, $comment) {
            return $total + $comment->getRating();
        }, 0);

        // Faire la division pour avoir la moyenne

         if (count($this->comments) > 0)  return $somme / count($this->comments);

         return 0;
    }

    /**
     * Permet de vérifier si l'utilisateur à déjà laissé un commentaire sur l'annonce
     *
     * @param User $author
     * @return mixed|null
     */
    public function getCommentFromAuthor(User $author)
    {
        // Cette fonction à besoin de recevoir un auteur pour chopper ses commentaires
        foreach ($this->comments as $comment) {
            if ($comment->getAuthor() === $author) return $comment;
        }

        return null;

    }


    public function __toString()
    {
        return $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getIntro(): ?string
    {
        return $this->intro;
    }

    public function setIntro(string $intro): self
    {
        $this->intro = $intro;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(string $coverImage): self
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function getRooms(): ?int
    {
        return $this->rooms;
    }

    public function setRooms(int $rooms): self
    {
        $this->rooms = $rooms;

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setAnnonce($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getAnnonce() === $this) {
                $image->setAnnonce(null);
            }
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

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
            $booking->setAnnonce($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->contains($booking)) {
            $this->bookings->removeElement($booking);
            // set the owning side to null (unless already changed)
            if ($booking->getAnnonce() === $this) {
                $booking->setAnnonce(null);
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
            $comment->setAnnonce($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAnnonce() === $this) {
                $comment->setAnnonce(null);
            }
        }

        return $this;
    }
}
