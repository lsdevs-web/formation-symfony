<?php


namespace App\Service;


use Doctrine\ORM\EntityManagerInterface;

class StatService
{

    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        return $this->manager = $manager;
    }

    public function getStats()
    {
        $users = $this->getUserCount();
        $annonces = $this->getAdsCount();
        $bookings = $this->getBookingsCount();
        $comments = $this->getCommentsCount();

        return compact('users', 'annonces', 'bookings', 'comments');
    }

    public function getAdsStats($direction, $maxResult = 5)
    {
        return $this->manager->createQuery(
            'SELECT AVG(c.rating) as note, a.title, a.id, u.firstName, u.avatar, u.lastName
            FROM App\Entity\Comment c
            JOIN c.annonce a
            JOIN a.author u
            GROUP BY a
            ORDER BY note ' . $direction
        )
            ->setMaxResults($maxResult)
            ->getResult();
    }

    public function getUserCount()
    {
        return $this->manager->createQuery('SELECT COUNT(u) FROM App\Entity\User u')->getSingleScalarResult();
    }

    public function getAdsCount()
    {
        return $this->manager->createQuery('SELECT COUNT(a) FROM App\Entity\Annonce a')->getSingleScalarResult();
    }

    public function getBookingsCount()
    {
        return $this->manager->createQuery('SELECT COUNT(b) FROM App\Entity\Booking b')->getSingleScalarResult();
    }

    public function getCommentsCount()
    {
        return $this->manager->createQuery('SELECT COUNT(c) FROM App\Entity\Comment c')->getSingleScalarResult();
    }




}
