<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BookingRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Booking
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $booker;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Annonce", inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $annonce;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\GreaterThan("today", message="La date d'arrivée doit être ultérieure à la date d'aujourd'hui", groups={"front"}) // VALIDATION DE GROUPE
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\GreaterThan(propertyPath="startDate", message="La date de départ doit être supérrieure à celle d'arrivée")
     */
    private $endDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createAt;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;


    /**
     * @ORM\PrePersist()
     * @return void
     * @throws Exception
     */
    public function prePersist()
    {
        if (empty($this->createAt)) {
            $this->createAt = new DateTime();
        }
        if (empty($this->amount)) {
            // Prix annonce * nb Jour
            $this->amount = $this->annonce->getPrice() * $this->getDuration();
        }
    }

    public function getDuration()
    {
        // On fait la différence entre la date de fin et la date de début pour compter le nb de jours
        $diff = $this->endDate->diff($this->startDate);
        // On retourne en nb de jours
        return $diff->days;
    }

    // Empêche de réserver par dessus un date existente
    public function isBookableDate()
    {
        // Il faut connaître les dates qui sont impossibles pour l'annonce (Il faut créer une fonction dans l'entité annonce)
        $notAvailablesDays = $this->annonce->getNotAvailablesDays();
        //Il faut comparer les dates choisies avec les dates impossibles
        $bookingDays = $this->getDays();

        // Tableau qui contient des chaînes de caractères de mes journées
        $days = array_map(function ($day) {
            return $day->format('Y-m-d');
        }, $bookingDays);


        $notAvailable = array_map(function ($day) {
            return $day->format('Y-m-d');
        }, $notAvailablesDays);

        foreach ($days as $day) {
            if (array_search($day, $notAvailable) !== false) return false;
        }

        return true;
        // On retourne true si ok false si problème

    }

    /**
     * Permet de récupérer un tableau des journées qui correspondent à ma réservation
     *
     * @return array
     */
    public function getDays()
    {
        $resultat = range(
            $this->startDate->getTimestamp(),
            $this->endDate->getTimestamp(),
            24 * 60 * 60
        );

        $days = array_map(function ($dayTimestamp) {
            return new DateTime(date('Y-m-d', $dayTimestamp));
        }, $resultat);

        return $days;

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBooker(): ?User
    {
        return $this->booker;
    }

    public function setBooker(?User $booker): self
    {
        $this->booker = $booker;

        return $this;
    }

    public function getAnnonce(): ?Annonce
    {
        return $this->annonce;
    }

    public function setAnnonce(?Annonce $annonce): self
    {
        $this->annonce = $annonce;

        return $this;
    }

    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCreateAt(): ?DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(DateTimeInterface $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
